<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Client
 *
 * @property int $telegram_id
 * @property int $state
 * @property-read
 * @package App\Models
 */
class BotState extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'telegram_id';

    public const STATE_NEW = 0;

    public const STATE_REGISTRATION_START = 100;
    public const STATE_REGISTRATION_LANGUAGE = 101;
    public const STATE_REGISTRATION_NAME = 102;
    public const STATE_REGISTRATION_PHONE = 103;
    public const STATE_REGISTRATION_LOCATION_SUB_1 = 104;
    public const STATE_REGISTRATION_LOCATION_SUB_2 = 105;
    public const STATE_REGISTRATION_DONE = 106;

    /** @var string[]  */
    protected $fillable = [
        'telegram_id',
        'state'
    ];
}