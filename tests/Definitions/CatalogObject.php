<?php declare(strict_types=1);
namespace Squareup\Pjson\Tests\Definitions;

use Squareup\Pjson\Json;
use Squareup\Pjson\JsonSerialize;

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
