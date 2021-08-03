<?php

namespace App\Models;

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

    /** @var string[]  */
    protected $fillable = [
        'ua_name',
        'ru_name',
    ];

    /**
     * @param string $lang
     * @return string
     */
    public function getName(string $lang = 'ru'): string
    {
        return $lang === 'ua' ? $this->ua_name : $this->ru_name;
    }
}
