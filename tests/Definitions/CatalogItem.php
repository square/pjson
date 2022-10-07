<?php declare(strict_types=1);
namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

class CatalogItem extends CatalogObject
{
    use JsonSerialize;

    #[Json]
    protected string $name;
}
