<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductCategory
 *
 * @property int $id
 * @property int $sorting_position
 * @property int $menu_id
 * @property string $ru_name
 * @property string $ua_name
 * @property Menu $menu
 * @property Product[] $products
 * @package App\Models
 */
class ProductCategory extends Model
{
    /** @var string[]  */
    protected $fillable = [
        'ru_name',
        'ua_name',
        'sorting_position',
        'menu_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id')
            ->orderBy('sorting_position');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }
}
