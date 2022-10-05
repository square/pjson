<?php declare(strict_types=1);

namespace Squareup\Pjson\Tests\Definitions;

use Squareup\Pjson\Json;
use Squareup\Pjson\JsonSerialize;

class Privateer
{
    use JsonSerialize;

    #[Json]
    private $name = "Jenna";
}
