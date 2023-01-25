<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

class Collector
{
    use JsonSerialize;

    #[Json(type: Schedule::class)]
    public Collection $schedules;

    #[Json(type: Schedule::class, collection_factory_method: 'make')]
    public Collection $static_factoried_schedules;

    #[Json(type: Schedule::class, collection_factory_method: 'makeme')]
    public Collection $factoried_schedules;
}
