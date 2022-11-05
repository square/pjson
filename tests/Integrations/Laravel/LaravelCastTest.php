<?php declare(strict_types=1);
namespace Square\Pjson\Tests\Integrations\Laravel;

use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use Square\Pjson\Tests\Definitions\Integrations\Laravel\Address;
use Square\Pjson\Tests\Definitions\Integrations\Laravel\CastableAddress;
use Square\Pjson\Tests\Definitions\Integrations\Laravel\Models\FirstModel;

class LaravelCastTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Schema::create('first_model', function ($table) {
            $table->increments('id')->unsigned();
            $table->string('name')->nullable();
            $table->text('castable_address')->nullable();
            $table->text('address')->nullable();
        });
    }

    public function testCasts()
    {
        $addr = '{"line1":"678 Lombard St.","line2":"","city":"San Fransokyo","zipcode":"94959","country":"USA"}';
        (new FirstModel([
            'name' => 'jane',
            'castable_address' => CastableAddress::fromJsonString($addr),
            'address' => Address::fromJsonString($addr),
        ]))->save();
        $m = FirstModel::query()->first();
        $dbAddr = $m->getAttributes()['castable_address'];
        $this->assertEquals($addr, $dbAddr);
        $dbAddr = $m->getAttributes()['address'];
        $this->assertEquals($addr, $dbAddr);

        // Reading the attribute directly should give objects of the right class
        $caddr = $m->castable_address;
        $this->assertEquals(CastableAddress::class, get_class($caddr));
        $this->assertEquals($addr, $caddr->toJson());
        $oaddr = $m->address;
        $this->assertEquals(Address::class, get_class($oaddr));
        $this->assertEquals($addr, $oaddr->toJson());
    }
}
