<?php declare(strict_types=1);
namespace Squareup\Pjson\Tests\php81;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Squareup\Pjson\Tests\Definitions\DTO;

final class Php81DeSerializationTest extends TestCase
{
    public function export($value)
    {
        if (is_null($value)) {
            return $value;
        }

        if (is_scalar($value)) {
            return $value;
        }

        if (is_array($value)) {
            $r = [];
            foreach ($value as $k => $v) {
                $r[$k] = $this->export($v);
            }
            return $r;
        }
        $rc = new ReflectionClass($value);
        $data = [
            '@class' => get_class($value),
        ];
        foreach ($rc->getProperties() as $prop) {
            $v = $prop->isInitialized($value) ? $prop->getValue($value) : null;
            $n = $prop->getName();

            $data[$n] = $this->export($v);
        }

        return $data;
    }

    public function testReadOnly()
    {
        $d = DTO::fromJsonString('{
            "value": 6
        }');
        $this->assertEquals([
            '@class' => DTO::class,
            "value" => 6,
        ], $this->export($d));
    }
}
