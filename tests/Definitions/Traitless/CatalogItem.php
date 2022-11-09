<?php declare(strict_types=1);
namespace Square\Pjson\Tests\Definitions\Traitless;

use Square\Pjson\Json;
use Square\Pjson\Pjson;

class CatalogItem extends CatalogObject
{
    #[Json]
    protected string $name;
}
