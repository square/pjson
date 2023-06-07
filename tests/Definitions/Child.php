<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

// phpcs:ignore PSR1.Classes.ClassDeclaration
class Child
{
    use JsonSerialize;

    #[Json("identifier")]
    public $id;

    #[Json(["parent"], omit_empty: true)]
    public ?ParentObject $parent = null;

    #[Json(["parent", "id"])]
    public ?string $parentId = null;
}
