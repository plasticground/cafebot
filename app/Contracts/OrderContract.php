<?php


namespace App\Contracts;


use App\Models\BotState;
use App\Models\Cafe;
use App\Models\Client;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Product;

/**
 * Interface TelegramBotContract
 * @package App\Contracts\Telegram
 */
interface OrderContract
{
    /**
     * @param BotState|null $botState
     * @return mixed
     */
    public function setChat(?BotState $botState);

    /**
     * @param Cafe|null $cafe
     * @return mixed
     */
    public function setCafe(?Cafe $cafe);

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
     * @param int $stage
     * @return mixed
     */
    public function order(int $stage = BotState::STATE_ORDER_NEW);

    /**
     * @param Menu $menu
     * @return mixed
     */
    public function makeMenu(Menu $menu);

    /**
     * @param Product $product
     * @return mixed
     */
    public function makeOrder(Product $product);

    /**
     * @param Product $product
     * @return mixed
     */
    public function editOrder(Product $product);

    /**
     * @param Order $order
     * @return mixed
     */
    public function acceptOrder(Order $order);

    /**
     * @return mixed
     */
    public function removeProduct();

    /**
     * @return mixed
     */
    public function addProduct();

    /**
     * @param Order $order
     * @return mixed
     */
    public function cancel(Order $order);

    /**
     * @return mixed
     */
    public function back();

    /**
     * @param Order $order
     * @return mixed
     */
    public function sendOrder(Order $order);
}
