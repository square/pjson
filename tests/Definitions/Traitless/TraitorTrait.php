<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions\Traitless;

use Square\Pjson\Json;

trait TraitorTrait
{
    #[Json]
    protected string $secretly_working_for = 'MI6';
}
