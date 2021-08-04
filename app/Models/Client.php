<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Client
 *
 * @property int $id
 * @property int $telegram_id
 * @property string $name
 * @property string $phone
 * @property string $locale
 * @property string $telegram_username
 * @property-read Location $location
 * @property-read BotState $botState
 * @property-read Order[] $orders
 * @package App\Models
 */
class Client extends Model
{
    /** @var string[]  */
    protected $fillable = [
        'name',
        'phone',
        'locale',
        'telegram_id',
        'telegram_username'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function location()
    {
        return $this->hasOne(Location::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function botState()
    {
        return $this->hasOne(BotState::class, 'telegram_id', 'telegram_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
