<?php


namespace App\Services;



use App\Contracts\BotContract;
use App\Models\BotState;
use App\Models\Client;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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

    /**
     * @param $update
     */
    public function getUpdate(Update $update)
    {
        $message = $update->getMessage();
        $chat = BotState::find($message->from->id);

        if ($chat) {
            if ($chat->state > BotState::STATE_REGISTRATION_START && $chat->state < BotState::STATE_REGISTRATION_DONE) {
                $text = $message->text ?? '';

                if (empty($text)) {
                    abort(400, 'Empty text');
                }

                switch ($chat->state) {
                    case BotState::STATE_REGISTRATION_LANGUAGE:

                        if (($client = Client::whereTelegramId($message->from->id)->first()) === null) {
                            $client = new Client([
                                'telegram_id' => $message->from->id,
                                'telegram_username' => $message->from->username,
                            ]);
                        }

                        switch ($text) {
                            case self::LANG_UA:
                                $client->locale = 'ua';

                                Telegram::sendMessage(['chat_id' => $chat->telegram_id, 'text' => 'Выбран украинский']);
                                $this->registration($chat, 1);

                                break;
                            case self::LANG_RU:
                                $client->locale = 'ru';

                                Telegram::sendMessage(['chat_id' => $chat->telegram_id, 'text' => 'Выбран русский']);
                                $this->registration($chat, 1);

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
                                $chat->telegram_id
                            )
                        ) {
                            Client::whereTelegramId($message->from->id)->update(['name' => $text]);
                            Telegram::sendMessage(['chat_id' => $chat->telegram_id, 'text' => 'Ваше имя: ' . $text]);
                            $this->registration($chat, 2);
                        }

                        break;

                    case BotState::STATE_REGISTRATION_PHONE:
                        if (
                            $this->validate(
                                ['phone' => $text],
                                ['phone' => ['string', 'min:10', 'regex:/^([0-9\s\-\+\(\)]*)$/']],
                                [],
                                $chat->telegram_id
                            )
                        ) {
                            Client::whereTelegramId($message->from->id)->update(['phone' => $text]);
                            Telegram::sendMessage(['chat_id' => $chat->telegram_id, 'text' => 'Ваш телефон: ' . $text]);
                            $this->registration($chat, 3);
                        }
                        break;

                    case BotState::STATE_REGISTRATION_LOCATION_SUB_1:
                        if (
                            $this->validate(
                                ['sub1' => $text],
                                ['sub1' => 'required|string'],
                                [],
                                $chat->telegram_id
                            )
                        ) {
                            $client = Client::whereTelegramId($message->from->id)->first();
                            Location::create([
                                'client_id' => $client->id,
                                'name' => Location::NAME_RYNOK,
                                'sub1' => $text
                            ]);
                        }

                        Telegram::sendMessage(['chat_id' => $chat->telegram_id, 'text' => 'Ваш ряд: ' . $text]);
                        $this->registration($chat, 4);
                        break;

                    case BotState::STATE_REGISTRATION_LOCATION_SUB_2:
                        if (
                            $this->validate(
                                ['sub2' => $text],
                                ['sub2' => 'required|numeric'],
                                [],
                                $chat->telegram_id
                            )
                        ) {
                            Location::whereHas('client', function (Builder $builder) use ($message) {
                                return $builder->whereTelegramId($message->from->id);
                            })
                                ->first()
                                ->update(['sub2' => $text]);
                            Telegram::sendMessage(['chat_id' => $chat->telegram_id, 'text' => 'Ваш контейнер: ' . $text]);
                            $this->registration($chat, 5);
                        }
                        break;
                }
            }
        }
    }

    public function registration($chat, int $stage = 0)
    {
        $messages = (new Collection([
            'start' => 'Зравствуйте, сперва нужно зарегистрироваться',
            'action-1' => 'Выберите язык:',
            'action-2' => 'Как к вам обращаться:',
            'action-3' => 'Напишите свой телефон:',
            'action-4' => 'В каком ряду вы находитесь:',
            'action-5' => 'И последнее, напишите номер контейтера:',
            'done' => 'Спасибо! Теперь вы можете сделать заказ ☺'
        ]))->map(function ($item) use ($chat){
            return ['chat_id' => $chat->telegram_id, 'text' => $item];
        });

        switch ($stage) {
            case 0:
                $langKeyboard = Keyboard::make()->setOneTimeKeyboard(true);
                $langKeyboard->row(
                    Keyboard::button(self::LANG_UA),
                    Keyboard::button(self::LANG_RU),
                );

                $chat->update(['state' => BotState::STATE_REGISTRATION_LANGUAGE]);

                Telegram::sendMessage($messages->get('start'));
                Telegram::sendMessage($messages->get('action-1') + ['reply_markup' => $langKeyboard]);
                break;

            case 1:
            $langKeyboard = Keyboard::remove();
            $chat->update(['state' => BotState::STATE_REGISTRATION_NAME]);

            Telegram::sendMessage($messages->get('action-2') + ['reply_markup' => $langKeyboard]);
            break;

            case 2:
            $chat->update(['state' => BotState::STATE_REGISTRATION_PHONE]);

            Telegram::sendMessage($messages->get('action-3'));
            break;

            case 3:
            $chat->update(['state' => BotState::STATE_REGISTRATION_LOCATION_SUB_1]);

            Telegram::sendMessage($messages->get('action-4'));
            break;

            case 4:
            $chat->update(['state' => BotState::STATE_REGISTRATION_LOCATION_SUB_2]);

            Telegram::sendMessage($messages->get('action-5'));
            break;

            case 5:
            $chat->update(['state' => BotState::STATE_REGISTRATION_DONE]);

            Telegram::sendMessage($messages->get('done'));
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
