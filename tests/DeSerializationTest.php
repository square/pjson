<?php declare(strict_types=1);
namespace Square\Pjson\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Square\Pjson\Pjson;
use Square\Pjson\Tests\Definitions\BigCat;
use Square\Pjson\Tests\Definitions\BigInt;
use Square\Pjson\Tests\Definitions\CatalogCategory;
use Square\Pjson\Tests\Definitions\CatalogItem;
use Square\Pjson\Tests\Definitions\CatalogObject;
use Square\Pjson\Tests\Definitions\Category;
use Square\Pjson\Tests\Definitions\MenuList;
use Square\Pjson\Tests\Definitions\Schedule;
use Square\Pjson\Tests\Definitions\Privateer;
use Square\Pjson\Tests\Definitions\Stats;
use Square\Pjson\Tests\Definitions\Traitor;
use Square\Pjson\Tests\Definitions\Weekend;
use Square\Pjson\Tests\Definitions\Traitless;

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

    public function testDeserializesSimpleClassTraitless()
    {
        $c = Pjson::fromJsonString(
            '{"identifier":"myid","category_name":"Clothes","data":{"name":null}}',
            Traitless\Category::class
        );
        $this->assertEquals([
            '@class' => Traitless\Category::class,
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

    public function testThrowsOnError()
    {
        $this->expectException(\JsonException::class);
        $this->expectExceptionMessage('Syntax error');

        $c = Category::fromJsonString('{"identifier":"myid","category_name":"Clothes","data":{"name":nil}}');
    }

    public function testThrowsOnErrorTraitless()
    {
        $this->expectException(\JsonException::class);
        $this->expectExceptionMessage('Syntax error');

        $c = Pjson::fromJsonString(
            '{"identifier":"myid","category_name":"Clothes","data":{"name":nil}}',
            Traitless\Category::class
        );
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

    public function testOmitsEmptyValuesTraitless()
    {
        $bc = Pjson::fromJsonString('{"identifier":"myid","category_name":"Clothes"}', Traitless\BigCat::class);
        $e = $this->export($bc);
        $this->assertEquals([
            '@class' => Traitless\BigCat::class,
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

    public function testInheritsTraitTraitless()
    {
        $s = Pjson::fromJsonString('{"schedule_start":1,"schedule_end":10}', Traitless\Schedule::class);
        $this->assertEquals([
            '@class' => Traitless\Schedule::class,
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

    public function testSerializesClassAttributesRecursivelyTraitless()
    {
        $c = Pjson::fromJsonString('{
            "identifier": "myid",
            "category_name": "Clothes",
            "data": {
                "name": null
            },
            "next_schedule": {
                "schedule_start": 1,
                "schedule_end": 20
            }
        }', Traitless\Category::class);
        $this->assertEquals([
            '@class' => Traitless\Category::class,
            "id" => "myid",
            "name" => "Clothes",
            "data_name" => null,
            "schedule" =>  [
              '@class' => Traitless\Schedule::class,
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

    public function testSerializesObjectArraysTraitless()
    {
        $c = Pjson::fromJsonString('{
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
        }', Traitless\Category::class);
        $this->assertEquals([
            '@class' => Traitless\Category::class,
            "id" => "myid",
            "name" => "Clothes",
            "data_name" => null,
            "schedule" => [
              '@class' => Traitless\Schedule::class,
              "start" => 1,
              "end" => 20,
            ],
            "schedules" => [
              0 => [
                '@class' => Traitless\Schedule::class,
                "start" => 1,
                "end" => 20,
              ],
              1 => [
                '@class' => Traitless\Schedule::class,
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

    public function testSerializesScalarArraysTraitless()
    {
        $c = Pjson::fromJsonString('{
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
        }', Traitless\Category::class);
        $this->assertEquals([
            '@class' => Traitless\Category::class,
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

    public function testSerializesWithNoNameTraitless()
    {
        $c = Pjson::fromJsonString('{
            "identifier": "myid",
            "category_name": "Clothes",
            "data": {
                "name": null
            },
            "unnamed": "bob"
        }', Traitless\Category::class);

        $this->assertEquals([
            '@class' => Traitless\Category::class,
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

    public function testSerializesWithUntypedPropTraitless()
    {
        $c = Pjson::fromJsonString('{
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
        }', Traitless\Category::class);

        $this->assertEquals([
            '@class' => Traitless\Category::class,
            "id" => "myid",
            "name" => "Clothes",
            "data_name" => null,
            "schedule" => null,
            "schedules" => null,
            "counts" => [],
            "unnamed" => "bob",
            "untypedSchedule" => [
                '@class' => Traitless\Schedule::class,
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

    public function testPrivatePropsTraitless()
    {
        $p = Pjson::fromJsonString('{
            "name": "Jenna"
        }', Traitless\Privateer::class);
        $this->assertEquals([
            '@class' => Traitless\Privateer::class,
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

    public function testHashMapsTraitless()
    {
        $w = Pjson::fromJsonString('{
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
        }', Traitless\Weekend::class);
        $this->assertEquals([
            '@class' => Traitless\Weekend::class,
            "weekend" => [
              "sat" => [
                '@class' => Traitless\Schedule::class,
                "start" => 1,
                "end" => 2,
              ],
              "sun" => [
                '@class' => Traitless\Schedule::class,
                "start" => 3,
                "end" => 4,
              ],
            ],
        ], $this->export($w));
    }

    public function testTraitProps()
    {
        $t = Traitor::fromJsonString('{"secretly_working_for": "MI6"}');

        $this->assertEquals([
            "@class" => Traitor::class,
            "secretly_working_for" => "MI6"
        ], $this->export($t));
    }

    public function testTraitPropsTraitless()
    {
        $t = Pjson::fromJsonString('{"secretly_working_for": "MI6"}', Traitless\Traitor::class);

        $this->assertEquals([
            "@class" => Traitless\Traitor::class,
            "secretly_working_for" => "MI6"
        ], $this->export($t));
    }

    public function testPolymorphicClass()
    {
        $jsonCat = '{"type": "category", "id": "123", "parent_category_id": "456"}';
        $c = CatalogObject::fromJsonString($jsonCat);

        $this->assertEquals([
            "@class" => CatalogCategory::class,
            "parentCategoryId" => "456",
            "id" => "123",
            "type" => "category",
        ], $this->export($c));

        $jsonItem = '{"type": "item", "id": "123", "name": "Sandals"}';
        $c = CatalogObject::fromJsonString($jsonItem);
        $this->assertEquals([
            "@class" => CatalogItem::class,
            "name" => "Sandals",
            "id" => "123",
            "type" => "item",
        ], $this->export($c));
    }

    public function testPolymorphicClassTraitless()
    {
        $jsonCat = '{"type": "category", "id": "123", "parent_category_id": "456"}';
        $c = Pjson::fromJsonString($jsonCat, Traitless\CatalogObject::class);

        $this->assertEquals([
            "@class" => Traitless\CatalogCategory::class,
            "parentCategoryId" => "456",
            "id" => "123",
            "type" => "category",
        ], $this->export($c));

        $jsonItem = '{"type": "item", "id": "123", "name": "Sandals"}';
        $c = Pjson::fromJsonString($jsonItem, Traitless\CatalogObject::class);
        $this->assertEquals([
            "@class" => Traitless\CatalogItem::class,
            "name" => "Sandals",
            "id" => "123",
            "type" => "item",
        ], $this->export($c));
    }

    public function testList()
    {
        $deser = Schedule::listFromJsonString('[
            {
                "schedule_start": 1,
                "schedule_end": 2
            },
            {
                "schedule_start": 11,
                "schedule_end": 22
            },
            {
                "schedule_start": 111,
                "schedule_end": 222
            }
        ]');

        $this->assertEquals([
            [
              "@class" => Schedule::class,
              "start" => 1,
              "end" => 2,
            ],
            [
              "@class" => Schedule::class,
              "start" => 11,
              "end" => 22,
            ],
            [
              "@class" => Schedule::class,
              "start" => 111,
              "end" => 222,
            ]
        ], $this->export($deser));
    }

    public function testListTraitless()
    {
        $deser = Pjson::listFromJsonString('[
            {
                "schedule_start": 1,
                "schedule_end": 2
            },
            {
                "schedule_start": 11,
                "schedule_end": 22
            },
            {
                "schedule_start": 111,
                "schedule_end": 222
            }
        ]', Traitless\Schedule::class);

        $this->assertEquals([
            [
              "@class" => Traitless\Schedule::class,
              "start" => 1,
              "end" => 2,
            ],
            [
              "@class" => Traitless\Schedule::class,
              "start" => 11,
              "end" => 22,
            ],
            [
              "@class" => Traitless\Schedule::class,
              "start" => 111,
              "end" => 222,
            ]
        ], $this->export($deser));
    }

    public function testPaths()
    {
        $deser = Schedule::fromJsonString('{
            "data": {
                "schedule_start": 1,
                "schedule_end": 2
            }
        }', path: 'data');
        $this->assertEquals([
            "@class" => Schedule::class,
            "start" => 1,
            "end" => 2,
        ], $this->export($deser));

        $deser = Schedule::fromJsonString('{
            "data": {
                "first_schedule": {
                    "schedule_start": 1,
                    "schedule_end": 2
                }
            }
        }', path: ['data', 'first_schedule']);
        $this->assertEquals([
            "@class" => Schedule::class,
            "start" => 1,
            "end" => 2,
        ], $this->export($deser));

        $deser = Schedule::listFromJsonString('{
            "data": [
                {
                    "schedule_start": 1,
                    "schedule_end": 2
                }
            ]
        }', path: 'data');
        $this->assertEquals([
            [
                "@class" => Schedule::class,
                "start" => 1,
                "end" => 2,
            ]
        ], $this->export($deser));

        $deser = Schedule::listFromJsonString('{
            "data": {
                "first_schedule": [
                    {
                        "schedule_start": 1,
                        "schedule_end": 2
                    }
                ]
            }
        }', path: ['data', 'first_schedule']);
        $this->assertEquals([
            [
                "@class" => Schedule::class,
                "start" => 1,
                "end" => 2,
            ]
        ], $this->export($deser));
    }

    public function testPathsTraitless()
    {
        $deser = Pjson::fromJsonString('{
            "data": {
                "schedule_start": 1,
                "schedule_end": 2
            }
        }', path: 'data', type: Traitless\Schedule::class);
        $this->assertEquals([
            "@class" => Traitless\Schedule::class,
            "start" => 1,
            "end" => 2,
        ], $this->export($deser));

        $deser = Pjson::fromJsonString('{
            "data": {
                "first_schedule": {
                    "schedule_start": 1,
                    "schedule_end": 2
                }
            }
        }', path: ['data', 'first_schedule'], type: Traitless\Schedule::class);
        $this->assertEquals([
            "@class" => Traitless\Schedule::class,
            "start" => 1,
            "end" => 2,
        ], $this->export($deser));

        $deser = Pjson::listFromJsonString('{
            "data": [
                {
                    "schedule_start": 1,
                    "schedule_end": 2
                }
            ]
        }', path: 'data', type: Traitless\Schedule::class);
        $this->assertEquals([
            [
                "@class" => Traitless\Schedule::class,
                "start" => 1,
                "end" => 2,
            ]
        ], $this->export($deser));

        $deser = Pjson::listFromJsonString('{
            "data": {
                "first_schedule": [
                    {
                        "schedule_start": 1,
                        "schedule_end": 2
                    }
                ]
            }
        }', path: ['data', 'first_schedule'], type: Traitless\Schedule::class);
        $this->assertEquals([
            [
                "@class" => Traitless\Schedule::class,
                "start" => 1,
                "end" => 2,
            ]
        ], $this->export($deser));
    }

    public function testClassToScalar()
    {
        $stats =Stats::fromJsonString('{
            "count": "123456789876543234567898765432345678976543234567876543212345678765432"
        }');
        $this->assertEquals([
            "@class" => Stats::class,
            "count" => [
              "@class" => BigInt::class,
              "value" => "123456789876543234567898765432345678976543234567876543212345678765432",
            ]
        ], $this->export($stats));
    }

    public function testClassToScalarTraitless()
    {
        $stats =Pjson::fromJsonString('{
            "count": "123456789876543234567898765432345678976543234567876543212345678765432"
        }', Traitless\Stats::class);
        $this->assertEquals([
            "@class" => Traitless\Stats::class,
            "count" => [
              "@class" => Traitless\BigInt::class,
              "value" => "123456789876543234567898765432345678976543234567876543212345678765432",
            ]
        ], $this->export($stats));
    }

    public function testIntegerPath()
    {
        $json = '{
            "menus": [
                {"main": true, "name": "main-menu"},
                {"main": false, "name": "secondary-menu"}
            ]
        }';

        $dl = MenuList::fromJsonString($json);

        $this->assertEquals([
            "@class" => MenuList::class,
            "mainMenuName" => "main-menu"
        ], $this->export($dl));
    }

    public function testIntegerPathTraitless()
    {
        $json = '{
            "menus": [
                {"main": true, "name": "main-menu"},
                {"main": false, "name": "secondary-menu"}
            ]
        }';

        $dl = Pjson::fromJsonString($json, Traitless\MenuList::class);

        $this->assertEquals([
            "@class" => Traitless\MenuList::class,
            "mainMenuName" => "main-menu"
        ], $this->export($dl));
    }
}
