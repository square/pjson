<?php declare(strict_types=1);
namespace Square\Pjson\Tests\php81;

use PHPUnit\Framework\TestCase;
use Square\Pjson\Tests\Definitions\DTO;

final class Php81SerializationTest extends TestCase
{
    public function testReadOnly()
    {
        $d = new DTO;
        $this->assertEquals(json_encode(json_decode('{
            "value": 6
        }')), $d->toJson());
    }
}
