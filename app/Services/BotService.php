<?php


namespace App\Services;



use App\Contracts\BotContract;
use App\Contracts\OrderContract;
use App\Contracts\RegistrationContract;
use App\Contracts\SettingsContract;
use App\Models\BotState;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

/**
 * Class TelegramBotService
 * @package App\Services\Telegram
 */
class BotService implements BotContract
{

    public const
        LANG_UA = 'Українська',
        LANG_RU = 'Русский';

    /** @var BotState|null  */
    private ?BotState $chat;

    /** @var Client|null  */
    private ?Client $client;

    /**
     * @param BotState $botState
     * @return $this
     */
    public function setChat(?BotState $botState)
    {
        $this->chat = $botState ?? null;

        return $this;
    }

    /**
     * @param Client|null $client
     * @return $this
     */
    public function setClient(?Client $client)
    {
        $this->client = $client ?? null;

        return $this;
    }
    /**
     * @param Update $update
     */
    public function getUpdate(Update $update)
    {
        switch ($update->detectType()) {
            case 'message':
                $this->handleMessage($update);
                break;
            case 'callback_query':
                $this->handleCallbackQuery($update);
                break;
        }
    }

    /**
     * @param Update $update
     */
    public function handleMessage(Update $update)
    {
        $message = $update->getMessage();
        $this->setChat(BotState::find($message->from->id));

        if ($client = Client::whereTelegramId($message->from->id)->first()) {
            $this->setClient($client);
        }

        app('translator')->setLocale($this->client->locale ?? 'ua');

        if ($this->chat) {
            $text = $message->text ?? '';

            if (empty($text)) {
                abort(400, 'Empty text');
            }

            if ($this->chat->state > BotState::STATE_REGISTRATION_START && $this->chat->state < BotState::STATE_MAIN_MENU) {
                $this->registrationService()->handle($text);
            } elseif ($this->chat->state >= BotState::STATE_ORDER_NEW && $this->chat->state <= BotState::STATE_ORDER_FINISHED) {
                if ($this->orderService()->handle($text) === BotState::STATE_MAIN_MENU) {
                    $this->mainMenu();
                }
            } elseif ($this->chat->state >= BotState::STATE_SETTINGS && $this->chat->state <= BotState::STATE_SETTINGS_LANGUAGE) {
                if ($this->settingsService()->handle($text) === BotState::STATE_MAIN_MENU) {
                    $this->mainMenu();
                }
            }
        }
    }

    /**
     * @param Update $update
     */
    public function handleCallbackQuery(Update $update)
    {
        $callbackQuery = $update->callbackQuery;
        $this->setChat(BotState::find($callbackQuery->from->id));

        if ($client = Client::whereTelegramId($callbackQuery->from->id)->first()) {
            $this->setClient($client);
        }

        app('translator')->setLocale($this->client->locale ?? 'ua');

        if ($this->chat) {
            if ($this->chat->state >= BotState::STATE_ORDER_NEW && $this->chat->state <= BotState::STATE_ORDER_FINISHED) {
                $product = Product::findOrFail($callbackQuery->data);

                switch ($this->chat->state) {
                    case BotState::STATE_ORDER_STARTED:
                        $this->orderService()->makeOrder($product);
                        break;

                    case BotState::STATE_ORDER_EDITING:
                        $this->orderService()->editOrder($product);
                        break;

                    default:
                        break;
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function orderService()
    {
        return app(OrderContract::class)
            ->setChat($this->chat)
            ->setClient($this->client);
    }

    /**
     * @return mixed
     */
    public function settingsService()
    {
        return app(SettingsContract::class)
            ->setChat($this->chat)
            ->setClient($this->client);
    }

    /**
     * @return mixed
     */
    public function registrationService()
    {
        return app(RegistrationContract::class)
            ->setChat($this->chat)
            ->setClient($this->client);
    }

    /**
     *
     */
    public function mainMenu()
    {
        $messages = (new Collection([
            'main-menu' => trans('bot\mainMenu.open'),
        ]))->map(function ($item) {
            return ['chat_id' => $this->chat->telegram_id, 'text' => $item];
        });

        $this->chat->update(['state' => BotState::STATE_MAIN_MENU]);

        $menu = Keyboard::make();
        $menu->row(Keyboard::button(trans('bot\mainMenu.buttons.order')));
        $menu->row(Keyboard::button(trans('bot\mainMenu.buttons.history')), Keyboard::button(trans('bot\mainMenu.buttons.feedback')));
        $menu->row(Keyboard::button(trans('bot\mainMenu.buttons.settings')));

        Telegram::sendMessage($messages->get('main-menu') + ['reply_markup' => $menu]);
    }

    /**
     * @param int $page
     */
    public function history(int $page = 1)
    {
        app('translator')->setLocale($this->client->locale);

        $orders = Order::with('products')
            ->whereHas('client', function (Builder $builder) {
                return $builder->where('telegram_id', $this->client->telegram_id);
            })
            ->latest()
            ->paginate(5, ['*'], 'page', $page);

        $list = $orders->map(function (Order $order) {
            return "*" . trans('bot\history.order') . " № {$order->id} - {$order->price} ₴*\n"
                . '*' . trans('bot\history.state') . ' - ' . Order::getVerbalStatus($order->status) . '*' . "\n"
                . $order->products->map(fn(Product $product) => $product->getDisplayNamePriceWithAmount($this->client->locale))->implode("\n")
                . ($order->comment ? "\n_{$order->comment}_" : '');
        });

        Telegram::sendMessage([
            'chat_id' => $this->chat->telegram_id,
            'text' => $list->implode("\n\n"),
            'parse_mode' => 'markdown',
        ]);
    }
}
