<?php

namespace App\Models;

use App\Traits\Localeable;
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
 * @property ProductCategory $category
 * @package App\Models
 */
class Product extends Model
{
    use Localeable;

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

    /**
     * @param string $locale
     * @return string
     */
    public function getDisplayNamePrice(string $locale = 'ua'): string
    {
        return $this->getName($locale) . ' - ' . $this->price . ' ₴';
    }
}
