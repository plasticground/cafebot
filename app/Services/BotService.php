<?php


namespace App\Services;



use App\Contracts\BotContract;
use App\Models\Chat;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Helpers\Emojify;
use Telegram\Bot\Keyboard\Button;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\MessageEntity;
use Telegram\Bot\Objects\Update;

/**
 * Class TelegramBotService
 * @package App\Services\Telegram
 */
class BotService implements BotContract
{
    /**
     * @param $update
     */
    public function getUpdate(Update $update)
    {
        $message = $update->getMessage();
        $chat = Chat::find($message->chat->id);

        if ($chat) {
            if ($chat->state > Chat::STATE_REGISTRATION_START && $chat->state < Chat::STATE_REGISTRATION_DONE) {
                switch ($chat->state) {
                    case Chat::STATE_REGISTRATION_LANGUAGE:
                        switch ($message->text) {
                            case Emojify::text(':ua:'):
                                Telegram::sendMessage(['chat_id' => $chat->id, 'text' => 'Выбран украинский']);
                                $this->registration($chat, 1);
                                break;
                            case Emojify::text(':ru:'):
                                Telegram::sendMessage(['chat_id' => $chat->id, 'text' => 'Выбран русский']);
                                $this->registration($chat, 1);
                                break;
                        }
                        break;

                    case Chat::STATE_REGISTRATION_NAME:
                        Telegram::sendMessage(['chat_id' => $chat->id, 'text' => 'Ваше имя: ' . $message->text]);
                        $this->registration($chat, 2);
                        break;

                    case Chat::STATE_REGISTRATION_PHONE:
                        Telegram::sendMessage(['chat_id' => $chat->id, 'text' => 'Ваш телефон: ' . $message->text]);
                        $this->registration($chat, 3);
                        break;

                    case Chat::STATE_REGISTRATION_LOCATION_SUB_1:
                        Telegram::sendMessage(['chat_id' => $chat->id, 'text' => 'Ваше ряд: ' . $message->text]);
                        $this->registration($chat, 4);
                        break;

                    case Chat::STATE_REGISTRATION_LOCATION_SUB_2:
                        Telegram::sendMessage(['chat_id' => $chat->id, 'text' => 'Ваше контейнер: ' . $message->text]);
                        $this->registration($chat, 5);
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
            return ['chat_id' => $chat->id, 'text' => $item];
        });

        switch ($stage) {
            case 0:
                $langKeyboard = Keyboard::make()->setOneTimeKeyboard(true);
                $langKeyboard->row(
                    Keyboard::button(['text' => Emojify::text(':ua:'), 'callback_data' => 'registration.action-1.ua']),
                    Keyboard::button(['text' => Emojify::text(':ru:'), 'callback_data' => 'registration.action-1.ru']),
                );
//                $langKeyboard = Keyboard::remove();

                $chat->update(['state' => Chat::STATE_REGISTRATION_LANGUAGE]);

                Telegram::sendMessage($messages->get('start'));
                Telegram::sendMessage($messages->get('action-1') + ['reply_markup' => $langKeyboard]);
                break;

            case 1:
            $langKeyboard = Keyboard::remove();
            $chat->update(['state' => Chat::STATE_REGISTRATION_NAME]);

            Telegram::sendMessage($messages->get('action-2') + ['reply_markup' => $langKeyboard]);
            break;

            case 2:
            $chat->update(['state' => Chat::STATE_REGISTRATION_PHONE]);

            Telegram::sendMessage($messages->get('action-3'));
            break;

            case 3:
            $chat->update(['state' => Chat::STATE_REGISTRATION_LOCATION_SUB_1]);

            Telegram::sendMessage($messages->get('action-4'));
            break;

            case 4:
            $chat->update(['state' => Chat::STATE_REGISTRATION_LOCATION_SUB_2]);

            Telegram::sendMessage($messages->get('action-5'));
            break;

            case 5:
            $chat->update(['state' => Chat::STATE_REGISTRATION_DONE]);

            Telegram::sendMessage($messages->get('done'));
            break;
        }


    }
}
