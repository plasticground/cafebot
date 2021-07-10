<?php

namespace App\Http\Controllers;

use App\Services\BotService;
use Illuminate\Http\Request;
use Telegram\Bot\Api;

class BotController extends Controller
{
    /**
     * @var Api
     */
    private Api $bot;

    /**
     * BotController constructor.
     * @throws \Telegram\Bot\Exceptions\TelegramSDKException
     */
    public function __construct()
    {
        $this->bot = new Api(config('telegram.bots.mybot.token'));
    }

    public function index(Request $request)
    {
        $bot = $this->bot;
        $webhookInfo = $this->bot->getWebhookInfo();
        return view('admin.bot.index', compact('bot', 'webhookInfo'));
    }

    public function setWebhook(Request $request)
    {
        $this->validate($request, [
            'url' => [
                'required',
                'string'
            ],
            'max_connections' => [
                'numeric',
                'min:1',
                'max:100'
            ],
        ]);

        $this->bot->setWebhook($request->all());

        return redirect(route('admin.bot.index'));
    }


}
