<?php declare(strict_types=1);
namespace Square\Pjson;

use Attribute;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use Square\Pjson\Exceptions\MissingRequiredPropertyException;
use Square\Pjson\Internal\RClass;
use Traversable;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Json
{
    protected array $path;

    protected string $type;

    protected bool $omit_empty;

    protected string $collection_factory_method;

    protected bool $required;

    public function __construct(
        string|array $path = '',
        string $type = '',
        bool $omit_empty = false,
        string $collection_factory_method = '',
        bool $required = false,
    ) {
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
        $this->collection_factory_method = $collection_factory_method;
        $this->required = $required;
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
     * Builds the PHP value from the json data and a type if available
     */
    public function retrieveValue(?array $data, ReflectionNamedType|ReflectionUnionType|null $type = null)
    {
        foreach ($this->path as $pathBit) {
            if (!array_key_exists($pathBit, $data)) {
                return $this->handleMissingValue($data);
            }
            $data = $data[$pathBit];
        }

        if ($type instanceof ReflectionUnionType) {
            $typesByType = $type->getTypes();
            foreach ($type->getTypes() as $type) {
                $typesByType[$type->getName()] = $type;
            }
            $type = match (gettype($data)) {
                'string' => $typesByType['string'],
                'boolean' => $typesByType['bool'],
                'integer' => $typesByType['int'],
                'array' => $typesByType['array'],
            };
        }


        if (is_null($data) && $type && $type->allowsNull()) {
            return null;
        }

        if ($type === null) {
            if (isset($this->type)) {
                $t = $this->type;
                return $t::fromJsonData($data);
            }
            return $data;
        }

        $typename = $type->getName();

        if (!class_exists($typename) && ($typename !== 'array' || !isset($this->type))) {
            return $data;
        }
        if (!class_exists($typename) && $typename === 'array' && isset($this->type)) {
            if (is_null($data)) {
                return $data;
            }

            if (RClass::make($this->type)->isBackedEnum()) {
                return array_map(fn ($d) => $this->type::from($d), $data);
            }

            return array_map(fn ($d) => $this->type::fromJsonData($d), $data);
        }

        // Deal with collections / Traversable classes
        if (class_exists($typename) && $this->isCollection($typename) && isset($this->type)) {
            $mapped = array_map(fn ($d) => $this->type::fromJsonData($d), $data);
            if (!isset($this->collection_factory_method) || $this->collection_factory_method === '') {
                return new $typename($mapped);
            }
            if (RClass::make($typename)->isMethodStatic($this->collection_factory_method)) {
                return $typename::{trim($this->collection_factory_method, ':')}($mapped);
            }

            $r = RClass::make($typename);
            $instance = $r->source()->newInstanceWithoutConstructor();
            $instance->{$this->collection_factory_method}($mapped);
            return $instance;
        }

        if (RClass::make($typename)->readsFromJson()) {
            return $typename::fromJsonData($data);
        }

        if (RClass::make($typename)->isBackedEnum()) {
            if ($type->allowsNull()) {
                return $typename::tryFrom($data);
            }
            return $typename::from($data);
        }

        return $data;
    }

    /**
     * What happens when deserializing a property that isn't set.
     */
    protected function handleMissingValue($data)
    {
        if ($this->required) {
            throw new MissingRequiredPropertyException($this->path, json_encode($data));
        }
        return null;
    }

    protected function isCollection(string $className)
    {
        return is_subclass_of($className, Traversable::class);
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
    public function appendValue(array &$data, $value)
    {
        if ($this->omit_empty && $this->isEmpty($value)) {
            return;
        }
        $max = count($this->path)-1;
        $d = &$data;
        foreach ($this->path as $i => $pathBit) {
            if (array_key_exists($pathBit, $d) && $i === $max) {
                throw new \Exception('invalid path: '.json_encode($this->path));
            }

            if ((!array_key_exists($pathBit, $d) || is_null($d[$pathBit])) && $i < $max) {
                $d[$pathBit] = [];
            }

            if ($i < $max) {
                $d = &$d[$pathBit];
            }

            if ($i === $max) {
                if (is_array($value) || $value instanceof Traversable) {
                    $d[$pathBit] = [];
                    foreach ($value as $k => $val) {
                        $d[$pathBit][$k] = $this->jsonValue($val);
                    }
                } else {
                    $d[$pathBit] = $this->jsonValue($value);
                }
            }
        }
    }

    protected function jsonValue($value)
    {
        if (!is_object($value)) {
            return $value;
        }

        if (RClass::make($value)->writesToJson()) {
            return $value->toJsonData();
        }

        if (RClass::make($value)->isSimpleEnum()) {
            return $value->name;
        }

        return $value;
    }
}
