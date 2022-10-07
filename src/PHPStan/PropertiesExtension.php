<?php

namespace Squareup\Pjson\PHPStan;

use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;
use ReflectionAttribute;
use Squareup\Pjson\Json;

class PropertiesExtension implements ReadWritePropertiesExtension
{
    public function isAlwaysRead(PropertyReflection $property, string $propertyName): bool
    {
        return $this->propertyUsesJsonAttribute($property, $propertyName);
    }

    public function isAlwaysWritten(PropertyReflection $property, string $propertyName): bool
    {
        return $this->propertyUsesJsonAttribute($property, $propertyName);
    }

    public function isInitialized(PropertyReflection $property, string $propertyName): bool
    {
        // It might be worth adding some logic here to make address other PHPStan issues here.
        return false;
    }

    private function propertyUsesJsonAttribute(PropertyReflection $property, string $propertyName): bool
    {
        $rp = $property->getDeclaringClass()->getNativeReflection()->getProperty($propertyName);

        return in_array(
            Json::class,
            array_map(fn (ReflectionAttribute $a) => $a->getName(), $rp->getAttributes())
        );
    }
}
