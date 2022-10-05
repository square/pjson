<?php declare(strict_types=1);
require_once __DIR__.'/definitions.php';

use PHPUnit\Framework\TestCase;


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
}

