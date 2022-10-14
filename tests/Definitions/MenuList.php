<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

class MenuList
{
    use JsonSerialize;

    #[Json(['menus', 0, 'name'])]
    public string $mainMenuName;
}
