<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Client
 *
 * @property int $id
 * @property string $name
 * @property string $telegram
 * @property string $phone
 * @property string $locale
 * @property-read Location $location
 * @property-read Order[] $orders
 * @package App\Models
 */
class Client extends Model
{
    /** @var string[]  */
    protected $fillable = [
        'name',
        'telegram',
        'phone',
        'locale'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function location()
    {
        return $this->hasOne(Location::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
