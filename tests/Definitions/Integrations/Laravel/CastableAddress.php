<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions\Integrations\Laravel;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Square\Pjson\Integrations\Laravel\JsonCastable;

class CastableAddress extends Address implements Castable
{
    use JsonCastable;
}
