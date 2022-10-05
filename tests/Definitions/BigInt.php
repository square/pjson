<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\JsonDataSerializable;

class BigInt implements JsonDataSerializable
{
    public function __construct(
        protected string $value,
    ) {
    }

    public static function fromJsonData($jd, array|string $path = []) : static
    {
        return new BigInt($jd);
    }

    public function toJsonData()
    {
        return $this->value;
    }
}
