<?php declare(strict_types=1);
namespace Square\Pjson\Tests\php81;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Square\Pjson\Tests\Definitions\DTO;
use Square\Pjson\Tests\Definitions\Size;
use Square\Pjson\Tests\Definitions\Status;
use Square\Pjson\Tests\Definitions\StatusList;
use Square\Pjson\Tests\Definitions\Widget;

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
            $v = $prop->isInitialized($value) ? $prop->getValue($value) : '@uninitialized';
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

    public function testBackedEnum()
    {
        $w = Widget::fromJsonString('{
            "status": "ON",
            "optional": null
        }');
        $this->assertEquals([
            "@class" => Widget::class,
            "status" => [
              "@class" => Status::class,
              "name" => "ON",
              "value" => "ON",
            ],
            "optional" => null,
            "size" => '@uninitialized',
        ], $this->export($w));
    }

    public function testEnum()
    {
        $w = Widget::fromJsonString('{
            "status": "ON",
            "optional": null,
            "size": "BIG"
        }');
        $this->assertEquals([
            "@class" => Widget::class,
            "status" => [
              "@class" => Status::class,
              "name" => "ON",
              "value" => "ON",
            ],
            "optional" => null,
            "size" => [
                '@class' => Size::class,
                'name' => 'BIG',
            ],
        ], $this->export($w));

        $w = Widget::fromJsonString('{
            "status": "ON",
            "optional": null,
            "size": "big"
        }');
        $this->assertEquals([
            "@class" => Widget::class,
            "status" => [
              "@class" => Status::class,
              "name" => "ON",
              "value" => "ON",
            ],
            "optional" => null,
            "size" => [
                '@class' => Size::class,
                'name' => 'BIG',
            ],
        ], $this->export($w));
    }

    public function testBackedEnumArray()
    {
        $w = StatusList::fromJsonString('{
            "status_list": ["ON", "OFF", "ON"]
        }');
        $this->assertEquals([
            "@class" => StatusList::class,
            "statusList" => [
                0 => [
                    "@class" => Status::class,
                    "name" => "ON",
                    "value" => "ON",
                ],
                1 => [
                    "@class" => Status::class,
                    "name" => "OFF",
                    "value" => "OFF",
                ],
                2 => [
                    "@class" => Status::class,
                    "name" => "ON",
                    "value" => "ON",
                ]
            ],
        ], $this->export($w));
    }
}
