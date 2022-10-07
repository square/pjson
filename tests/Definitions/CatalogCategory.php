<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

class CatalogCategory extends CatalogObject
{
    use JsonSerialize;

    #[Json('parent_category_id')]
    protected string $parentCategoryId;
}
