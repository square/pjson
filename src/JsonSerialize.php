<?php declare(strict_types=1);

namespace Squareup\Pjson;

use ReflectionAttribute;
use Squareup\Pjson\Internal\RClass;
use stdClass;

trait JsonSerialize
{
    public function toJson() : string
    {
        return json_encode($this->toJsonData());
    }

    public function toJsonData()
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

    public static function fromJsonString(string $json) : static
    {
        $jd = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        return self::fromJsonArray($jd);
    }

    public static function fromJsonArray(array $jd) : static
    {
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
