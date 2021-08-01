<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductCategory
 *
 * @property int $id
 * @property int $sorting_position
 * @property string $ru_name
 * @property string $ua_name
 * @package App\Models
 */
class ProductCategory extends Model
{
    /** @var string[]  */
    protected $fillable = [
        'ru_name',
        'ua_name',
        'sorting_position',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id')
            ->orderBy('sorting_position');
    }
}
