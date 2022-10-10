<?php declare(strict_types=1);
namespace Square\Pjson;

interface FromJsonData
{
    /**
     * Returns an instance of the class based on the received json data.
     * The data received could be an array, an object (stdClass), a string, a bool, int or float (all standard json
     * types)
     *
     * @param mixed $jd
     * @param array $path if given, the path in the json value where the actual value is expected to be found
     * @return static
     */
    public static function fromJsonData($jd, array|string $path = []) : static;
}
