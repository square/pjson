<?php declare(strict_types=1);

namespace Square\Pjson\Integrations\Laravel;

trait JsonCastable
{
    public static function castUsing(array $arguments)
    {
        return new JsonCaster(static::class);
    }
}
