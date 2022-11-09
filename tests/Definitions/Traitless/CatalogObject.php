<?php declare(strict_types=1);
namespace Square\Pjson\Tests\Definitions\Traitless;

use Square\Pjson\FromJsonData;
use Square\Pjson\Json;
use Square\Pjson\Pjson;

abstract class CatalogObject implements FromJsonData
{
    #[Json]
    protected $id;

    #[Json]
    protected string $type;

    public static function fromJsonData($jd, array|string $path = []): static
    {
        $t = $jd['type'];

        return match ($t) {
            'category' => Pjson::fromJsonData($jd, CatalogCategory::class),
            'item' => Pjson::fromJsonData($jd, CatalogItem::class),
        };
    }
}
