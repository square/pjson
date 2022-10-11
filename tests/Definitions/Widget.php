<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

class Widget
{
    use JsonSerialize;

    #[Json]
    public Status $status;

    #[Json('optional_status')]
    public ?Status $optional;

    #[Json]
    public Size $size;
}
