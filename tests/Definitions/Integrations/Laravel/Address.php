<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions\Integrations\Laravel;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

class Address
{
    use JsonSerialize;

    #[Json]
    protected string $line1;
    #[Json]
    protected string $line2;
    #[Json]
    protected string $city;
    #[Json]
    protected string $zipcode;
    #[Json]
    protected string $country;
}
