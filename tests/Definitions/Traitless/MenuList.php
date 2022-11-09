<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions\Traitless;

use Square\Pjson\Json;

class MenuList
{
    #[Json(['menus', 0, 'name'])]
    public string $mainMenuName;
}
