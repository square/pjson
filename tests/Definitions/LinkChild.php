<?php

declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonParent;
use Square\Pjson\JsonSerialize;

class LinkChild
{
    use JsonSerialize;

    #[Json]
    public string $name;

    #[JsonParent]
    public LinkParent $parent;
}
