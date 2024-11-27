<?php

declare(strict_types=1);

namespace Square\Pjson;

use Attribute;

/**
 * Allows linking a deserialized json item to its parent
 * There is no intention for this library to handle more complex backwards lookign paths than
 * this one.
 * Those more complex paths can easily be handled by methods that traverse the entire
 * data structure once it is in memory once the parent is available.
 * So encoding something like parent->property->parent->parent->property->property is left to be
 * implemented in client code
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class JsonParent extends Json
{
    /**
     * No constructor params are made available in this case as we do not allow customizing how we link
     * to the parent object. There is only one parent object available, that is the one we are linking to
     * and that's it.
     */
    public function __construct()
    {
        $this->path = [];
        $this->omit_empty = false;
    }

    public function linksToParentObject(): bool
    {
        return true;
    }
}
