<?php

namespace App\Http\Controllers\Api\v1\Bot;

use App\Http\Controllers\Controller;
use App\Services\BotService;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Laravel\Facades\Telegram;

class BotController extends Controller
{
    /** @var Api  */
    private Api $bot;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->bot = new Api(config('telegram.bots.mybot.token'));
    }

    public function webhook(BotService $botService)
    {
        try {
            $botService->getUpdate(Telegram::commandsHandler(true));
        } catch (\Throwable $exception) {
            Log::error('Webhook', ['error' => $exception->getMessage()]);

            return response()->json(['ok' => false]);
        }

        return response()->json(['ok' => true]);
    }
}
