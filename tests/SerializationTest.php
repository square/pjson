<?php declare(strict_types=1);
namespace Square\Pjson\Tests;

use PHPUnit\Framework\TestCase;
use Square\Pjson\Tests\Definitions\BigCat;
use Square\Pjson\Tests\Definitions\CatalogCategory;
use Square\Pjson\Tests\Definitions\CatalogItem;
use Square\Pjson\Tests\Definitions\CatalogObject;
use Square\Pjson\Tests\Definitions\Category;
use Square\Pjson\Tests\Definitions\Privateer;
use Square\Pjson\Tests\Definitions\Schedule;
use Square\Pjson\Tests\Definitions\Traitor;
use Square\Pjson\Tests\Definitions\Weekend;

final class SerializationTest extends TestCase
{
    public function testSerializesSimpleClass()
    {
        $c = new Category;
        $this->assertEquals('{"identifier":"myid","category_name":"Clothes","data":{"name":null}}', $c->toJson());
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
        $this->assertEquals(json_encode(json_decode('{
            "identifier": "myid",
            "category_name": "Clothes",
            "data": {
                "name": null
            },
            "next_schedule": {
                "schedule_start": 1,
                "schedule_end": 20
            }
        }')), $c->toJson());
    }

    public function testSerializesObjectArrays()
    {
        $c = new Category;
        $c->setSchedule(new Schedule(1, 20));
        $c->setUpcoming([new Schedule(1, 20), new Schedule(30, 40)]);
        $this->assertEquals(json_encode(json_decode('{
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
        }')), $c->toJson());
    }

    public function testSerializesScalarArrays()
    {
        $c = new Category;
        $c->counts = [1, 'abc', 678];
        $this->assertEquals(json_encode(json_decode('{
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
        }')), $c->toJson());
    }

    public function testSerializesWithNoName()
    {
        $c = new Category;
        $c->unnamed = 'bob';
        $this->assertEquals(json_encode(json_decode('{
            "identifier": "myid",
            "category_name": "Clothes",
            "data": {
                "name": null
            },
            "unnamed": "bob"
        }')), $c->toJson());
    }

    public function testPrivateProps()
    {
        $p = new Privateer;
        $this->assertEquals(json_encode(json_decode('{
            "name": "Jenna"
        }')), $p->toJson());
    }

    public function testHashMaps()
    {
        $w = new Weekend;
        $this->assertEquals(json_encode(json_decode('{
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
        }')), $w->toJson());
    }

    public function testTraitProps()
    {
        $t = new Traitor;
        $this->assertEquals(json_encode(json_decode('{"secretly_working_for": "MI6"}')), $t->toJson());
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
        $this->assertEquals(json_encode(json_decode('[
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
        ]')), $jl);
    }
}
