<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Order
 *
 * @property int $id
 * @property int $client_id
 * @property int $status
 * @property string $comment
 * @property float $price
 * @package App\Models
 */
class Order extends Model
{
    public const STATUS_NEW = 0;
    public const STATUS_CREATING = 1;
    public const STATUS_CREATED = 2;
    public const STATUS_COOKING = 3;
    public const STATUS_DELIVERING = 4;
    public const STATUS_DONE = 5;
    public const STATUS_REJECTED = 6;

    /** @var string[]  */
    protected $fillable = [
        'client_id',
        'comment',
        'price',
        'status',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
