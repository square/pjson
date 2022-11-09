<?php declare(strict_types=1);

namespace Square\Pjson\Internal;

use ArrayObject;

final class ArrayRecursionWrapper extends ArrayObject
{
    public string $type = '';

    public static function from($d) : static
    {
        if ($d instanceof static) {
            return $d;
        }

        return new static($d);
    }
}
