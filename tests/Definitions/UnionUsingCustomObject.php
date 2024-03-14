<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

class UnionUsingCustomObject
{
    use JsonSerialize;
    #[Json]
    public string $key;
    #[Json]
    public int|string|null|UnionUsingCustomObject $value;
}
