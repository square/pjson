<?php declare(strict_types=1);

namespace Squareup\Pjson\Tests\Definitions;

use Squareup\Pjson\Json;

class BigCat extends Category
{
    #[Json(['data', 'name'], omit_empty: true)]
    protected $data_name;
}
