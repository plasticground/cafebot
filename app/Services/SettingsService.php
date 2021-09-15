<?php


namespace App\Services;


use App\Contracts\SettingsContract;
use App\Models\BotState;
use App\Models\Cafe;
use App\Models\Client;
use App\Models\Location;
use App\Models\LocationName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

/**
 * Class TelegramBotService
 * @package App\Services\Telegram
 */
class SettingsService implements SettingsContract
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
            case BotState::STATE_SETTINGS:
                switch ($text) {
                    case trans('bot\settings.select.name'):
                        $this->settings(BotState::STATE_SETTINGS_NAME);
                        break;
                    case trans('bot\settings.select.phone'):
                        $this->settings(BotState::STATE_SETTINGS_PHONE);
                        break;
                    case trans('bot\settings.select.location'):
                        $this->settings(BotState::STATE_SETTINGS_LOCATION);
                        break;
                    case trans('bot\settings.select.lang'):
                        $this->settings(BotState::STATE_SETTINGS_LANGUAGE);
                        break;
                    case trans('bot\settings.select.back'):
                        $state = BotState::STATE_MAIN_MENU;
                        break;
                }
                break;
            case BotState::STATE_SETTINGS_LANGUAGE:
                switch ($text) {
                    case BotService::LANG_UA:
                        $this->client->update(['locale' => 'ua']);
                        app('translator')->setLocale('ua');

                        Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\registration.lang')]);
                        $state = BotState::STATE_MAIN_MENU;

                        break;
                    case BotService::LANG_RU:
                        $this->client->update(['locale' => 'ru']);
                        app('translator')->setLocale('ru');

                        Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\registration.lang')]);
                        $state = BotState::STATE_MAIN_MENU;

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
                    $this->client->update(['name' => $text]);
                    Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\registration.name') . $text]);
                    $state = BotState::STATE_MAIN_MENU;
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
                    $this->client->update(['phone' => $text]);
                    Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\registration.phone') . $text]);
                    $state = BotState::STATE_MAIN_MENU;
                }
                break;

            case BotState::STATE_SETTINGS_LOCATION:
                switch ($text) {
                    case trans('bot\settings.select.locations.main'):
                        $this->settings(BotState::STATE_SETTINGS_LOCATION_MAIN);
                        break;
                    case trans('bot\settings.select.locations.sub1'):
                        $this->settings(BotState::STATE_SETTINGS_LOCATION_SUB1);
                        break;
                    case trans('bot\settings.select.locations.sub2'):
                        $this->settings(BotState::STATE_SETTINGS_LOCATION_SUB2);
                        break;
                }
                break;

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

                    Location::whereHas('client', function (Builder $builder) {
                        return $builder->whereTelegramId($this->client->telegram_id);
                    })
                        ->first()
                        ->update(['location_name_id' => $location->id]);
                }

                Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\registration.location.main') . $text]);
                $state = BotState::STATE_MAIN_MENU;
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
                    Location::whereHas('client', function (Builder $builder) {
                        return $builder->whereTelegramId($this->client->telegram_id);
                    })
                        ->first()
                        ->update(['sub1' => $text]);
                }

                Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\registration.location.sub1') . $text]);
                $state = BotState::STATE_MAIN_MENU;
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
                    Location::whereHas('client', function (Builder $builder) {
                        return $builder->whereTelegramId($this->client->telegram_id);
                    })
                        ->first()
                        ->update(['sub2' => $text]);
                    Telegram::sendMessage(['chat_id' => $this->chat->telegram_id, 'text' => trans('bot\registration.location.sub2') . $text]);
                    $state = BotState::STATE_MAIN_MENU;
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
     */
    public function settings(int $stage = BotState::STATE_SETTINGS)
    {
        switch ($stage) {
            case BotState::STATE_SETTINGS:
                $this->chat->update(['state' => BotState::STATE_SETTINGS]);

                $settingsButtons = Keyboard::make()
                    ->row(Keyboard::button(trans('bot\settings.select.name')), Keyboard::button(trans('bot\settings.select.phone')))
                    ->row(Keyboard::button(trans('bot\settings.select.location')), Keyboard::button(trans('bot\settings.select.lang')))
                    ->row(Keyboard::button(trans('bot\settings.select.back')));

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => trans('bot\settings.select.choose')
                        . $this->client->name . "\n"
                        . $this->client->phone . "\n"
                        . $this->client->location->locationName->getName($this->client->locale) . ': ' . $this->client->location->sub1 . ' - ' . $this->client->location->sub2 . "\n"
                        . ($this->client->locale === 'ua' ? BotService::LANG_UA : BotService::LANG_RU),
                    'reply_markup' => $settingsButtons,
                    'parse_mode' => 'markdown',
                ]);

                break;
            case BotState::STATE_SETTINGS_NAME:
                $this->chat->update(['state' => BotState::STATE_SETTINGS_NAME]);

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => trans('bot\settings.enter.name'),
                    'reply_markup' => Keyboard::remove()
                ]);

                break;
            case BotState::STATE_SETTINGS_PHONE:
                $this->chat->update(['state' => BotState::STATE_SETTINGS_PHONE]);

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => trans('bot\settings.enter.phone'),
                    'reply_markup' => Keyboard::remove()
                ]);

                break;
            case BotState::STATE_SETTINGS_LOCATION:
                $this->chat->update(['state' => BotState::STATE_SETTINGS_LOCATION]);

                $settingsButtons = Keyboard::make()
                    ->row(Keyboard::button(trans('bot\settings.select.locations.main')))
                    ->row(Keyboard::button(trans('bot\settings.select.locations.sub1')))
                    ->row(Keyboard::button(trans('bot\settings.select.locations.sub2')));

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => trans('bot\settings.select.choose')
                        . $this->client->location->locationName->getName($this->client->locale) . ': '
                        . $this->client->location->sub1 . ' - ' . $this->client->location->sub2 . "\n",
                    'reply_markup' => $settingsButtons,
                    'parse_mode' => 'markdown',
                ]);

                break;
            case BotState::STATE_SETTINGS_LOCATION_MAIN:
                $this->chat->update(['state' => BotState::STATE_SETTINGS_LOCATION_MAIN]);

                $locations = LocationName::all(['id', 'ru_name', 'ua_name']);
                $locations->map(fn(LocationName $location) => $location->name = $location->getName($this->client->locale));
                $locationKeyboard = Keyboard::make()->setOneTimeKeyboard(true);

                foreach ($locations as $location) {
                    $locationKeyboard->row(Keyboard::button($location->name));
                }

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => trans('bot\settings.enter.locations.main'),
                    'reply_markup' => $locationKeyboard
                ]);

                break;
            case BotState::STATE_SETTINGS_LOCATION_SUB1:
                $this->chat->update(['state' => BotState::STATE_SETTINGS_LOCATION_SUB1]);

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => trans('bot\settings.enter.locations.sub1'),
                    'reply_markup' => Keyboard::remove()
                ]);

                break;
            case BotState::STATE_SETTINGS_LOCATION_SUB2:
                $this->chat->update(['state' => BotState::STATE_SETTINGS_LOCATION_SUB2]);

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => trans('bot\settings.enter.locations.sub2'),
                    'reply_markup' => Keyboard::remove()
                ]);

                break;
            case BotState::STATE_SETTINGS_LANGUAGE:
                $this->chat->update(['state' => BotState::STATE_SETTINGS_LANGUAGE]);

                $langKeyboard = Keyboard::make()->setOneTimeKeyboard(true);
                $langKeyboard->row(
                    Keyboard::button(BotService::LANG_UA),
                    Keyboard::button(BotService::LANG_RU),
                );

                Telegram::sendMessage([
                    'chat_id' => $this->chat->telegram_id,
                    'text' => trans('bot\settings.enter.lang'),
                    'reply_markup' => $langKeyboard
                ]);

                break;
        }
    }
}
