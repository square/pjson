<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions\Traitless;

use Square\Pjson\Json;

class BigCat extends Category
{
    #[Json(['data', 'name'], omit_empty: true)]
    protected $data_name;
}
