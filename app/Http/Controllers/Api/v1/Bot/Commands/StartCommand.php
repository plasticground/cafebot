<?php

namespace App\Http\Controllers\Api\v1\Bot\Commands;


use App\Contracts\BotContract;
use App\Models\Chat;
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
        $chat_id = $this->getUpdate()->getChat()->id;

        if (Chat::find($chat_id) === null) {
            Chat::create(
                [
                    'id' => $chat_id,
                    'state' => Chat::STATE_NEW,
                ]
            );
        }

        $chat = Chat::find($chat_id);

        if ($chat->state === Chat::STATE_NEW) {
            try {
                app(BotContract::class)->registration($chat);

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
