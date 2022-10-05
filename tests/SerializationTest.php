<?php declare(strict_types=1);
namespace Squareup\Pjson\Tests;

use PHPUnit\Framework\TestCase;
use Squareup\Pjson\Tests\Definitions\BigCat;
use Squareup\Pjson\Tests\Definitions\Category;
use Squareup\Pjson\Tests\Definitions\DTO;
use Squareup\Pjson\Tests\Definitions\Privateer;
use Squareup\Pjson\Tests\Definitions\Schedule;
use Squareup\Pjson\Tests\Definitions\Weekend;

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

    public function testReadOnly()
    {
        $d = new DTO;
        $this->assertEquals(json_encode(json_decode('{
            "value": 6
        }')), $d->toJson());
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
}
