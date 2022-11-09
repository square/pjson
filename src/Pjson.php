<?php declare(strict_types=1);

namespace Square\Pjson;

use ReflectionAttribute;
use Square\Pjson\Internal\ArrayRecursionWrapper;
use Square\Pjson\Internal\RClass;

class Pjson
{
    public static function fromJsonString(
        string $json,
        string $type,
        array|string $path = [],
        int $depth = 512,
        int $flags = 0,
    ) {
        $flags |= JSON_THROW_ON_ERROR;
        $jd = json_decode($json, associative: true, flags: $flags, depth: $depth);
        return static::fromJsonData($jd, $type, $path);
    }

    public static function listFromJsonString(
        string $json,
        string $type,
        array|string $path = [],
        int $depth = 512,
        int $flags = 0,
    ) : array {
        $flags |= JSON_THROW_ON_ERROR;
        $jd = json_decode($json, associative: true, flags: $flags, depth: $depth);
        return static::listfromJsonData($jd, $type, $path);
    }

    public static function listfromJsonData(array $json, string $type, array|string $path = []) : array
    {
        if (is_string($path)) {
            $path = [$path];
        }
        foreach ($path as $pathBit) {
            $json = $json[$pathBit];
        }
        return array_map(fn ($d) => static::fromJsonData($d, $type), $json);
    }

    public static function fromJsonData($jd, string $type, array|string $path = [])
    {
        if (is_string($path)) {
            $path = [$path];
        }
        foreach ($path as $pathBit) {
            $jd = $jd[$pathBit];
        }

        $r = RClass::make($type);
        if ($r->readsFromJson()) {
            $continue = false;
            if ($jd instanceof ArrayRecursionWrapper && $jd->type === $type) {
                $continue = true;
            }
            if ($jd instanceof ArrayRecursionWrapper) {
                $jd->type = $type;
            }

            if (!$continue) {
                return $type::fromJsonData(ArrayRecursionWrapper::from($jd), $path);
            }
        }
        if ($jd instanceof ArrayRecursionWrapper) {
            $jd = $jd->getArrayCopy();
        }
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
