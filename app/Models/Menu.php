<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 *
 * @property int $id
 * @property string $name
 * @property Cafe $cafe
 * @package App\Models
 */
class Menu extends Model
{
    /** @var string[]  */
    protected $fillable = [
        'name'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function categories()
    {
        return $this->hasMany(ProductCategory::class)->orderBy('sorting_position');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cafe()
    {
        return $this->hasOne(Cafe::class, 'menu_id', 'id');
    }
}
