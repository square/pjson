<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\JsonDataSerializable;
use Square\Pjson\JsonSerialize;

class AbstractSchedule implements JsonDataSerializable
{
    use JsonSerialize;
}
