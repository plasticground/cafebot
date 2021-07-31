<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 *
 * @property int $id
 * @property int $category_id
 * @property int $sorting_position
 * @property string $name
 * @property float $price
 * @package App\Models
 */
class Product extends Model
{
    /** @var string[]  */
    protected $fillable = [
        'category_id',
        'name',
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
