<?php

namespace App\Http\Controllers\Api\v1\Bot\Commands;


use App\Contracts\OrderContract;
use App\Models\BotState;
use App\Models\Client;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;

/**
 * Class HelpCommand.
 */
class OrderCommand extends Command
{

    /**
     * @var string Command Name
     */
    protected $name = 'order';

    /**
     * @var string Command Description
     */
    protected $description = 'Start command';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $client = Client::whereTelegramId($this->getUpdate()->message->from->id)->first();

        if ($client) {
            if (($botState = $client->botState)->state === BotState::STATE_MAIN_MENU) {
                try {
                    app(OrderContract::class)
                        ->setChat($botState)
                        ->setClient($client)
                        ->order(BotState::STATE_ORDER_NEW);

                    return response()->json(['ok' => true]);
                } catch (\Throwable $exception) {
                    Log::error('Order', ['error' => $exception->getMessage()]);

                    return response()->json(['ok' => false]);
                }
            }

            return response()->json(['ok' => false]);
        }

        return response()->json(['ok' => false]);
    }
}
