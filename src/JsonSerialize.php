<?php declare(strict_types=1);

namespace Square\Pjson;

use ReflectionAttribute;
use Square\Pjson\Internal\RClass;
use stdClass;
use const JSON_THROW_ON_ERROR;

trait JsonSerialize
{
    public function toJson(int $flags = 0, int $depth = 512) : string
    {
        $flags |= JSON_THROW_ON_ERROR;
        return json_encode($this->toJsonData(), flags: $flags, depth: $depth);
    }

    public function toJsonData()
    {
        $r = RClass::make($this);
        $props = $r->getProperties();
        $d = [];
        foreach ($props as $prop) {
            $attrs = $prop->getAttributes(Json::class, ReflectionAttribute::IS_INSTANCEOF);
            if (empty($attrs)) {
                continue;
            }
            $a = $attrs[0];
            if ($prop->isInitialized($this)) {
                $a->newInstance()->forProperty($prop)->appendValue($d, $prop->getValue($this));
            }
        }

        return $d;
    }

    public static function toJsonListData(array $data) : array
    {
        return array_map(fn ($d) => $d->toJsonData(), $data);
    }

    public static function toJsonList(array $data, int $flags = 0, int $depth = 512) : string
    {
        $flags |= JSON_THROW_ON_ERROR;
        return json_encode(static::toJsonListData($data), flags: $flags, depth: $depth);
    }

    public static function fromJsonString(
        string $json,
        array|string $path = [],
        int $depth = 512,
        int $flags = 0,
    ) : static {
        $flags |= JSON_THROW_ON_ERROR;
        $jd = json_decode($json, associative: true, flags: $flags, depth: $depth);
        return self::fromJsonData($jd, $path);
    }

    public static function listFromJsonString(
        string $json,
        array|string $path = [],
        int $depth = 512,
        int $flags = 0,
    ) : array {
        $flags |= JSON_THROW_ON_ERROR;
        $jd = json_decode($json, associative: true, flags: $flags, depth: $depth);
        return static::listfromJsonData($jd, $path);
    }

    public static function listfromJsonData(array $json, array|string $path = []) : array
    {
        if (is_string($path)) {
            $path = [$path];
        }
        foreach ($path as $pathBit) {
            $json = $json[$pathBit];
        }
        return array_map(fn ($d) => static::fromJsonData($d), $json);
    }

    public static function fromJsonData($jd, array|string $path = []) : static
    {
        if (is_string($path)) {
            $path = [$path];
        }
        foreach ($path as $pathBit) {
            $jd = $jd[$pathBit];
        }

        $r = RClass::make(static::class);
        $props = $r->getProperties();
        $return = $r->source()->newInstanceWithoutConstructor();
        foreach ($props as $prop) {
            $attrs = $prop->getAttributes(Json::class, ReflectionAttribute::IS_INSTANCEOF);
            if (empty($attrs)) {
                continue;
            }
            $a = $attrs[0];

            $type = $prop->getType();
            $v = $a->newInstance()->forProperty($prop)->retrieveValue($jd, $type);
            if (is_null($v) && $type && !$type->allowsNull()) {
                continue;
            }

            $prop->setValue($return, $v);
        }
        return $return;
    }
}
