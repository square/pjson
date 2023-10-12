<?php
declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

class MergeOne
{
    use JsonSerialize;

    public function __construct(
        #[Json(path: ['sub', 'one'])] public string $one,
    ) {
    }
}
