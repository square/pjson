<?php declare(strict_types=1);

namespace Squareup\Pjson\Tests\Definitions;

use Squareup\Pjson\Json;
use Squareup\Pjson\JsonSerialize;

// phpcs:ignore PSR1.Classes.ClassDeclaration
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
