<?php


namespace App\Contracts;


use App\Models\BotState;
use App\Models\Client;

/**
 * Interface TelegramBotContract
 * @package App\Contracts\Telegram
 */
interface RegistrationContract
{
    /**
     * @param BotState|null $botState
     * @return mixed
     */
    public function setChat(?BotState $botState);

    /**
     * @param Client|null $client
     * @return mixed
     */
    public function setClient(?Client $client);

    /**
     * @param string $text
     * @return int
     */
    public function handle(string $text): int;

    /**
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param $id
     * @return bool
     */
    public function validate(array $data, array $rules, array $messages, $id): bool;

    /**
     * @param int $stage
     * @return mixed
     */
    public function registration(int $stage = BotState::STATE_REGISTRATION_START);
}
