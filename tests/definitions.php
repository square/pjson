<?php declare(strict_types=1);

use Squareup\Pjson\Json;
use Squareup\Pjson\JsonSerialize;

class Category
{
    use JsonSerialize;

    #[Json("identifier")]
    protected $id;

    #[Json("category_name")]
    protected $name;

    #[Json(["data", "name"])]
    protected $data_name;

    #[Json('next_schedule')]
    protected Schedule $schedule;

    #[Json('upcomming_schedules', type: Schedule::class)]
    protected array $schedules;

    #[Json('counts', omit_empty: true)]
    public array $counts = [];

    #[Json]
    public string $unnamed;

    public function __construct()
    {
        $this->id = 'myid';
        $this->name = 'Clothes';
    }

    public function setSchedule(Schedule $s)
    {
        $this->schedule = $s;
    }

    public function setUpcoming(array $up)
    {
        $this->schedules = $up;
    }
}

class BigCat extends Category
{
    #[Json(['data', 'name'], omit_empty: true)]
    protected $data_name;
}

class AbstractSchedule
{
    use JsonSerialize;
}

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
