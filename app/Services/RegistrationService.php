<?php


namespace App\Services;


use App\Contracts\BotContract;
use App\Contracts\RegistrationContract;
use App\Models\BotState;
use App\Models\Client;
use App\Models\Location;
use App\Models\LocationName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

/**
 * Class TelegramBotService
 * @package App\Services\Telegram
 */
class RegistrationService implements RegistrationContract
{
    /** @var BotState|null  */
    private ?BotState $chat;

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
            case BotState::STATE_REGISTRATION_LANGUAGE:
                switch ($text) {
                    case BotService::LANG_UA:
                        $this->client->locale = 'ua';
                        app('translator')->setLocale('ua');

                        Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\registration.lang')]);
                        $this->registration(BotState::STATE_REGISTRATION_LANGUAGE);

                        break;
                    case BotService::LANG_RU:
                        $this->client->locale = 'ru';
                        app('translator')->setLocale('ru');

                        Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\registration.lang')]);
                        $this->registration(BotState::STATE_REGISTRATION_LANGUAGE);

                        break;

                    default:
                        abort(400);
                        break;
                }

                $this->client->save();

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
                    $this->client->update(['name' => $text]);
                    Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\registration.name') . $text]);
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
                    $this->client->update(['phone' => $text]);
                    Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\registration.phone') . $text]);
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
                        'client_id' => $this->client->id,
                        'location_name_id' => $location->id,
                    ]);
                }

                Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\registration.location.main') . $text]);
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
                    Location::whereHas('client', function (Builder $builder) {
                        return $builder->whereTelegramId($this->chat->telegram_id,);
                    })
                        ->first()
                        ->update(['sub1' => $text]);
                }

                Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\registration.location.sub1') . $text]);
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
                    Location::whereHas('client', function (Builder $builder) {
                        return $builder->whereTelegramId($this->chat->telegram_id,);
                    })
                        ->first()
                        ->update(['sub2' => $text]);
                    Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\registration.location.sub2') . $text]);
                    $this->registration(BotState::STATE_REGISTRATION_LOCATION_SUB_2);
                }
                break;

            default:
                break;
        }

        return $state;
    }

    /**
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param $id
     * @return bool
     */
    public function validate(array $data, array $rules, array $messages, $id): bool
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

    /**
     * @param int $stage
     * @return mixed|void
     */
    public function registration(int $stage = BotState::STATE_REGISTRATION_START)
    {
        $messages = (new Collection([
            'start' => trans('bot\registration.actions.start'),
            'action-1' => trans('bot\registration.actions.1'),
            'action-2' => trans('bot\registration.actions.2'),
            'action-3' => trans('bot\registration.actions.3'),
            'action-4' => trans('bot\registration.actions.4'),
            'action-5' => trans('bot\registration.actions.5'),
            'action-6' => trans('bot\registration.actions.6'),
            'done' => trans('bot\registration.actions.done')
        ]))->map(function ($item) {
            return ['chat_id' => $this->chat->telegram_id, 'text' => $item];
        });

        switch ($stage) {
            case BotState::STATE_REGISTRATION_START:
                $langKeyboard = Keyboard::make()->setOneTimeKeyboard(true);
                $langKeyboard->row(
                    Keyboard::button(BotService::LANG_UA),
                    Keyboard::button(BotService::LANG_RU),
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
                $locations->map(fn(LocationName $location) => $location->name = $location->getName($this->client->locale));
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
                app(BotContract::class)
                    ->setChat($this->chat)
                    ->mainMenu();
                break;
        }
    }
}
