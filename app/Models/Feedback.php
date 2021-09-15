<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Client
 *
 * @property string $ru_text
 * @property string $ua_text
 * @property-read
 * @package App\Models
 */
class Feedback extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    public $primaryKey = null;

    /** @var string[]  */
    protected $fillable = [
        'ua_text',
        'ru_text'
    ];
}
