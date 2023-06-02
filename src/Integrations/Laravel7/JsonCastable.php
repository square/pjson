<?php declare(strict_types=1);

namespace Square\Pjson\Integrations\Laravel7;

trait JsonCastable
{
    public static function castUsing()
    {
        return new JsonCaster(static::class);
    }
}
