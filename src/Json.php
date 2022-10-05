<?php declare(strict_types=1);
namespace Squareup\Pjson;

use Attribute;
use ReflectionNamedType;
use ReflectionProperty;
use stdClass;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Json
{
    protected array $path;

    protected string $type;

    protected bool $omit_empty;

    public function __construct(string|array $path = '', string $type = '', bool $omit_empty = false)
    {
        if ($path !== '') {
            if (is_string($path)) {
                $path = [$path];
            }
            $this->path = $path;
        }

        if ($type !== '') {
            $this->type = $type;
        }

        $this->omit_empty = $omit_empty;
    }

    /**
     * Receive the property this attribute was set on. If the attribute was created without a name, we grab
     * the property's name instead.
     */
    public function forProperty(ReflectionProperty $prop) : Json
    {
        if (isset($this->path)) {
            return $this;
        }

        $this->path = [$prop->getName()];
        return $this;
    }

    /**
     * Builds the PHP value from the json array data and a type if available
     */
    public function retrieveValue(array $data, ?ReflectionNamedType $type = null)
    {
        foreach ($this->path as $pathBit) {
            if (!array_key_exists($pathBit, $data)) {
                return ;
            }
            $data = $data[$pathBit];
        }

        if ($type === null) {
            if (isset($this->type)) {
                $t = $this->type;
                return $t::fromJsonArray($data);
            }
            return $data;
        }

        if (!class_exists($type->getName()) && ($type->getName() !== 'array' || !isset($this->type))) {
            return $data;
        }

        if (!class_exists($type->getName()) && $type->getName() === 'array' && isset($this->type)) {
            $t = $this->type;
            return array_map(fn ($d) => $t::fromJsonArray($d), $data);
        }

        if (!$this->hasTrait($type->getName())) {
            return $data;
        }

        $n = $type->getName();
        return $n::fromJsonArray($data);
    }

    /**
     * Whether or not a value is empty
     */
    protected function isEmpty($value) : bool
    {
        if ($value === null) {
            return true;
        }

        if ($value === '') {
            return true;
        }

        if (is_array($value) && empty($value)) {
            return true;
        }

        return false;
    }

    /**
     * Appends the given value to an array of data on the path to serializing to a JSON string
     */
    public function appendValue(object $data, $value)
    {
        if ($this->omit_empty && $this->isEmpty($value)) {
            return;
        }
        $max = count($this->path)-1;
        $d = $data;
        foreach ($this->path as $i => $pathBit) {
            if (property_exists($d, $pathBit) && $i === $max) {
                throw new \Exception('no bueno');
            }

            if (!property_exists($d, $pathBit) && $i < $max) {
                $d->$pathBit = new stdClass;
            }

            if ($i < $max) {
                $d = $data->$pathBit;
            }

            if ($i === $max) {
                if (is_array($value)) {
                    $d->$pathBit = [];
                    foreach ($value as $k => $val) {
                        $d->$pathBit[$k] = $this->jsonValue($val);
                    }
                } else {
                    $d->$pathBit = $this->jsonValue($value);
                }
            }
        }
    }

    /**
     * Check that a target type has the JsonSerialize trait
     */
    protected function hasTrait($valueOrType) : bool
    {
        $classes = [$valueOrType, ...array_values(class_parents($valueOrType))];
        $traits = [];
        foreach ($classes as $class) {
            $traits = array_merge($traits, class_uses($class));
        }

        return array_key_exists(JsonSerialize::class, $traits);
    }

    protected function jsonValue($value)
    {
        if (!is_object($value)) {
            return $value;
        }

        if (!$this->hasTrait($value)) {
            return $value;
        }

        return $value->toJsonData();
    }
}
