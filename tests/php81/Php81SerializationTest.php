<?php declare(strict_types=1);
namespace Squareup\Pjson\Tests\php81;

use PHPUnit\Framework\TestCase;
use Squareup\Pjson\Tests\Definitions\DTO;

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
