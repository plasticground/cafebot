<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Location
 *
 * @property int $id
 * @property string $ua_name
 * @property string $ru_name
 * @package App\Models
 */
class LocationName extends Model
{

    /** @var string[]  */
    protected $fillable = [
        'ua_name',
        'ru_name',
    ];
}
