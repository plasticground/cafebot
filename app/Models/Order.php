<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class Order
 *
 * @property int $id
 * @property int $client_id
 * @property int $message_id
 * @property int $status
 * @property string $comment
 * @property float $price
 * @property-read Collection|Product[] $products
 * @package App\Models
 *
 * @mixin Builder
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
        'message_id',
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
        return $this->belongsToMany(Product::class)->withPivot(['amount']);
    }

    public function addProduct(Product $product)
    {
        $existingProduct = $this->products()->find($product->id);

        if ($existingProduct) {
            return $this->products()->updateExistingPivot($product->id, ['amount' => ++$existingProduct->pivot->amount]);
        }

        $this->products()->attach($product->id);

        return 1;
    }

    public function removeProduct(Product $product)
    {
        $existingProduct = $this->products()->find($product->id);

        if ($existingProduct) {
            if ($existingProduct->pivot->amount > 1) {
                return $this->products()->updateExistingPivot($product->id, ['amount' => --$existingProduct->pivot->amount]);
            }
        }

        return $this->products()->detach($product->id);
    }
}
