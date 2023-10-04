<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

class StatusList
{
    use JsonSerialize;

    #[Json('status_list', type: Status::class)]
    public array $statusList;
}
