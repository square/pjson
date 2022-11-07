<?php declare(strict_types=1);

namespace Square\Pjson\Integrations\Laravel;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class JsonCaster implements CastsAttributes
{
    public function __construct(
        protected string $target
    ) {
    }

    public function get($model, $key, $value, $attributes)
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return $this->target::fromJsonString($value);
    }

    public function set($model, $key, $value, $attributes)
    {
        return $value?->toJson();
    }
}
