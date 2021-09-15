<?php


namespace App\Services;


use App\Contracts\OrderContract;
use App\Models\BotState;
use App\Models\Cafe;
use App\Models\Client;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Collection;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

/**
 * Class TelegramBotService
 * @package App\Services\Telegram
 */
class OrderService implements OrderContract
{
    /** @var BotState|null  */
    private ?BotState $chat;

    /** @var Cafe|null  */
    private ?Cafe $cafe;

    /** @var Client|null  */
    private ?Client $client;

    /**
     * @param BotState|null $botState
     * @return $this
     */
    public function setChat(?BotState $botState)
    {
        $this->chat = $botState ?? null;

        return $this;
    }

    /**
     * @param Cafe $cafe
     * @return $this
     */
    public function setCafe(?Cafe $cafe)
    {
        $this->cafe = $cafe ?? null;

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
     * @param string $text
     * @return int
     */
    public function handle(string $text): int
    {
        $state = $this->chat->state;

        switch ($state) {
            case BotState::STATE_ORDER_CHOOSE_CAFE:
                $this->cafe = Cafe::where('ru_name', $text)->orWhere('ua_name', $text)->first();

                if ($this->cafe) {
                    $this->order(BotState::STATE_ORDER_CHOOSE_CAFE);
                }
                break;

            case BotState::STATE_ORDER_STARTED:
            case BotState::STATE_ORDER_EDITING:
                $order = Order::with('products')
                    ->whereClientId($this->client->id)
                    ->whereStatus(Order::STATUS_CREATING)
                    ->latest(Order::UPDATED_AT)
                    ->first();

                if ($order) {
                    switch ($text) {
                        case trans('bot\order.accept'):
                            $this->acceptOrder($order);
                            break;
                        case trans('bot\order.remove'):
                            $this->removeProduct();
                            break;
                        case trans('bot\order.add'):
                            $this->addProduct();
                            break;
                        case trans('bot\order.cancel'):
                            $this->cancel($order);
                            $state = BotState::STATE_MAIN_MENU;
                            break;
                    }
                } else {
                    switch ($text) {
                        case trans('bot\order.accept'):
                        case trans('bot\order.remove'):
                        case trans('bot\order.add'):
                            Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\order.empty')]);
                            break;
                        case trans('bot\order.cancel'):
                            $this->chat->update(['state' => BotState::STATE_MAIN_MENU]);
                            $state = BotState::STATE_MAIN_MENU;
                            break;
                    }
                }
                break;

            case BotState::STATE_ORDER_ACCEPTING:
                $order = Order::with('products')
                    ->whereClientId($this->client->id)
                    ->whereStatus(Order::STATUS_CREATING)
                    ->latest(Order::UPDATED_AT)
                    ->first();

                if ($order) {
                    switch ($text) {
                        case trans('bot\order.apply'):
                            $this->chat->update(['state' => BotState::STATE_MAIN_MENU]);
                            $this->sendOrder($order);
                            $state = BotState::STATE_MAIN_MENU;
                            break;
                        case trans('bot\order.back'):
                            $this->chat->update(['state' => BotState::STATE_ORDER_STARTED]);
                            $this->back();
                            break;
                    }
                }
                break;

            default:
                break;
        }

        return $state;
    }

    /**
     * @param int $stage
     */
    public function order(int $stage = BotState::STATE_ORDER_NEW)
    {
        $messages = (new Collection([
            'cafe' => trans('bot\order.cafe'),
            'start-menu' => trans('bot\order.start'),
        ]))->map(function ($item) {
            return ['chat_id' => $this->chat->telegram_id, 'text' => $item];
        });

        switch ($stage) {
            case BotState::STATE_ORDER_NEW:
                $this->chat->update(['state' => BotState::STATE_ORDER_CHOOSE_CAFE]);

                $cafes = Cafe::all(['id', 'ru_name', 'ua_name']);
                $cafes->map(fn(Cafe $cafe) => $cafe->name = $cafe->getName($this->client->locale));
                $cafeKeyboard = Keyboard::make()->setOneTimeKeyboard(true);

                foreach ($cafes as $cafe) {
                    $cafeKeyboard->row(Keyboard::button($cafe->name));
                }

                Telegram::sendMessage($messages->get('cafe') + ['reply_markup' => $cafeKeyboard]);
                break;

            case BotState::STATE_ORDER_CHOOSE_CAFE:
                $this->chat->update(['state' => BotState::STATE_ORDER_STARTED]);

                $orderButtons = Keyboard::make()
                    ->row(Keyboard::button(trans('bot\order.cancel')));

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => 'Кафе: *' . $this->cafe->getName($this->client->locale) . '*',
                    'parse_mode' => 'markdown',
                    'reply_markup' => $orderButtons
                ]);

                $this->makeMenu($this->cafe->menu);

                break;
        }
    }

    /**
     * @param Menu $menu
     */
    public function makeMenu(Menu $menu)
    {
        foreach ($menu->categories as $category) {
            $menuKeyboard = Keyboard::make()->inline();

            foreach ($category->products->chunk(2) as $productChunk) {
                if ($productChunk->count() === 1) {
                    $productFirst = $productChunk->first();
                    $menuKeyboard->row(Keyboard::inlineButton([
                        'text' => $productFirst->getDisplayNamePrice($this->client->locale),
                        'callback_data' => $productFirst->id,
                    ]));
                } elseif ($productChunk->count() === 2) {
                    $productFirst = $productChunk->first();
                    $productLast = $productChunk->last();

                    $menuKeyboard->row(
                        Keyboard::inlineButton([
                            'text' => $productFirst->getDisplayNamePrice($this->client->locale),
                            'callback_data' => $productFirst->id,
                        ]),
                        Keyboard::inlineButton([
                            'text' => $productLast->getDisplayNamePrice($this->client->locale),
                            'callback_data' => $productLast->id,
                        ])
                    );
                }
            }

            Telegram::sendMessage([
                'chat_id' => $this->chat->telegram_id,
                'text' => '*' . $category->getName($this->client->locale) . '*',
                'parse_mode' => 'markdown',
                'reply_markup' => $menuKeyboard
            ]);
        }
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function makeOrder(Product $product)
    {
        $cafe = $product->category->menu->cafe;

        $order = Order::with('products')
            ->whereClientId($this->client->id)
            ->whereCafeId($cafe->id)
            ->whereStatus(Order::STATUS_CREATING)
            ->latest(Order::UPDATED_AT)
            ->first();

        if ($order) {
            if ($order->cafe_id !== $cafe->id) {
                return Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => trans('bot\order.bruh'),
                    'parse_mode' => 'markdown'
                ]);
            }

            $order->price += $product->price;
        } else {
            $orderButtons = Keyboard::make()
                ->row(Keyboard::button(trans('bot\order.accept')))
                ->row(Keyboard::button(trans('bot\order.remove')))
                ->row(Keyboard::button(trans('bot\order.cancel')));

            Telegram::sendMessage([
                'chat_id' => $this->chat->telegram_id,
                'text' => trans('bot\order.started'),
                'parse_mode' => 'markdown',
                'reply_markup' => $orderButtons
            ]);

            $order = Order::create([
                'cafe_id' => $cafe->id,
                'client_id' => $this->client->id,
                'status' => Order::STATUS_CREATING,
                'price' => $product->price
            ]);
        }

        $order->addProduct($product);

        $products = $order->products()
            ->get(['id', 'ru_name', 'ua_name', 'price'])
            ->map(function (Product $product) {
                return $product->getName($this->client->locale) . ' (' . $product->pivot->amount . 'шт.) - ' . ($product->price * $product->pivot->amount) . ' ₴';
            });

        $text = "*" . trans('bot\order.your-order') . ":*\n\n" . $products->implode("\n") . "\n\n*" . trans('bot\order.total') . ": " . $order->price . ' ₴*';

        if ($order->message_id) {
            Telegram::editMessageText([
                'chat_id' => $this->chat->telegram_id,
                'message_id' => $order->message_id,
                'text' => $text,
                'parse_mode' => 'markdown',
            ]);
        } else {
            $order->message_id = Telegram::sendMessage([
                'chat_id' => $this->chat->telegram_id,
                'text' => $text,
                'parse_mode' => 'markdown'
            ])->message_id;
        }

        return $order->save();
    }

    /**
     * @param Product $product
     * @return mixed
     */
    public function editOrder(Product $product)
    {
        $cafe = $product->category->menu->cafe;

        $order = Order::with('products')
            ->whereClientId($this->client->id)
            ->whereCafeId($cafe->id)
            ->whereStatus(Order::STATUS_CREATING)
            ->latest(Order::UPDATED_AT)
            ->first();

        if ($order === null) {
            return Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\order.start-before')]);
        }

        if ($order->cafe_id !== $cafe->id) {
            return Telegram::sendMessage([
                'chat_id' => $this->chat->telegram_id,
                'text' => trans('bot\order.bruh'),
                'parse_mode' => 'markdown'
            ]);
        }

        if ($order->products()->doesntExist()) {
            return Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\order.already-empty')]);
        }

        if ($order->removeProduct($product)) {
            $order->price -= $product->price;
        } else {
            return Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\order.wrong-product')]);
        }

        $products = $order->products()
            ->get(['id', 'ru_name', 'ua_name', 'price'])
            ->map(function (Product $product) {
                return $product->getName($this->client->locale) . ' (' . $product->pivot->amount . 'шт.) - ' . ($product->price * $product->pivot->amount) . ' ₴';
            });

        $text = "*" . trans('bot\order.your-order') . ":*\n\n" . $products->implode("\n") . "\n\n*" . trans('bot\order.total') . ": " . $order->price . ' ₴*';

        Telegram::editMessageText([
            'chat_id' => $this->chat->telegram_id,
            'message_id' => $order->message_id,
            'text' => $text,
            'parse_mode' => 'markdown',
        ]);

        return $order->save();
    }

    /**
     * @param Order $order
     */
    public function acceptOrder(Order $order)
    {
        $this->chat->update(['state' => BotState::STATE_ORDER_ACCEPTING]);

        $acceptButtons = Keyboard::make()
            ->row(Keyboard::button(trans('bot\order.apply')))
            ->row(Keyboard::button(trans('bot\order.back')));

        Telegram::sendMessage([
            'chat_id' => $this->chat->telegram_id,
            'text' => trans('bot\order.order') . ' *№' . $order->id . '*',
            'parse_mode' => 'markdown',
            'reply_markup' => $acceptButtons
        ]);

        Telegram::forwardMessage([
            'message_id' => $order->message_id,
            'from_chat_id' => $this->chat->telegram_id,
            'chat_id' => $this->chat->telegram_id,
        ]);
    }

    public function removeProduct()
    {
        $this->chat->update(['state' => BotState::STATE_ORDER_EDITING]);

        $orderButtons = Keyboard::make()
            ->row(Keyboard::button(trans('bot\order.accept')))
            ->row(Keyboard::button(trans('bot\order.add')))
            ->row(Keyboard::button(trans('bot\order.cancel')));

        Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\order.use-menu-to-remove'), 'reply_markup' => $orderButtons]);
    }

    public function addProduct()
    {
        $this->chat->update(['state' => BotState::STATE_ORDER_STARTED]);

        $orderButtons = Keyboard::make()
            ->row(Keyboard::button(trans('bot\order.accept')))
            ->row(Keyboard::button(trans('bot\order.remove')))
            ->row(Keyboard::button(trans('bot\order.cancel')));

        Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\order.use-menu-to-add'), 'reply_markup' => $orderButtons]);
    }

    /**
     * @param Order $order
     */
    public function cancel(Order $order)
    {
        Telegram::deleteMessage(['chat_id' => $this->chat->telegram_id, 'message_id' => $order->message_id]);

        $order->products()->delete();
        $order->delete();

        Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\order.canceled')]);

        $this->chat->update(['state' => BotState::STATE_MAIN_MENU]);
    }

    public function back()
    {
        $orderButtons = Keyboard::make()
            ->row(Keyboard::button(trans('bot\order.accept')))
            ->row(Keyboard::button(trans('bot\order.remove')))
            ->row(Keyboard::button(trans('bot\order.cancel')));

        Telegram::sendMessage([
            'chat_id' => $this->chat->telegram_id,
            'text' => trans('bot\order.can-resume'),
            'reply_markup' => $orderButtons
        ]);
    }

    /**
     * @param Order $order
     */
    public function sendOrder(Order $order)
    {
        $order->update([
            'status' => Order::STATUS_CREATED
        ]);

        Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\order.order') . ' *№ ' . $order->id . '* ' . trans('bot\order.send-to') . ' *' . $order->cafe->getName($this->client->locale) . '*!', 'parse_mode' => 'markdown']);
    }
}
