<?php declare(strict_types=1);

namespace Squareup\Pjson\Tests\Definitions;

use Squareup\Pjson\Json;
use Squareup\Pjson\JsonSerialize;

class Weekend
{
    use JsonSerialize;

    #[Json(type: Schedule::class)]
    public array $weekend;

    public function __construct()
    {
        $this->weekend = [
            'sat' => new Schedule(1, 2),
            'sun' => new Schedule(3, 4),
        ];
    }
}
