<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Class Order
 *
 * @property int $id
 * @property int $cafe_id
 * @property int $client_id
 * @property int $message_id
 * @property int $status
 * @property string $comment
 * @property float $price
 * @property-read Cafe $cafe
 * @property-read Collection|Product[] $products
 * @property-read Collection|Product[] $product_list
 * @package App\Models
 *
 * @mixin Builder
 */
class Order extends Model
{
    public const
        STATUS_NEW = 0,
        STATUS_CREATING = 1,
        STATUS_CREATED = 2,
        STATUS_COOKING = 3,
        STATUS_DELIVERING = 4,
        STATUS_DONE = 5,
        STATUS_REJECTED = 6;

    /** @var string[]  */
    protected $fillable = [
        'cafe_id',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cafe()
    {
        return $this->belongsTo(Cafe::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot(['amount']);
    }

    /**
     * @return Collection
     */
    public function getProductListAttribute()
    {
        return $this->products->map(fn(Product $product) => $product->getDisplayNamePriceWithAmount('ru'));
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

    /**
     * @return string[]
     */
    public static function getVerbalStatues(): array
    {
        return [
            self::STATUS_NEW => 'Новый',
            self::STATUS_CREATING => 'Создаётся',
            self::STATUS_CREATED => 'Создан',
            self::STATUS_COOKING => 'В готовке',
            self::STATUS_DELIVERING => 'В доставке',
            self::STATUS_DONE => 'Завершён',
            self::STATUS_REJECTED => 'Отклонён'
        ];
    }

    /**
     * @param int $status
     * @return string
     */
    public static function getVerbalStatus(int $status): string
    {
        return Arr::get(self::getVerbalStatues(), $status, 'Unknown');
    }
}
