<?php declare(strict_types=1);

namespace Square\Pjson\Internal;

use ReflectionClass;

class RClass
{
    protected static array $cache = [];

    protected ReflectionClass $rc;

    protected array $props;

    protected function __construct($class)
    {
        $this->rc = new ReflectionClass($class);
        $this->props = $this->rc->getProperties();

        foreach ($this->props as $prop) {
            $prop->setAccessible(true);
        }
    }

    public static function make($class) : RClass
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (!array_key_exists($class, self::$cache)) {
            self::$cache[$class] = new self($class);
        }

        return self::$cache[$class];
    }

    public function getProperties()
    {
        return $this->props;
    }

    public function source() : ReflectionClass
    {
        return $this->rc;
    }
}
