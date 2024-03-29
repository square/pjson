<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

class Token
{
    use JsonSerialize;

    #[Json(required: true)]
    public string $key;
}
