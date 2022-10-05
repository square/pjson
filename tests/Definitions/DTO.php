<?php declare(strict_types=1);

namespace Squareup\Pjson\Tests\Definitions;

use Squareup\Pjson\Json;
use Squareup\Pjson\JsonSerialize;

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
