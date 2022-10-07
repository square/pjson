<?php declare(strict_types=1);
namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

abstract class CatalogObject
{
    use JsonSerialize;

    #[Json]
    protected $id;

    #[Json]
    protected string $type;

    public static function fromJsonArray(array $jd): static
    {
        $t = $jd['type'];

        return match ($t) {
            'category' => CatalogCategory::fromJsonArray($jd),
            'item' => CatalogItem::fromJsonArray($jd),
        };
    }
}
