<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

class DTO
{
    use JsonSerialize;

    #[Json]
    public readonly int $value;

    public function __construct()
    {
        $this->value = 6;
    }
}
