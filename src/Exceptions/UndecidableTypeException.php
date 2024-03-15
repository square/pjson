<?php

declare(strict_types=1);

namespace Square\Pjson\Exceptions;

use Exception;
use ReflectionUnionType;

class UndecidableTypeException extends Exception
{
    public function __construct(ReflectionUnionType $type, mixed $json)
    {
        $choices = [];
        foreach ($type->getTypes() as $unionType) {
            $choices[] = $unionType->getName();
        }
        parent::__construct(
            sprintf(
                'could not pick a type for %s among %s consider implementing a custom fromJsonData method for this',
                json_encode($json),
                json_encode($choices)
            )
        );
    }
}
