<?php


namespace App\Contracts;

use App\Models\BotState;
use App\Models\Client;
use Telegram\Bot\Objects\Update;

/**
 * Interface TelegramBotContract
 * @package App\Contracts\Telegram
 */
interface BotContract
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
     * @param Update $update
     * @return mixed
     */
    public function getUpdate(Update $update);

    /**
     * @param Update $update
     * @return mixed
     */
    public function handleMessage(Update $update);

    /**
     * @param Update $update
     * @return mixed
     */
    public function handleCallbackQuery(Update $update);

    /**
     * @return mixed
     */
    public function orderService();

    /**
     * @return mixed
     */
    public function settingsService();

    /**
     * @return mixed
     */
    public function registrationService();

    /**
     * @return mixed
     */
    public function mainMenu();

    /**
     * @param int $page
     * @return mixed
     */
    public function history(int $page = 1);
}
