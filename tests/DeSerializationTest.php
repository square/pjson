<?php declare(strict_types=1);
namespace Squareup\Pjson\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Squareup\Pjson\Tests\Definitions\BigCat;
use Squareup\Pjson\Tests\Definitions\Category;
use Squareup\Pjson\Tests\Definitions\Schedule;
use Squareup\Pjson\Tests\Definitions\Privateer;
use Squareup\Pjson\Tests\Definitions\Weekend;

final class DeSerializationTest extends TestCase
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
            $prop->setAccessible(true);
            $v = $prop->isInitialized($value) ? $prop->getValue($value) : null;
            $n = $prop->getName();

            $data[$n] = $this->export($v);
        }

        return $data;
    }

    public function testDeserializesSimpleClass()
    {
        $c = Category::fromJsonString('{"identifier":"myid","category_name":"Clothes","data":{"name":null}}');
        $this->assertEquals([
            '@class' => Category::class,
            "id" => "myid",
            "name" => "Clothes",
            "data_name" => null,
            "schedule" => null,
            "schedules" => null,
            "counts" => [],
            "unnamed" => null,
            "untypedSchedule" => null,
          ], $this->export($c));
    }



    public function testOmitsEmptyValues()
    {
        $bc = BigCat::fromJsonString('{"identifier":"myid","category_name":"Clothes"}');
        $e = $this->export($bc);
        $this->assertEquals([
            '@class' => BigCat::class,
            "data_name" => null,
            "id" => "myid",
            "name" => "Clothes",
            "schedule" => null,
            "schedules" => null,
            "counts" => [],
            "unnamed" => null,
            "untypedSchedule" => null,
          ], $this->export($bc));
    }

    public function testInheritsTrait()
    {
        $s = Schedule::fromJsonString('{"schedule_start":1,"schedule_end":10}');
        $this->assertEquals([
            '@class' => Schedule::class,
            "start" => 1,
            "end" => 10,
        ], $this->export($s));
    }

    public function testSerializesClassAttributesRecursively()
    {
        $c = Category::fromJsonString('{
            "identifier": "myid",
            "category_name": "Clothes",
            "data": {
                "name": null
            },
            "next_schedule": {
                "schedule_start": 1,
                "schedule_end": 20
            }
        }');
        $this->assertEquals([
            '@class' => Category::class,
            "id" => "myid",
            "name" => "Clothes",
            "data_name" => null,
            "schedule" =>  [
              '@class' => Schedule::class,
              "start" => 1,
              "end" => 20,
            ],
            "schedules" => null,
            "counts" => [],
            "unnamed" => null,
            "untypedSchedule" => null,
        ], $this->export($c));
    }

    public function testSerializesObjectArrays()
    {
        $c = Category::fromJsonString('{
            "identifier": "myid",
            "category_name": "Clothes",
            "data": {
                "name": null
            },
            "next_schedule": {
                "schedule_start": 1,
                "schedule_end": 20
            },
            "upcomming_schedules": [
                {
                    "schedule_start": 1,
                    "schedule_end": 20
                },
                {
                    "schedule_start": 30,
                    "schedule_end": 40
                }
            ]
        }');
        $this->assertEquals([
            '@class' => Category::class,
            "id" => "myid",
            "name" => "Clothes",
            "data_name" => null,
            "schedule" => [
              '@class' => Schedule::class,
              "start" => 1,
              "end" => 20,
            ],
            "schedules" => [
              0 => [
                '@class' => Schedule::class,
                "start" => 1,
                "end" => 20,
              ],
              1 => [
                '@class' => Schedule::class,
                "start" => 30,
                "end" => 40,
              ],
            ],
            "counts" => [],
            "unnamed" => null,
            "untypedSchedule" => null,
        ], $this->export($c));
    }

    public function testSerializesScalarArrays()
    {
        $c = Category::fromJsonString('{
            "identifier": "myid",
            "category_name": "Clothes",
            "data": {
                "name": null
            },
            "counts": [
                1,
                "abc",
                678
            ]
        }');
        $this->assertEquals([
            '@class' => Category::class,
            "id" => "myid",
            "name" => "Clothes",
            "data_name" => null,
            "schedule" => null,
            "schedules" => null,
            "counts" => [
              0 => 1,
              1 => "abc",
              2 => 678,
            ],
            "unnamed" => null,
            "untypedSchedule" => null,
          ], $this->export($c));
    }

    public function testSerializesWithNoName()
    {
        $c = Category::fromJsonString('{
            "identifier": "myid",
            "category_name": "Clothes",
            "data": {
                "name": null
            },
            "unnamed": "bob"
        }');

        $this->assertEquals([
            '@class' => Category::class,
            "id" => "myid",
            "name" => "Clothes",
            "data_name" => null,
            "schedule" => null,
            "schedules" => null,
            "counts" => [],
            "unnamed" => "bob",
            "untypedSchedule" => null,
          ], $this->export($c));
    }

    public function testSerializesWithUntypedProp()
    {
        $c = Category::fromJsonString('{
            "identifier": "myid",
            "category_name": "Clothes",
            "data": {
                "name": null
            },
            "unnamed": "bob",
            "untyped_schedule": {
                "schedule_start": 10,
                "schedule_end": 90
            }
        }');

        $this->assertEquals([
            '@class' => Category::class,
            "id" => "myid",
            "name" => "Clothes",
            "data_name" => null,
            "schedule" => null,
            "schedules" => null,
            "counts" => [],
            "unnamed" => "bob",
            "untypedSchedule" => [
                '@class' => Schedule::class,
                "start" => 10,
                "end" => 90,
            ],
          ], $this->export($c));
    }

    public function testPrivateProps()
    {
        $p = Privateer::fromJsonString('{
            "name": "Jenna"
        }');
        $this->assertEquals([
            '@class' => Privateer::class,
            "name" => "Jenna",
        ], $this->export($p));
    }

    public function testHashMaps()
    {
        $w = Weekend::fromJsonString('{
            "weekend": {
                "sat": {
                    "schedule_start": 1,
                    "schedule_end": 2
                },
                "sun": {
                    "schedule_start": 3,
                    "schedule_end": 4
                }
            }
        }');
        $this->assertEquals([
            '@class' => Weekend::class,
            "weekend" => [
              "sat" => [
                '@class' => Schedule::class,
                "start" => 1,
                "end" => 2,
              ],
              "sun" => [
                '@class' => Schedule::class,
                "start" => 3,
                "end" => 4,
              ],
            ],
        ], $this->export($w));
    }
}
