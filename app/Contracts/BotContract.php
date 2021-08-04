<?php


namespace App\Contracts;

use Telegram\Bot\Objects\Update;

/**
 * Interface TelegramBotContract
 * @package App\Contracts\Telegram
 */
interface BotContract
{
    /**
     * @param $update
     */
    public function getUpdate(Update $update);
    public function registration(int $stage);
}
