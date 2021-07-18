<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Location
 *
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property string $sub1
 * @property string $sub2
 * @property-read Client $client
 * @package App\Models
 */
class Location extends Model
{
    public const NAME_RYNOK = 'rynok';

    /** @var string[]  */
    protected $fillable = [
        'client_id',
        'name',
        'sub1',
        'sub2'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
