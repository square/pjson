<?php declare(strict_types=1);

namespace Square\Pjson\Exceptions;

use Exception;

class MissingRequiredPropertyException extends Exception
{
    public function __construct(array $path, string $json)
    {
        parent::__construct(sprintf("missing property %s in %s", json_encode($path), $json));
    }
}
