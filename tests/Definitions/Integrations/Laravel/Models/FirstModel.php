<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions\Integrations\Laravel\Models;

use Illuminate\Database\Eloquent\Model;
use Square\Pjson\Integrations\Laravel\JsonCaster;
use Square\Pjson\Tests\Definitions\Integrations\Laravel\Address;
use Square\Pjson\Tests\Definitions\Integrations\Laravel\CastableAddress;

class FirstModel extends Model
{
    protected $table = 'first_model';
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'castable_address' => CastableAddress::class,
        'address' => JsonCaster::class.':'.Address::class,
    ];
}
