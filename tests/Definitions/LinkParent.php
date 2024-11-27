<?php

declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

class LinkParent
{
    use JsonSerialize;

    #[Json]
    public LinkChild $child;

    #[Json(type: LinkChild::class)]
    public array $children;

    #[Json]
    public string $name;
}
