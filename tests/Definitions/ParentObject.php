<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

// phpcs:ignore PSR1.Classes.ClassDeclaration
class ParentObject
{
    use JsonSerialize;

    #[Json("identifier")]
    public $id;
}
