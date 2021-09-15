<?php

namespace App\Http\Controllers\Api\v1\Bot\Commands;


use App\Contracts\SettingsContract;
use App\Models\BotState;
use App\Models\Client;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;

/**
 * Class HelpCommand.
 */
class SettingsCommand extends Command
{

    /**
     * @var string Command Name
     */
    protected $name = 'settings';

    /**
     * @var string Command Description
     */
    protected $description = 'Settings command';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $client = Client::whereTelegramId($this->getUpdate()->message->from->id)->first();

        if ($client) {
            if (($botState = $client->botState)->state === BotState::STATE_MAIN_MENU) {
                try {
                    app('translator')->setLocale($client->locale);

                    app(SettingsContract::class)
                        ->setChat($botState)
                        ->setClient($client)
                        ->settings(BotState::STATE_SETTINGS);

                    return response()->json(['ok' => true]);
                } catch (\Throwable $exception) {
                    Log::error('Settings', ['error' => $exception->getMessage()]);

                    return response()->json(['ok' => false]);
                }
            }

            return response()->json(['ok' => false]);
        }

        return response()->json(['ok' => false]);
    }
}
