<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions\Traitless;

use Square\Pjson\Json;
use Square\Pjson\Pjson;

class CatalogCategory extends CatalogObject
{
    #[Json('parent_category_id')]
    protected string $parentCategoryId;
}
