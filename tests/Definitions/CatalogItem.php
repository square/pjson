<?php declare(strict_types=1);
namespace Squareup\Pjson\Tests\Definitions;

use Squareup\Pjson\Json;
use Squareup\Pjson\JsonSerialize;

class CatalogItem extends CatalogObject
{
    use JsonSerialize;

    #[Json]
    protected string $name;
}
