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
        // Using laravel's castable interface
        'castable_address' => CastableAddress::class,
        // Using cast attributes
        'address' => JsonCaster::class.':'.Address::class,
    ];
}
