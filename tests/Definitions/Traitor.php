<?php declare(strict_types=1);

namespace Squareup\Pjson\Tests\Definitions;

use Squareup\Pjson\JsonSerialize;

class Traitor
{
    use TraitorTrait;
    use JsonSerialize;
}
