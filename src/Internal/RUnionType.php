<?php

declare(strict_types=1);

namespace Square\Pjson\Internal;

use ReflectionNamedType;
use ReflectionUnionType;
use Square\Pjson\Exceptions\UndecidableTypeException;

class RUnionType
{
    public static function getSingleTypeMatch(ReflectionUnionType $type, mixed $data): ReflectionNamedType
    {
        $datatype = get_debug_type($data);
        if ($datatype === 'array') {
            $matchedType = null;
            foreach ($type->getTypes() as $unionType) {
                if (class_exists($unionType->getName()) || ($unionType->getName() === 'array')) {
                    if ($matchedType === null) {
                        $matchedType = $unionType;
                    } else {
                        throw new UndecidableTypeException($type, $data);
                    }
                }
            }

            if ($matchedType !== null) {
                return $matchedType;
            }

            throw new UndecidableTypeException($type, $data);
        }

        foreach ($type->getTypes() as $unionType) {
            if ($unionType->getName() === $datatype) {
                return $unionType;
            }
        }

        throw new UndecidableTypeException($type, $data);
    }
}
