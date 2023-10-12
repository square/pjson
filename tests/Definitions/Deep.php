<?php
declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

class Deep
{
    use JsonSerialize;

    public function __construct(
        #[Json] public float $depth,
        #[Json(path: [])] public Container $container,
    ) {
    }
}
