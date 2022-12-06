<?php declare(strict_types=1);

namespace Square\Pjson;

use ReflectionAttribute;
use Square\Pjson\Internal\RClass;
use Square\Pjson\Json;

class Deserializer
{
    protected array $typeStack = [];

    public function fromJsonString(
        string $json,
        string $type,
        array|string $path = [],
        int $depth = 512,
        int $flags = 0,
    ) {
        $flags |= JSON_THROW_ON_ERROR;
        $jd = json_decode($json, associative: true, flags: $flags, depth: $depth);
        return $this->fromJsonData($jd, $type, $path);
    }

    public function listFromJsonString(
        string $json,
        string $type,
        array|string $path = [],
        int $depth = 512,
        int $flags = 0,
    ) : array {
        $flags |= JSON_THROW_ON_ERROR;
        $jd = json_decode($json, associative: true, flags: $flags, depth: $depth);
        return $this->listfromJsonData($jd, $type, $path);
    }

    public function listfromJsonData(array $json, string $type, array|string $path = []) : array
    {
        if (is_string($path)) {
            $path = [$path];
        }
        foreach ($path as $pathBit) {
            $json = $json[$pathBit];
        }
        return array_map(fn ($d) => $this->fromJsonData($d, $type), $json);
    }

    public function fromJsonData($jd, string $type, array|string $path = [])
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
            if (end($this->typeStack) === $type) {
                $continue = true;
            }
            array_push($this->typeStack, $type);

            try {
                if (!$continue) {
                    return $type::fromJsonData($jd, $path);
                }
            } finally {
                array_pop($this->typeStack);
            }
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
