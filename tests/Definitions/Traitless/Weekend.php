<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions\Traitless;

use Square\Pjson\Json;

class Weekend
{
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
