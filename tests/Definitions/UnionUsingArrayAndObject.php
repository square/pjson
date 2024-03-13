<?php

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

class UnionUsingArrayAndObject
{
    use JsonSerialize;

    #[Json]
    public string $key;

    #[Json]
    public int|string|null|array|UnionUsingArray $value;
}
