<?php

namespace Square\Pjson\Tests\Definitions;

use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;
use Square\Pjson\JsonStrictDeserialize;

#[JsonStrictDeserialize]
class Store
{
    use JsonSerialize;

    #[Json("identifier", required: true)]
    protected $id;
    
    #[Json("store_name")]
    protected string $name;

    #[Json("store_nickname")]
    protected ?string $nickname;
    
    #[Json("catalog", type: CatalogObject::class, omit_empty: true)]
    protected ?array $catalog;

    #[Json('store_hours', type: Schedule::class)]
    protected array $storeHours;

    #[Json('next_open_hours')]
    protected Schedule $nextOpenHours;

    #[Json(['owner', 'email'])]
    protected string $ownerEmail;

    #[Json(['owner', 'phone'])]
    protected ?string $ownerPhone;
}