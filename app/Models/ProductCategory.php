<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductCategory
 *
 * @property int $id
 * @property string $name
 * @package App\Models
 */
class ProductCategory extends Model
{
    /** @var string[]  */
    protected $fillable = [
        'name'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
