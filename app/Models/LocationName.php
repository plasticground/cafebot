<?php

namespace App\Models;

use App\Traits\Localeable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Location
 *
 * @property int $id
 * @property string $ua_name
 * @property string $ru_name
 * @property string $name
 * @package App\Models
 */
class LocationName extends Model
{
    use Localeable;

    /** @var string[]  */
    protected $fillable = [
        'ua_name',
        'ru_name',
    ];
}
