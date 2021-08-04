<?php


namespace App\Services;



use App\Contracts\BotContract;
use App\Models\BotState;
use App\Models\Client;
use App\Models\Location;
use App\Models\LocationName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Telegram\Bot\Helpers\Emojify;
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
    private ?BotState $chat;

    /**
     * @param BotState $botState
     */
    public function setChat(BotState $botState)
    {
        $this->chat = $botState;

        return $this;
    }
    /**
     * @param Update $update
     */
    public function getUpdate(Update $update)
    {
        $message = $update->getMessage();
        $this->chat = BotState::find($message->from->id);

        if ($client = Client::whereTelegramId($message->from->id)->first()) {
            $this->locale = $client->locale ?? 'ru';
        }

        if ($this->chat) {
            if ($this->chat->state > BotState::STATE_REGISTRATION_START && $this->chat->state < BotState::STATE_MAIN_MENU) {
                $text = $message->text ?? '';

                if (empty($text)) {
                    abort(400, 'Empty text');
                }

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

        $menu = Keyboard::make()->setOneTimeKeyboard(true);
        $menu->row(Keyboard::button('/order - Сделать заказ'));
        $menu->row(Keyboard::button('/history - Мои заказы'), Keyboard::button('/feedback - Обратная связь'));
        $menu->row(Keyboard::button('/settings - Настройки и информация'));

        Telegram::sendMessage($messages->get('main-menu') + ['reply_markup' => $menu]);
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
