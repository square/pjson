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

    #[Json('untyped_schedule', type: Schedule::class, omit_empty: true)]
    public $untypedSchedule;

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

class Privateer
{
    use JsonSerialize;

    #[Json]
    private $name = "Jenna";
}

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
