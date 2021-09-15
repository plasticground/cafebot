<?php

namespace App\Models;

use App\Traits\Localeable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 *
 * @property int $id
 * @property int $menu_id
 * @property string $ru_name
 * @property string $ua_name
 * @property-read Menu $menu
 * @package App\Models
 */
class Cafe extends Model
{
    use Localeable;

    /** @var string[]  */
    protected $fillable = [
        'ru_name',
        'ua_name',
        'menu_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function menu()
    {
        return $this->hasOne(Menu::class, 'id', 'menu_id');
    }
}
