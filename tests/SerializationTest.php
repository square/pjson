<?php declare(strict_types=1);
namespace Square\Pjson\Tests;

use PHPUnit\Framework\TestCase;
use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;
use Square\Pjson\Tests\Definitions\BigCat;
use Square\Pjson\Tests\Definitions\BigInt;
use Square\Pjson\Tests\Definitions\CatalogCategory;
use Square\Pjson\Tests\Definitions\CatalogItem;
use Square\Pjson\Tests\Definitions\CatalogObject;
use Square\Pjson\Tests\Definitions\Category;
use Square\Pjson\Tests\Definitions\Child;
use Square\Pjson\Tests\Definitions\Collector;
use Square\Pjson\Tests\Definitions\Container;
use Square\Pjson\Tests\Definitions\Deep;
use Square\Pjson\Tests\Definitions\MenuList;
use Square\Pjson\Tests\Definitions\MergeOne;
use Square\Pjson\Tests\Definitions\MergeTwo;
use Square\Pjson\Tests\Definitions\Privateer;
use Square\Pjson\Tests\Definitions\Recursive;
use Square\Pjson\Tests\Definitions\Schedule;
use Square\Pjson\Tests\Definitions\Shared;
use Square\Pjson\Tests\Definitions\Stats;
use Square\Pjson\Tests\Definitions\Traitor;
use Square\Pjson\Tests\Definitions\Weekend;

final class SerializationTest extends TestCase
{
    public function testSerializesSimpleClass()
    {
        $c = new Category;
        $this->assertEquals('{"identifier":"myid","category_name":"Clothes","data":{"name":null}}', $c->toJson());
    }

    public function testNullableProperty()
    {
        $c = new Category;
        $c->nullableSchedule = null;
        $this->assertEquals($this->comparableJson('{
            "identifier": "myid",
            "category_name": "Clothes",
            "data": {
                "name": null
            },
            "nullable_schedule": null
        }'), $c->toJson());
    }

    public function testSerializeAcceptsJsonFlags()
    {
        $c = new Category;
        $expected = <<<JSON
        {
            "identifier": "myid",
            "category_name": "Clothes",
            "data": {
                "name": null
            }
        }
        JSON;


        $this->assertEquals($expected, $c->toJson(flags: JSON_PRETTY_PRINT));
    }

    public function testSerializeThrowsOnJsonError()
    {
        $c = new class {
            use JsonSerialize;

            public function __construct(
                #[Json]
                private float $infinity = INF,
            ) {
                //
            }
        };

        $this->expectException(\JsonException::class);
        $this->expectExceptionMessage('Inf and NaN cannot be JSON encoded');
        $c->toJson();
    }

    public function testOmitsEmptyValues()
    {
        $bc = new BigCat;
        $this->assertEquals('{"identifier":"myid","category_name":"Clothes"}', $bc->toJson());
    }

    public function testInheritsTrait()
    {
        $s = new Schedule(1, 10);
        $this->assertEquals('{"schedule_start":1,"schedule_end":10}', $s->toJson());
    }

    public function testSerializesClassAttributesRecursively()
    {
        $c = new Category;
        $c->setSchedule(new Schedule(1, 20));
        $this->assertEquals($this->comparableJson('{
            "identifier": "myid",
            "category_name": "Clothes",
            "data": {
                "name": null
            },
            "next_schedule": {
                "schedule_start": 1,
                "schedule_end": 20
            }
        }'), $c->toJson());
    }

    public function testSerializesObjectArrays()
    {
        $c = new Category;
        $c->setSchedule(new Schedule(1, 20));
        $c->setUpcoming([new Schedule(1, 20), new Schedule(30, 40)]);
        $c->setNullable();
        $this->assertEquals($this->comparableJson('{
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
            ],
            "nullable_schedules": null
        }'), $c->toJson());
    }

    public function testSerializesScalarArrays()
    {
        $c = new Category;
        $c->counts = [1, 'abc', 678];
        $this->assertEquals($this->comparableJson('{
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
        }'), $c->toJson());
    }

    public function testSerializesWithNoName()
    {
        $c = new Category;
        $c->unnamed = 'bob';
        $this->assertEquals($this->comparableJson('{
            "identifier": "myid",
            "category_name": "Clothes",
            "data": {
                "name": null
            },
            "unnamed": "bob"
        }'), $c->toJson());
    }

    public function testPrivateProps()
    {
        $p = new Privateer;
        $this->assertEquals($this->comparableJson('{
            "name": "Jenna"
        }'), $p->toJson());
    }

    public function testHashMaps()
    {
        $w = new Weekend;
        $this->assertEquals($this->comparableJson('{
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
        }'), $w->toJson());
    }

    public function testTraitProps()
    {
        $t = new Traitor;
        $this->assertEquals($this->comparableJson('{"secretly_working_for": "MI6"}'), $t->toJson());
    }

    public function testPolymorphicClass()
    {
        $jsonCat = '{"type": "category", "id": "123", "parent_category_id": "456"}';
        $c = CatalogObject::fromJsonString($jsonCat);
        $this->assertEquals(CatalogCategory::class, get_class($c));
        $this->assertEquals('{"parent_category_id":"456","id":"123","type":"category"}', $c->toJson());

        $jsonItem = '{"type": "item", "id": "123", "name": "Sandals"}';
        $c = CatalogObject::fromJsonString($jsonItem);
        $this->assertEquals(CatalogItem::class, get_class($c));
        $this->assertEquals('{"name":"Sandals","id":"123","type":"item"}', $c->toJson());
    }

    public function testList()
    {
        $l = [
            new Schedule(1, 2),
            new Schedule(11, 22),
            new Schedule(111, 222),
        ];

        $jl = Schedule::toJsonList($l);
        $this->assertEquals($this->comparableJson('[
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
        ]'), $jl);
    }

    public function testClassToScalar()
    {
        $stats = new Stats;
        $stats->count = new BigInt("123456789876543234567898765432345678976543234567876543212345678765432");
        $this->assertEquals(
            '{"count":"123456789876543234567898765432345678976543234567876543212345678765432"}',
            $stats->toJson()
        );
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

        // MenuList doesn't store the entire structure. Only the name of the first menu
        // it can however still output all the data it has back into its original shape
        $this->assertEquals('{"menus":[{"name":"main-menu"}]}', $dl->toJson());
    }

    public function testCollections()
    {
        $json = '{
            "schedules": [
                {
                    "schedule_start": 1,
                    "schedule_end": 2
                },
                {
                    "schedule_start": 10,
                    "schedule_end": 20
                }
            ],
            "static_factoried_schedules": [
                {
                    "schedule_start": 1,
                    "schedule_end": 2
                },
                {
                    "schedule_start": 10,
                    "schedule_end": 20
                }
            ],
            "factoried_schedules": [
                {
                    "schedule_start": 1,
                    "schedule_end": 2
                },
                {
                    "schedule_start": 10,
                    "schedule_end": 20
                }
            ]
        }';

        $data = Collector::fromJsonString($json);

        $this->assertEquals($this->comparableJson($json), $data->toJson());
    }

    public function testMissingParent()
    {
        // Ensure that while the `parent` object is null, we can still serialize the parent.id property which is
        // nested under `parent`.
        $data = new Child();
        $json = '{"identifier":null,"parent":{"id":null}}';

        $this->assertEquals($this->comparableJson($json), $data->toJson());
    }

    protected function comparableJson(string $json) : string
    {
        return json_encode(json_decode($json));
    }

    public function testEmptyPathProperty()
    {
        $container = new Container(
            new Shared(7, 'content'),
            'test'
        );

        $json = $container->toJson();

        $this->assertJsonStringEqualsJsonString('{
            "data": 7,
            "additional": "test",
            "other": "content"
        }', $json);
    }

    public function testDeepEmptyPathMerge()
    {
        $deep = new Deep(
            3.0,
            new Container(
                new Shared(7, 'content'),
                'test'
            )
        );

        $json = $deep->toJson();

        $this->assertJsonStringEqualsJsonString('{
            "depth": 3.0,
            "data": 7,
            "additional": "test",
            "other": "content"
        }', $json);
    }

    public function testEmptyPathRecursiveMerge()
    {
        $recursive = new Recursive(
            new MergeOne('first'),
            new MergeTwo('second'),
            new Shared(5, 'other')
        );

        $json = $recursive->toJson();

        $this->assertJsonStringEqualsJsonString('{
            "sub": {
                "one": "first",
                "two": "second",
                "data": 5,
                "other": "other"
            }
        }', $json);
    }
}
