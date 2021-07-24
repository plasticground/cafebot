<?php

namespace App\Http\Controllers\Api\v1\Bot\Commands;


use App\Contracts\BotContract;
use App\Models\BotState;
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
                app(BotContract::class)->registration($botState);

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
