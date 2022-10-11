<?php declare(strict_types=1);
namespace Square\Pjson\Tests\php81;

use PHPUnit\Framework\TestCase;
use Square\Pjson\Tests\Definitions\DTO;
use Square\Pjson\Tests\Definitions\Size;
use Square\Pjson\Tests\Definitions\Status;
use Square\Pjson\Tests\Definitions\Widget;

final class Php81SerializationTest extends TestCase
{
    public function testReadOnly()
    {
        $d = new DTO;
        $this->assertEquals(json_encode(json_decode('{
            "value": 6
        }')), $d->toJson());
    }

    public function testBackedEnum()
    {
        $w = new Widget;
        $w->status = Status::ON;
        $this->assertEquals(json_encode(json_decode('{
            "status": "ON"
        }')), $w->toJson());
    }

    public function testEnum()
    {
        $w = new Widget;
        $w->status = Status::ON;
        $w->size = Size::BIG;
        $this->assertEquals(json_encode(json_decode('{
            "status": "ON",
            "size": "big"
        }')), $w->toJson());
    }
}
