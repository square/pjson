<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\JsonSerialize;

class Traitor
{
    use TraitorTrait;
    use JsonSerialize;
}
