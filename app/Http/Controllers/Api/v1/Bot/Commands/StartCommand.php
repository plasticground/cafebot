<?php

namespace App\Http\Controllers\Api\v1\Bot\Commands;


use App\Contracts\RegistrationContract;
use App\Models\BotState;
use App\Models\Client;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;

/**
 * Class HelpCommand.
 */
class StartCommand extends Command
{

    /**
     * @var string Command Name
     */
    protected $name = 'start';

    /**
     * @var string Command Description
     */
    protected $description = 'Start command';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $id = $this->getUpdate()->message->from->id;

        if (BotState::find($id) === null) {
            BotState::create(
                [
                    'telegram_id' => $id,
                    'state' => BotState::STATE_NEW,
                ]
            );
        }

        $botState = BotState::find($id);

        if ($botState->state === BotState::STATE_NEW) {
            try {
                if (!($client = Client::whereTelegramId($id)->first())) {
                    $client = Client::create([
                        'telegram_id' => $id,
                        'telegram_username' => $this->getUpdate()->message->from->username,
                    ]);
                }

                app(RegistrationContract::class)
                    ->setChat($botState)
                    ->setClient($client)
                    ->registration(BotState::STATE_REGISTRATION_START);

                return response()->json(['ok' => true]);
            } catch (\Throwable $exception) {
                Log::error('Registration', ['error' => $exception->getMessage()]);

                return response()->json(['ok' => false]);
            }

            return response()->json(['ok' => true]);
        }

        return $this->replyWithMessage([
            'text' => 'Вы уже зареганы 💢'
        ]);
    }
}
