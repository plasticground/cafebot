<?php

namespace App\Http\Controllers\Api\v1\Bot\Commands;


use App\Contracts\BotContract;
use App\Models\BotState;
use App\Models\Client;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;

/**
 * Class HelpCommand.
 */
class HistoryCommand extends Command
{

    /**
     * @var string Command Name
     */
    protected $name = 'history';

    /**
     * @var string Command Description
     */
    protected $description = 'History command';

    protected $pattern = '{page}';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $client = Client::whereTelegramId($this->getUpdate()->message->from->id)->first();
        $page = (!isset($this->arguments['page']) || $this->arguments['page'] < 1) ? 1 : $this->arguments['page'];

        if ($client) {
            if (($botState = $client->botState)->state === BotState::STATE_MAIN_MENU) {
                try {
                    app(BotContract::class)
                        ->setChat($botState)
                        ->setClient($client)
                        ->history((int) $page);

                    return response()->json(['ok' => true]);
                } catch (\Throwable $exception) {
                    Log::error('History', ['error' => $exception->getMessage()]);

                    return response()->json(['ok' => false]);
                }
            }

            return response()->json(['ok' => false]);
        }

        return response()->json(['ok' => false]);
    }
}
