<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions\Traitless;

use Square\Pjson\Json;

class Schedule extends AbstractSchedule
{
    #[Json("schedule_start")]
    protected int $start;

    #[Json("schedule_end")]
    protected int $end;

    public function __construct(int $start, int $end)
    {
        $this->start = $start;
        $this->end = $end;
    }
}
