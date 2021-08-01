<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 *
 * @property int $id
 * @property int $category_id
 * @property int $sorting_position
 * @property string $ru_name
 * @property string $ua_name
 * @property float $price
 * @package App\Models
 */
class Product extends Model
{
    /** @var string[]  */
    protected $fillable = [
        'category_id',
        'ru_name',
        'ua_name',
        'price',
        'sorting_position',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function category()
    {
        return $this->hasOne(ProductCategory::class);
    }
}
