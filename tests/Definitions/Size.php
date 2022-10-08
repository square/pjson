<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\JsonSerialize;
use stdClass;

enum Size
{
    use JsonSerialize;

    case BIG;
    case SMALL;

    public static function fromJsonData(string $d, array|string $path = []): static
    {
        return match ($d) {
            'BIG' => self::BIG,
            'SMALL' => self::SMALL,
            'big' => self::BIG,
            'small' => self::SMALL,
        };
    }

    public function toJsonData(): string
    {
        return strtolower($this->name);
    }
}
