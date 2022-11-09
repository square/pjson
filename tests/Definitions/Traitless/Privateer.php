<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions\Traitless;

use Square\Pjson\Json;

class Privateer
{
    #[Json]
    private $name = "Jenna";
}
