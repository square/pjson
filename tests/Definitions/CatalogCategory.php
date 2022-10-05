<?php declare(strict_types=1);

namespace Squareup\Pjson\Tests\Definitions;

use Squareup\Pjson\Json;
use Squareup\Pjson\JsonSerialize;

class CatalogCategory extends CatalogObject
{
    use JsonSerialize;

    #[Json('parent_category_id')]
    protected string $parentCategoryId;
}
