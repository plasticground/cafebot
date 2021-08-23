<?php


namespace App\Services;



use App\Contracts\BotContract;
use App\Models\BotState;
use App\Models\Cafe;
use App\Models\Client;
use App\Models\Location;
use App\Models\LocationName;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

/**
 * Class TelegramBotService
 * @package App\Services\Telegram
 */
class BotService implements BotContract
{
    public const LANG_UA = 'Українська';
    public const LANG_RU = 'Русский';

    /** @var string  */
    private string $locale;

    /** @var BotState|null  */
    private ?BotState $chat;

    /** @var Cafe|null  */
    private ?Cafe $cafe;

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
     * @param string $locale
     * @return $this
     */
    public function setLocale(?string $locale)
    {
        $this->locale = $locale ?? 'ru';

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

    private function handleMessage(Update $update)
    {
        $message = $update->getMessage();
        $this->setChat(BotState::find($message->from->id));

        if ($client = Client::whereTelegramId($message->from->id)->first()) {
            $this->setClient($client);
            $this->setLocale($client->locale);
        }

        if ($this->chat) {
            $text = $message->text ?? '';

            if (empty($text)) {
                abort(400, 'Empty text');
            }

            if ($this->chat->state > BotState::STATE_REGISTRATION_START && $this->chat->state < BotState::STATE_MAIN_MENU) {
                switch ($this->chat->state) {
                    case BotState::STATE_REGISTRATION_LANGUAGE:

                        if ($client === null) {
                            $client = new Client([
                                'telegram_id' => $message->from->id,
                                'telegram_username' => $message->from->username,
                            ]);
                        }

                        switch ($text) {
                            case self::LANG_UA:
                                $client->locale = 'ua';

                                Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Выбран украинский']);
                                $this->registration(BotState::STATE_REGISTRATION_LANGUAGE);

                                break;
                            case self::LANG_RU:
                                $client->locale = 'ru';

                                Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Выбран русский']);
                                $this->registration(BotState::STATE_REGISTRATION_LANGUAGE);

                                break;

                            default:
                                abort(400);
                                break;
                        }

                        $client->save();

                        break;

                    case BotState::STATE_REGISTRATION_NAME:
                        if (
                        $this->validate(
                            ['name' => $text],
                            ['name' => 'required|string'],
                            [],
                            $this->chat->telegram_id
                        )
                        ) {
                            $client->update(['name' => $text]);
                            Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Ваше имя: ' . $text]);
                            $this->registration(BotState::STATE_REGISTRATION_NAME);
                        }

                        break;

                    case BotState::STATE_REGISTRATION_PHONE:
                        if (
                        $this->validate(
                            ['phone' => $text],
                            ['phone' => ['string', 'min:10', 'regex:/^([0-9\s\-\+\(\)]*)$/']],
                            [],
                            $this->chat->telegram_id
                        )
                        ) {
                            $client->update(['phone' => $text]);
                            Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Ваш телефон: ' . $text]);
                            $this->registration(BotState::STATE_REGISTRATION_PHONE);
                        }
                        break;

                    case BotState::STATE_REGISTRATION_LOCATION_MAIN:
                        if (
                        $this->validate(
                            ['name' => $text],
                            ['name' => 'required|string'],
                            [],
                            $this->chat->telegram_id
                        )
                        ) {
                            $location = LocationName::where('ru_name', $text)->orWhere('ua_name', $text)->first();

                            Location::create([
                                'client_id' => $client->id,
                                'location_name_id' => $location->id,
                            ]);
                        }

                        Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Место доставки: ' . $text]);
                        $this->registration(BotState::STATE_REGISTRATION_LOCATION_MAIN);
                        break;

                    case BotState::STATE_REGISTRATION_LOCATION_SUB_1:
                        if (
                        $this->validate(
                            ['sub1' => $text],
                            ['sub1' => 'required|string'],
                            [],
                            $this->chat->telegram_id
                        )
                        ) {
                            Location::whereHas('client', function (Builder $builder) use ($message) {
                                return $builder->whereTelegramId($message->from->id);
                            })
                                ->first()
                                ->update(['sub1' => $text]);
                        }

                        Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Ваш ряд: ' . $text]);
                        $this->registration(BotState::STATE_REGISTRATION_LOCATION_SUB_1);
                        break;

                    case BotState::STATE_REGISTRATION_LOCATION_SUB_2:
                        if (
                        $this->validate(
                            ['sub2' => $text],
                            ['sub2' => 'required|numeric'],
                            [],
                            $this->chat->telegram_id
                        )
                        ) {
                            Location::whereHas('client', function (Builder $builder) use ($message) {
                                return $builder->whereTelegramId($message->from->id);
                            })
                                ->first()
                                ->update(['sub2' => $text]);
                            Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Ваш контейнер: ' . $text]);
                            $this->registration(BotState::STATE_REGISTRATION_LOCATION_SUB_2);
                        }
                        break;

                    default:
                        break;
                }
            } elseif ($this->chat->state >= BotState::STATE_ORDER_NEW && $this->chat->state <= BotState::STATE_ORDER_FINISHED) {
                switch ($this->chat->state) {
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
                                case 'Подтвердить заказ':
                                    $this->chat->update(['state' => BotState::STATE_ORDER_ACCEPTING]);

                                    $this->acceptOrder($order);
                                    break;
                                case 'Убрать блюдо':
                                    $this->chat->update(['state' => BotState::STATE_ORDER_EDITING]);

                                    $orderButtons = Keyboard::make()
                                        ->row(Keyboard::button('Подтвердить заказ'))
                                        ->row(Keyboard::button('Добавить блюдо'))
                                        ->row(Keyboard::button('Я передумал'));

                                    Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Используйте меню, чтобы убрать блюдо из заказа', 'reply_markup' => $orderButtons]);
                                    break;
                                case 'Добавить блюдо':
                                    $this->chat->update(['state' => BotState::STATE_ORDER_STARTED]);

                                    $orderButtons = Keyboard::make()
                                        ->row(Keyboard::button('Подтвердить заказ'))
                                        ->row(Keyboard::button('Убрать блюдо'))
                                        ->row(Keyboard::button('Я передумал'));

                                    Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Используйте меню, чтобы добавить блюдо в заказ', 'reply_markup' => $orderButtons]);
                                    break;
                                case 'Я передумал':
                                    Telegram::deleteMessage(['chat_id' => $this->chat->telegram_id, 'message_id' => $order->message_id]);

                                    $order->products()->delete();
                                    $order->delete();

                                    Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Заказ отменён']);

                                    $this->chat->update(['state' => BotState::STATE_MAIN_MENU]);
                                    $this->mainMenu();
                                    break;
                            }
                        } else {
                            switch ($text) {
                                case 'Подтвердить заказ':
                                case 'Убрать блюдо':
                                case 'Добавить блюдо':
                                    Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'У вас пустой заказ']);
                                    break;
                                case 'Я передумал':
                                    $this->chat->update(['state' => BotState::STATE_MAIN_MENU]);
                                    $this->mainMenu();
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
                                case 'Да, всё верно':
                                    $this->chat->update(['state' => BotState::STATE_MAIN_MENU]);
                                    $this->sendOrder($order);
                                    break;
                                case 'Нет, вернуться назад':
                                    $this->chat->update(['state' => BotState::STATE_ORDER_STARTED]);

                                    $orderButtons = Keyboard::make()
                                        ->row(Keyboard::button('Подтвердить заказ'))
                                        ->row(Keyboard::button('Убрать блюдо'))
                                        ->row(Keyboard::button('Я передумал'));

                                    Telegram::sendMessage([
                                        'chat_id' => $this->chat->telegram_id,
                                        'text' => 'Вы можете продолжить собирать заказ',
                                        'reply_markup' => $orderButtons
                                    ]);

                                    break;
                            }
                        }
                        break;

                    default:
                        break;
                }
            } elseif ($this->chat->state >= BotState::STATE_SETTINGS && $this->chat->state <= BotState::STATE_SETTINGS_LANGUAGE) {
                switch ($this->chat->state) {
                    case BotState::STATE_SETTINGS:
                        switch ($text) {
                            case 'Имя':
                                $this->settings(BotState::STATE_SETTINGS_NAME);
                                break;
                            case 'Телефон':
                                $this->settings(BotState::STATE_SETTINGS_PHONE);
                                break;
                            case 'Место работы':
                                $this->settings(BotState::STATE_SETTINGS_LOCATION);
                                break;
                            case 'Язык':
                                $this->settings(BotState::STATE_SETTINGS_LANGUAGE);
                                break;
                            case 'Я передумал':
                                $this->mainMenu();
                                break;
                        }
                        break;
                    case BotState::STATE_SETTINGS_LANGUAGE:
                        switch ($text) {
                            case self::LANG_UA:
                                $client->update(['locale' => 'ua']);

                                Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Выбран украинский']);
                                $this->mainMenu();

                                break;
                            case self::LANG_RU:
                                $client->update(['locale' => 'ru']);

                                Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Выбран русский']);
                                $this->mainMenu();

                                break;
                        }

                        break;

                    case BotState::STATE_SETTINGS_NAME:
                        if (
                        $this->validate(
                            ['name' => $text],
                            ['name' => 'required|string'],
                            [],
                            $this->chat->telegram_id
                        )
                        ) {
                            $client->update(['name' => $text]);
                            Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Ваше имя: ' . $text]);
                            $this->mainMenu();
                        }

                        break;

                    case BotState::STATE_SETTINGS_PHONE:
                        if (
                        $this->validate(
                            ['phone' => $text],
                            ['phone' => ['string', 'min:10', 'regex:/^([0-9\s\-\+\(\)]*)$/']],
                            [],
                            $this->chat->telegram_id
                        )
                        ) {
                            $client->update(['phone' => $text]);
                            Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Ваш телефон: ' . $text]);
                            $this->mainMenu();
                        }
                        break;

                    case BotState::STATE_SETTINGS_LOCATION:
                        switch ($text) {
                            case 'Место доставки':
                                $this->settings(BotState::STATE_SETTINGS_LOCATION_MAIN);
                                break;
                            case 'Ряд':
                                $this->settings(BotState::STATE_SETTINGS_LOCATION_SUB1);
                                break;
                            case 'Контейнер':
                                $this->settings(BotState::STATE_SETTINGS_LOCATION_SUB2);
                                break;
                        }

                    case BotState::STATE_SETTINGS_LOCATION_MAIN:
                        if (
                        $this->validate(
                            ['name' => $text],
                            ['name' => 'required|string'],
                            [],
                            $this->chat->telegram_id
                        )
                        ) {
                            $location = LocationName::where('ru_name', $text)->orWhere('ua_name', $text)->first();

                            Location::whereHas('client', function (Builder $builder) use ($message) {
                                return $builder->whereTelegramId($this->client->telegram_id);
                            })
                                ->first()
                                ->update(['location_name_id' => $location->id]);
                        }

                        Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Место доставки: ' . $text]);
                        $this->mainMenu();
                        break;

                    case BotState::STATE_SETTINGS_LOCATION_SUB1:
                        if (
                        $this->validate(
                            ['sub1' => $text],
                            ['sub1' => 'required|string'],
                            [],
                            $this->chat->telegram_id
                        )
                        ) {
                            Location::whereHas('client', function (Builder $builder) use ($message) {
                                return $builder->whereTelegramId($this->client->telegram_id);
                            })
                                ->first()
                                ->update(['sub1' => $text]);
                        }

                        Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Ваш ряд: ' . $text]);
                        $this->mainMenu();
                        break;

                    case BotState::STATE_SETTINGS_LOCATION_SUB2:
                        if (
                        $this->validate(
                            ['sub2' => $text],
                            ['sub2' => 'required|numeric'],
                            [],
                            $this->chat->telegram_id
                        )
                        ) {
                            Location::whereHas('client', function (Builder $builder) use ($message) {
                                return $builder->whereTelegramId($this->client->telegram_id);
                            })
                                ->first()
                                ->update(['sub2' => $text]);
                            Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Ваш контейнер: ' . $text]);
                            $this->mainMenu();
                        }
                        break;

                    default:
                        break;
                }
            }
        }
    }

    private function handleCallbackQuery(Update $update)
    {
        $callbackQuery = $update->callbackQuery;
        $this->setChat(BotState::find($callbackQuery->from->id));

        if ($client = Client::whereTelegramId($callbackQuery->from->id)->first()) {
            $this->setClient($client);
            $this->setLocale($client->locale);
        }

        if ($this->chat) {
            if ($this->chat->state >= BotState::STATE_ORDER_NEW && $this->chat->state <= BotState::STATE_ORDER_FINISHED) {
                switch ($this->chat->state) {
                    case BotState::STATE_ORDER_STARTED:
                        $product = Product::findOrFail($callbackQuery->data);

                        $this->makeOrder($product);
                        break;

                    case BotState::STATE_ORDER_EDITING:
                        $product = Product::findOrFail($callbackQuery->data);

                        $this->editOrder($product);
                        break;

                    default:
                        break;
                }
            }
        }
    }

    public function registration(int $stage = BotState::STATE_REGISTRATION_START)
    {
        $messages = (new Collection([
            'start' => 'Зравствуйте, сперва нужно зарегистрироваться',
            'action-1' => 'Выберите язык:',
            'action-2' => 'Как к вам обращаться:',
            'action-3' => 'Напишите свой телефон:',
            'action-4' => 'Выберите место доставки:',
            'action-5' => 'В каком ряду вы находитесь:',
            'action-6' => 'И последнее, напишите номер контейтера:',
            'done' => 'Спасибо! Теперь вы можете сделать заказ ☺'
        ]))->map(function ($item) {
            return ['chat_id' => $this->chat->telegram_id, 'text' => $item];
        });

        switch ($stage) {
            case BotState::STATE_REGISTRATION_START:
                $langKeyboard = Keyboard::make()->setOneTimeKeyboard(true);
                $langKeyboard->row(
                    Keyboard::button(self::LANG_UA),
                    Keyboard::button(self::LANG_RU),
                );

                $this->chat->update(['state' => BotState::STATE_REGISTRATION_LANGUAGE]);

                Telegram::sendMessage($messages->get('start'));
                Telegram::sendMessage($messages->get('action-1') + ['reply_markup' => $langKeyboard]);
                break;

            case BotState::STATE_REGISTRATION_LANGUAGE:
                $langKeyboard = Keyboard::remove();
                $this->chat->update(['state' => BotState::STATE_REGISTRATION_NAME]);

                Telegram::sendMessage($messages->get('action-2') + ['reply_markup' => $langKeyboard]);
            break;

            case BotState::STATE_REGISTRATION_NAME:
                $this->chat->update(['state' => BotState::STATE_REGISTRATION_PHONE]);

                Telegram::sendMessage($messages->get('action-3'));
            break;

            case BotState::STATE_REGISTRATION_PHONE:
                $locations = LocationName::all(['id', 'ru_name', 'ua_name']);
                $locations->map(fn(LocationName $location) => $location->name = $location->getName($this->locale));
                $locationKeyboard = Keyboard::make()->setOneTimeKeyboard(true);

                foreach ($locations as $location) {
                    $locationKeyboard->row(Keyboard::button($location->name));
                }

                $this->chat->update(['state' => BotState::STATE_REGISTRATION_LOCATION_MAIN]);

                Telegram::sendMessage($messages->get('action-4') + ['reply_markup' => $locationKeyboard]);
            break;

            case BotState::STATE_REGISTRATION_LOCATION_MAIN:
                $locationKeyboard = Keyboard::remove();
                $this->chat->update(['state' => BotState::STATE_REGISTRATION_LOCATION_SUB_1]);

                Telegram::sendMessage($messages->get('action-5') + ['reply_markup' => $locationKeyboard]);
            break;

            case BotState::STATE_REGISTRATION_LOCATION_SUB_1:
                $this->chat->update(['state' => BotState::STATE_REGISTRATION_LOCATION_SUB_2]);

                Telegram::sendMessage($messages->get('action-6'));
            break;

            case BotState::STATE_REGISTRATION_LOCATION_SUB_2:
                Telegram::sendMessage($messages->get('done'));
                $this->mainMenu();
            break;
        }
    }

    public function mainMenu()
    {
        $messages = (new Collection([
            'main-menu' => 'Вы перешли в главное меню',
        ]))->map(function ($item) {
            return ['chat_id' => $this->chat->telegram_id, 'text' => $item];
        });

        $this->chat->update(['state' => BotState::STATE_MAIN_MENU]);

        $menu = Keyboard::make();
        $menu->row(Keyboard::button('/order - Сделать заказ'));
        $menu->row(Keyboard::button('/history - Мои заказы'), Keyboard::button('/feedback - Обратная связь'));
        $menu->row(Keyboard::button('/settings - Настройки и информация'));

        Telegram::sendMessage($messages->get('main-menu') + ['reply_markup' => $menu]);
    }

    public function order(int $stage = BotState::STATE_ORDER_NEW)
    {
        $messages = (new Collection([
            'cafe' => 'Выберите кафе',
            'start-menu' => 'Начинаем собирать заказ',
        ]))->map(function ($item) {
            return ['chat_id' => $this->chat->telegram_id, 'text' => $item];
        });

        switch ($stage) {
            case BotState::STATE_ORDER_NEW:
                $this->chat->update(['state' => BotState::STATE_ORDER_CHOOSE_CAFE]);

                $cafes = Cafe::all(['id', 'ru_name', 'ua_name']);
                $cafes->map(fn(Cafe $cafe) => $cafe->name = $cafe->getName($this->locale));
                $cafeKeyboard = Keyboard::make()->setOneTimeKeyboard(true);

                foreach ($cafes as $cafe) {
                    $cafeKeyboard->row(Keyboard::button($cafe->name));
                }

                Telegram::sendMessage($messages->get('cafe') + ['reply_markup' => $cafeKeyboard]);
                break;

            case BotState::STATE_ORDER_CHOOSE_CAFE:
                $this->chat->update(['state' => BotState::STATE_ORDER_STARTED]);

                $orderButtons = Keyboard::make()
                    ->row(Keyboard::button('Я передумал'));

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => 'Кафе: *' . $this->cafe->getName($this->locale) . '*',
                    'parse_mode' => 'markdown',
                    'reply_markup' => $orderButtons
                ]);

                $this->makeMenu($this->cafe->menu);

                break;
        }
    }

    private function makeMenu(Menu $menu)
    {
        foreach ($menu->categories as $category) {
            $menuKeyboard = Keyboard::make()->inline();

            foreach ($category->products->chunk(2) as $productChunk) {
                if ($productChunk->count() === 1) {
                    $productFirst = $productChunk->first();
                    $menuKeyboard->row(Keyboard::inlineButton([
                        'text' => $productFirst->getDisplayNamePrice($this->locale),
                        'callback_data' => $productFirst->id,
                    ]));
                } elseif ($productChunk->count() === 2) {
                    $productFirst = $productChunk->first();
                    $productLast = $productChunk->last();

                    $menuKeyboard->row(
                        Keyboard::inlineButton([
                            'text' => $productFirst->getDisplayNamePrice($this->locale),
                            'callback_data' => $productFirst->id,
                        ]),
                        Keyboard::inlineButton([
                            'text' => $productLast->getDisplayNamePrice($this->locale),
                            'callback_data' => $productLast->id,
                        ])
                    );
                }
            }

            Telegram::sendMessage([
                'chat_id' => $this->chat->telegram_id,
                'text' => '*' . $category->getName($this->locale) . '*',
                'parse_mode' => 'markdown',
                'reply_markup' => $menuKeyboard
            ]);
        }
    }

    private function makeOrder(Product $product)
    {
        $order = Order::with('products')
            ->whereClientId($this->client->id)
            ->whereStatus(Order::STATUS_CREATING)
            ->latest(Order::UPDATED_AT)
            ->first();

        if ($order) {
            $order->price += $product->price;
        } else {
            $orderButtons = Keyboard::make()
                ->row(Keyboard::button('Подтвердить заказ'))
                ->row(Keyboard::button('Убрать блюдо'))
                ->row(Keyboard::button('Я передумал'));

            Telegram::sendMessage([
                'chat_id' => $this->chat->telegram_id,
                'text' => 'Вы начали собирать заказ',
                'parse_mode' => 'markdown',
                'reply_markup' => $orderButtons
            ]);

            $order = Order::create([
                'client_id' => $this->client->id,
                'status' => Order::STATUS_CREATING,
                'price' => $product->price
            ]);
        }

        $order->addProduct($product);

        $products = $order->products()
            ->get(['id', 'ru_name', 'ua_name', 'price'])
            ->map(function (Product $product) {
                return $product->getName($this->locale) . ' (' . $product->pivot->amount . 'шт.) - ' . ($product->price * $product->pivot->amount) . ' ₴';
            });

        $text = "*Ваш заказ:*\n\n" . $products->implode("\n") . "\n\n*Итого: " . $order->price . ' ₴*';

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

    private function editOrder(Product $product)
    {
        $order = Order::with('products')
            ->whereClientId($this->client->id)
            ->whereStatus(Order::STATUS_CREATING)
            ->latest(Order::UPDATED_AT)
            ->first();

        if ($order === null) {
            return Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Сначала начните собирать заказ']);
        }

        if ($order->products()->doesntExist()) {
            return Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'У вас уже пустой заказ']);
        }

        if ($order->removeProduct($product)) {
            $order->price -= $product->price;
        } else {
            return Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'В вашем заказе нет этого блюда']);
        }

        $products = $order->products()
            ->get(['id', 'ru_name', 'ua_name', 'price'])
            ->map(function (Product $product) {
                return $product->getName($this->locale) . ' (' . $product->pivot->amount . 'шт.) - ' . ($product->price * $product->pivot->amount) . ' ₴';
            });

        $text = "*Ваш заказ:*\n\n" . $products->implode("\n") . "\n\n*Итого: " . $order->price . ' ₴*';

        Telegram::editMessageText([
            'chat_id' => $this->chat->telegram_id,
            'message_id' => $order->message_id,
            'text' => $text,
            'parse_mode' => 'markdown',
        ]);

        return $order->save();
    }

    private function acceptOrder(Order $order)
    {
        $acceptButtons = Keyboard::make()
            ->row(Keyboard::button('Да, всё верно'))
            ->row(Keyboard::button('Нет, вернуться назад'));

        Telegram::sendMessage([
            'chat_id' => $this->chat->telegram_id,
            'text' => 'Заказ *№' . $order->id . '*',
            'parse_mode' => 'markdown',
            'reply_markup' => $acceptButtons
        ]);

        Telegram::forwardMessage([
            'message_id' => $order->message_id,
            'from_chat_id' => $this->chat->telegram_id,
            'chat_id' => $this->chat->telegram_id,
        ]);
    }

    private function sendOrder(Order $order)
    {
        $order->update([
            'status' => Order::STATUS_CREATED
        ]);
        Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => 'Заказ *№ ' . $order->id . '* отправлен в рестик!', 'parse_mode' => 'markdown']);
        $this->mainMenu();
    }

    public function settings(int $stage = BotState::STATE_SETTINGS)
    {
        switch ($stage) {
            case BotState::STATE_SETTINGS:
                $this->chat->update(['state' => BotState::STATE_SETTINGS]);

                $settingsButtons = Keyboard::make()
                    ->row(Keyboard::button('Имя'), Keyboard::button('Телефон'))
                    ->row(Keyboard::button('Место работы'), Keyboard::button('Язык'))
                    ->row(Keyboard::button('Я передумал'));

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => "Выберите что вы хотите изменить:\n"
                    . $this->client->name . "\n"
                    . $this->client->phone . "\n"
                    . $this->client->location->locationName->getName($this->locale) . ': ' . $this->client->location->sub1 . ' - ' . $this->client->location->sub2 . "\n"
                    . ($this->locale === 'ua' ? self::LANG_UA : self::LANG_RU),
                    'reply_markup' => $settingsButtons,
                    'parse_mode' => 'markdown',
                ]);

                break;
            case BotState::STATE_SETTINGS_NAME:
                $this->chat->update(['state' => BotState::STATE_SETTINGS_NAME]);

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => 'Введите новое имя',
                    'reply_markup' => Keyboard::remove()
                ]);

                break;
            case BotState::STATE_SETTINGS_PHONE:
                $this->chat->update(['state' => BotState::STATE_SETTINGS_PHONE]);

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => 'Введите новый телефон',
                    'reply_markup' => Keyboard::remove()
                ]);

                break;
            case BotState::STATE_SETTINGS_LOCATION:
                $this->chat->update(['state' => BotState::STATE_SETTINGS_LOCATION]);

                $settingsButtons = Keyboard::make()
                    ->row(Keyboard::button('Место доставки'))
                    ->row(Keyboard::button('Ряд'))
                    ->row(Keyboard::button('Контейнер'));

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => "Выберите что вы хотите изменить:\n"
                        . $this->client->location->locationName->getName($this->locale) . ': '
                        . $this->client->location->sub1 . ' - ' . $this->client->location->sub2 . "\n",
                    'reply_markup' => $settingsButtons,
                    'parse_mode' => 'markdown',
                ]);

                break;
            case BotState::STATE_SETTINGS_LOCATION_MAIN:
                $this->chat->update(['state' => BotState::STATE_SETTINGS_LOCATION_MAIN]);

                $locations = LocationName::all(['id', 'ru_name', 'ua_name']);
                $locations->map(fn(LocationName $location) => $location->name = $location->getName($this->locale));
                $locationKeyboard = Keyboard::make()->setOneTimeKeyboard(true);

                foreach ($locations as $location) {
                    $locationKeyboard->row(Keyboard::button($location->name));
                }

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => 'Выберите новое место доставки',
                    'reply_markup' => $locationKeyboard
                ]);

                break;
            case BotState::STATE_SETTINGS_LOCATION_SUB1:
                $this->chat->update(['state' => BotState::STATE_SETTINGS_LOCATION_SUB1]);

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => 'Напишите ваш ряд',
                    'reply_markup' => Keyboard::remove()
                ]);

                break;
            case BotState::STATE_SETTINGS_LOCATION_SUB2:
                $this->chat->update(['state' => BotState::STATE_SETTINGS_LOCATION_SUB2]);

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => 'Напишите номер контейнера',
                    'reply_markup' => Keyboard::remove()
                ]);

                break;
            case BotState::STATE_SETTINGS_LANGUAGE:
                $this->chat->update(['state' => BotState::STATE_SETTINGS_LANGUAGE]);

                $langKeyboard = Keyboard::make()->setOneTimeKeyboard(true);
                $langKeyboard->row(
                    Keyboard::button(self::LANG_UA),
                    Keyboard::button(self::LANG_RU),
                );

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => 'Выберите язык',
                    'reply_markup' => $langKeyboard
                ]);

                break;
        }
    }
    /**
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param $id
     * @return bool
     */
    private function validate(array $data, array $rules, array $messages, $id): bool
    {
        try {
            $validator = Validator::make($data, $rules, $messages);
            $validator->validate();
        } catch (\Throwable $exception) {
            Telegram::sendMessage(['chat_id' => $id, 'text' => $exception->getMessage()]);

            return false;
        }

        return true;
    }
}
