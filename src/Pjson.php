<?php declare(strict_types=1);

namespace Square\Pjson;

class Pjson
{
    protected static Deserializer $deserializer;

    public static function fromJsonString(
        string $json,
        string $type,
        array|string $path = [],
        int $depth = 512,
        int $flags = 0,
    ) {
        return static::deserializer()->fromJsonString($json, $type, $path, $depth, $flags);
    }

    public static function listFromJsonString(
        string $json,
        string $type,
        array|string $path = [],
        int $depth = 512,
        int $flags = 0,
    ) : array {
        return static::deserializer()->listFromJsonString($json, $type, $path, $depth, $flags);
    }

    public static function listfromJsonData(array $json, string $type, array|string $path = []) : array
    {
        return static::deserializer()->listFromJsonData($json, $type, $path);
    }

    public static function fromJsonData($jd, string $type, array|string $path = [])
    {
        return static::deserializer()->fromJsonData($jd, $type, $path);
    }

    protected static function deserializer() : Deserializer
    {
        if (!isset(static::$deserializer)) {
            static::$deserializer = new Deserializer;
        }

        return static::$deserializer;
    }
}
