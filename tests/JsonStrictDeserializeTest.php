<?php

declare(strict_types=1);

namespace Square\Pjson\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Square\Pjson\Exceptions\MissingRequiredPropertyException;
use Square\Pjson\Tests\Definitions\Schedule;
use Square\Pjson\Tests\Definitions\Store;
use TypeError;


final class JsonStrictDeserializeTest extends TestCase
{

    const UNINITIALIZED = '@uninitialized';

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
            $v = $prop->isInitialized($value) ? $prop->getValue($value) : self::UNINITIALIZED;
            $n = $prop->getName();

            $data[$n] = $this->export($v);
        }

        return $data;
    }

    public function testDeserializesSimpleClass()
    {
        $store = Store::fromJsonString('{
            "identifier": "id123",
            "store_name": "Bobs Burgers",
            "store_hours": [
                {
                    "schedule_start": 1,
                    "schedule_end": 8
                },
                {
                    "schedule_start": 12,
                    "schedule_end": 20
                }
            ],
            "next_open_hours": {
                "schedule_start": 1,
                "schedule_end": 8
            },
            "owner": {
                "email": "owner@store.com"
            }
        }');

        $this->assertEquals([
            '@class' => Store::class,
            'id' => 'id123',
            'name' => 'Bobs Burgers',
            'nickname' => null,
            'catalog' => null,
            'storeHours' => [[
                '@class' => Schedule::class,
                'start' => 1,
                'end' => 8,
            ], [
                '@class' => Schedule::class,
                'start' => 12,
                'end' => 20,
            ]],
            'nextOpenHours' => [
                '@class' => Schedule::class,
                'start' => 1,
                'end' => 8,
            ],
            'ownerEmail' => 'owner@store.com',
            'ownerPhone' => null,
        ], $this->export($store));
    }

    public function testRequiredAttribute()
    {
        $this->expectException(MissingRequiredPropertyException::class);
        // missing 'identifier' field
        Store::fromJsonString('{
            "store_name": "Bobs Burgers",
            "store_hours": [
                {
                    "schedule_start": 1,
                    "schedule_end": 8
                },
                {
                    "schedule_start": 12,
                    "schedule_end": 20
                }
            ],
            "next_open_hours": {
                "schedule_start": 1,
                "schedule_end": 8
            },
            "owner": {
                "email": "owner@store.com"
            }
        }');
    }

    public function testNullScalarField()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("Property 'name' is non-nullable");
        // null 'store_name' field
        Store::fromJsonString('{
            "identifier": "id123",
            "store_name": null,
            "store_hours": [
                {
                    "schedule_start": 1,
                    "schedule_end": 8
                },
                {
                    "schedule_start": 12,
                    "schedule_end": 20
                }
            ],
            "next_open_hours": {
                "schedule_start": 1,
                "schedule_end": 8
            },
            "owner": {
                "email": "owner@store.com"
            }
        }');
    }
    
    public function testMissingScalarField()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("Property 'name' is non-nullable");
        // missing 'store_name' field
        Store::fromJsonString('{
            "identifier": "id123",
            "store_hours": [
                {
                    "schedule_start": 1,
                    "schedule_end": 8
                },
                {
                    "schedule_start": 12,
                    "schedule_end": 20
                }
            ],
            "next_open_hours": {
                "schedule_start": 1,
                "schedule_end": 8
            },
            "owner": {
                "email": "owner@store.com"
            }
        }');
    }

    public function testNullArrayField()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("Property 'storeHours' is non-nullable");
        // null 'store_hours' field
        Store::fromJsonString('{
            "identifier": "id123",
            "store_name": "Bobs Burgers",
            "store_hours": null,
            "next_open_hours": {
                "schedule_start": 1,
                "schedule_end": 8
            },
            "owner": {
                "email": "owner@store.com"
            }
        }');
    }

    public function testMissingArrayField()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("Property 'storeHours' is non-nullable");
        // missing 'store_hours' field
        Store::fromJsonString('{
            "identifier": "id123",
            "store_name": "Bobs Burgers",
            "next_open_hours": {
                "schedule_start": 1,
                "schedule_end": 8
            },
            "owner": {
                "email": "owner@store.com"
            }
        }');
    }

    public function testNullDeserializableObjectField()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("Property 'nextOpenHours' is non-nullable");
        // null 'next_open_hours' field
        Store::fromJsonString('{
            "identifier": "id123",
            "store_name": "Bobs Burgers",
            "store_hours": [
                {
                    "schedule_start": 1,
                    "schedule_end": 8
                },
                {
                    "schedule_start": 12,
                    "schedule_end": 20
                }
            ],
            "next_open_hours": null,
            "owner": {
                "email": "owner@store.com"
            }
        }');
    }

    public function testMissingDeserializableObjectField()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("Property 'nextOpenHours' is non-nullable");
        // missing 'next_open_hours' field
        Store::fromJsonString('{
            "identifier": "id123",
            "store_name": "Bobs Burgers",
            "store_hours": [
                {
                    "schedule_start": 1,
                    "schedule_end": 8
                },
                {
                    "schedule_start": 12,
                    "schedule_end": 20
                }
            ],
            "owner": {
                "email": "owner@store.com"
            }
        }');
    }

    public function testNullMultiplePathBitsField1()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("Property 'ownerEmail' is non-nullable");
        // null 'owner.email' field
        Store::fromJsonString('{
            "identifier": "id123",
            "store_name": "Bobs Burgers",
            "store_hours": [
                {
                    "schedule_start": 1,
                    "schedule_end": 8
                },
                {
                    "schedule_start": 12,
                    "schedule_end": 20
                }
            ],
            "next_open_hours": {
                "schedule_start": 1,
                "schedule_end": 8
            },
            "owner": {
                "email": null
            }
        }');
    }

    public function testMissingMultiplePathBitsField1()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("Property 'ownerEmail' is non-nullable");
        // missing 'owner.email' field
        Store::fromJsonString('{
            "identifier": "id123",
            "store_name": "Bobs Burgers",
            "store_hours": [
                {
                    "schedule_start": 1,
                    "schedule_end": 8
                },
                {
                    "schedule_start": 12,
                    "schedule_end": 20
                }
            ],
            "next_open_hours": {
                "schedule_start": 1,
                "schedule_end": 8
            },
            "owner": {}
        }');
    }

    public function testNullMultiplePathBitsField2()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("Property 'ownerEmail' is non-nullable");
        // null 'owner' field
        Store::fromJsonString('{
            "identifier": "id123",
            "store_name": "Bobs Burgers",
            "store_hours": [
                {
                    "schedule_start": 1,
                    "schedule_end": 8
                },
                {
                    "schedule_start": 12,
                    "schedule_end": 20
                }
            ],
            "next_open_hours": {
                "schedule_start": 1,
                "schedule_end": 8
            },
            "owner": null
        }');
    }

    public function testMissingMultiplePathBitsField2()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("Property 'ownerEmail' is non-nullable");
        // missing 'owner' field
        Store::fromJsonString('{
            "identifier": "id123",
            "store_name": "Bobs Burgers",
            "store_hours": [
                {
                    "schedule_start": 1,
                    "schedule_end": 8
                },
                {
                    "schedule_start": 12,
                    "schedule_end": 20
                }
            ],
            "next_open_hours": {
                "schedule_start": 1,
                "schedule_end": 8
            }
        }');
    }

    public function testStrictModeAppliesToNestedObjects_nullField()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("Property 'start' is non-nullable");
        // null 'next_open_hours.schedule_start' field
        Store::fromJsonString('{
            "identifier": "id123",
            "store_name": "Bobs Burgers",
            "store_hours": [
                {
                    "schedule_start": 1,
                    "schedule_end": 8
                },
                {
                    "schedule_start": 12,
                    "schedule_end": 20
                }
            ],
            "next_open_hours": {
                "schedule_start": null,
                "schedule_end": 8
            },
            "owner": {
                "email": "owner@store.com"
            }
        }');
    }

    public function testStrictModeAppliesToNestedObjects_missingField()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("Property 'start' is non-nullable");
        // null 'next_open_hours.schedule_start' field
        Store::fromJsonString('{
            "identifier": "id123",
            "store_name": "Bobs Burgers",
            "store_hours": [
                {
                    "schedule_start": 1,
                    "schedule_end": 8
                },
                {
                    "schedule_start": 12,
                    "schedule_end": 20
                }
            ],
            "next_open_hours": {
                "schedule_end": 8
            },
            "owner": {
                "email": "owner@store.com"
            }
        }');
    }

    public function testStrictModeAppliesToNestedArrayElements_nullField()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("Property 'start' is non-nullable");
        // null 'next_open_hours.schedule_start' field
        Store::fromJsonString('{
            "identifier": "id123",
            "store_name": "Bobs Burgers",
            "store_hours": [
                {
                    "schedule_start": null,
                    "schedule_end": 8
                },
                {
                    "schedule_start": 12,
                    "schedule_end": 20
                }
            ],
            "next_open_hours": {
                "schedule_start": 1,
                "schedule_end": 8
            },
            "owner": {
                "email": "owner@store.com"
            }
        }');
    }

    public function testStrictModeAppliesToNestedArrayElements_missingField()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("Property 'start' is non-nullable");
        // null 'next_open_hours.schedule_start' field
        Store::fromJsonString('{
            "identifier": "id123",
            "store_name": "Bobs Burgers",
            "store_hours": [
                {
                    "schedule_end": 8
                },
                {
                    "schedule_start": 12,
                    "schedule_end": 20
                }
            ],
            "next_open_hours": {
                "schedule_start": 1,
                "schedule_end": 8
            },
            "owner": {
                "email": "owner@store.com"
            }
        }');
    }
    
}
