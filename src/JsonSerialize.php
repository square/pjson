<?php declare(strict_types=1);

namespace Square\Pjson;

use ReflectionAttribute;
use Square\Pjson\Internal\RClass;
use stdClass;

trait JsonSerialize
{
    public function toJson() : string
    {
        return json_encode($this->toJsonData());
    }

    public function toJsonData() : stdClass
    {
        $r = RClass::make($this);
        $props = $r->getProperties();
        $d = new stdClass;
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

    public static function toJsonList(array $data) : string
    {
        return json_encode(static::toJsonListData($data));
    }

    public static function fromJsonString(string $json, array|string $path = []) : static
    {
        $jd = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        return self::fromJsonArray($jd, $path);
    }

    public static function listFromJsonString(string $json, array|string $path = []) : array
    {
        $jd = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        return static::listFromJsonArray($jd, $path);
    }

    public static function listFromJsonArray(array $json, array|string $path = []) : array
    {
        if (is_string($path)) {
            $path = [$path];
        }
        foreach ($path as $pathBit) {
            $json = $json[$pathBit];
        }
        return array_map(fn ($d) => static::fromJsonArray($d), $json);
    }

    public static function fromJsonArray(array $jd, array|string $path = []) : static
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
