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
    public const STATE_REGISTRATION_LOCATION_MAIN = 104;
    public const STATE_REGISTRATION_LOCATION_SUB_1 = 105;
    public const STATE_REGISTRATION_LOCATION_SUB_2 = 106;

    public const STATE_MAIN_MENU = 200;

    public const STATE_ORDER_NEW = 300;
    public const STATE_ORDER_CHOOSE_CAFE = 301;
    public const STATE_ORDER_STARTED = 302;
    public const STATE_ORDER_EDITING = 303;
    public const STATE_ORDER_ACCEPTING = 304;
    public const STATE_ORDER_FINISHED = 305;

    public const STATE_HISTORY = 400;

    public const STATE_FEEDBACK = 500;

    public const STATE_SETTINGS = 600;
    public const STATE_SETTINGS_NAME = 601;
    public const STATE_SETTINGS_PHONE = 602;
    public const STATE_SETTINGS_LOCATION = 603;
    public const STATE_SETTINGS_LOCATION_MAIN = 604;
    public const STATE_SETTINGS_LOCATION_SUB1 = 605;
    public const STATE_SETTINGS_LOCATION_SUB2 = 606;
    public const STATE_SETTINGS_LANGUAGE = 607;

    /** @var string[]  */
    protected $fillable = [
        'telegram_id',
        'state'
    ];
}
