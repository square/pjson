<?php declare(strict_types=1);

namespace Squareup\Pjson\Tests\Definitions;

use Squareup\Pjson\Json;

trait TraitorTrait
{
    #[Json]
    protected string $secretly_working_for = 'MI6';
}
