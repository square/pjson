A **simple** library for JSON to PHP Objects conversions

Often times, we interact with an API, or data source that returns JSON.
PHP only offers the possibility to deserialize that json into an array or objects of type `stdClass`.

This library helps deserializing JSON into actual objects of custom defined classes.
It does so by using PHP8's attributes on class properties.

## Examples

### Simple serialization of a class

```php
use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

class Schedule
{
    use JsonSerialize;

    #[Json]
    protected int $start;

    #[Json]
    protected int $end;

    public function __construct(int $start, int $end)
    {
        $this->start = $start;
        $this->end = $end;
    }
}

(new Schedule(1, 2))->toJson();
```
Would yield the following:

```json
{
    "start": 1,
    "end": 2
}
```
And then the reverse can be achieved via:

```php
Schedule::fromJsonString('{"start":1,"end":2}');
```

Which would return an instance of class `Schedule` with the properties set according to the JSON.

### Custom Names

The previous example can be made to use custom names in JSON instead of just the property name:

```php
use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

class Schedule
{
    use JsonSerialize;

    #[Json('begin')]
    protected int $start;

    #[Json('stop')]
    protected int $end;

    public function __construct(int $start, int $end)
    {
        $this->start = $start;
        $this->end = $end;
    }
}

(new Schedule(1, 2))->toJson();
```
would yield
```json
{
    "begin": 1,
    "stop": 2
}
```

And deserializing with those new property names works just as before:
```php
dump(Schedule::fromJsonString('{"begin":1,"stop":2}'));

// ^ Schedule^ {#345
//   #start: 1
//   #end: 2
// }
```
### Private / Protected

The visibility of a property does not matter. A private or protected property can be serialized / unserialized as well (see previous examples).

### Property Path

Sometimes the json format isn't exactly the PHP version we want to use. Say for example that the JSON we received for the previous schedule examples
were to look like:

```json
{
    "data": {
        "start": 1,
        "end": 2
    }
}
```
By declaring our class json attributes as follows, we can still read those properties direclty into our class:

```php
class Schedule
{
    use JsonSerialize;

    #[Json(['data', 'start'])]
    protected int $start;

    #[Json(['data', 'end'])]
    protected int $end;

    public function __construct(int $start, int $end)
    {
        $this->start = $start;
        $this->end = $end;
    }
}
```

### Recursive serialize / deserialize

If we are working with a json structure that's a bit more complex, we will want to have properties be classes that can also be properly deserialized into.

```json
{
    "saturday": {
        "start": 0,
        "end": 2
    },
    "sunday": {
        "start": 0,
        "end": 7
    }
}
```

The following 2 PHP classes could work well with this:

```php
class Schedule
{
    use JsonSerialize;

    #[Json]
    protected int $start;

    #[Json]
    protected int $end;
}

class Weekend
{
    use JsonSerialize;

    #[Json('saturday')]
    protected Schedule $sat;
    #[Json('sunday')]
    protected Schedule $sun;
}
```

### Arrays

When working with an array of items where each item should be of a given class, we need to tell pjson about the target type:

```json
{
    "days": [
        {
            "start": 0,
            "end": 2
        },
        {
            "start": 0,
            "end": 2
        },
        {
            "start": 0,
            "end": 2
        },
        {
            "start": 0,
            "end": 7
        }
    ]
}
```

With Schedule still defined as before, we'd define a week like:

```php
class Week
{
    use JsonSerialize;

    #[Json(type: Schedule::class)]
    protected array $days;
}
```

This would also work with a map if the json were like:

```json
{
    "days": {
        "monday": {
            "start": 0,
            "end": 2
        },
        "wednesday": {
            "start": 0,
            "end": 2
        }
    }
}
```

And the resulting PHP object would be:

```
Week^ {#353
  #days: array:2 [
    "monday" => Schedule^ {#344
      #start: 0
      #end: 2
    }
    "wednesday" => Schedule^ {#343
      #start: 0
      #end: 2
    }
  ]
}
```

### Collection Classes

Similar to arrays, you might want to use collection classes. You can do so as long as your classes implement the `Traversable` interface.
In this case, pjson will by default try to construct your class by passing in the array of data in the constructor.
If this doesn't work for you, you can specify a custom factory method for your collections:

```php
class Collector
{
    use JsonSerialize;

    #[Json(type: Schedule::class)]
    public Collection $schedules;

    #[Json(type: Schedule::class, collection_factory_method: 'make')]
    public Collection $static_factoried_schedules;

    #[Json(type: Schedule::class, collection_factory_method: 'makeme')]
    public Collection $factoried_schedules;
}
```

Here our collection has a static factory method `make` and an instance method `makeme` that could each be used. The constructor option also works.
You can look at the collection class in the `tests/Definitions` directory.

This would allow you to work with json like:

```json
{
    "schedules": [
        {
            "schedule_start": 1,
            "schedule_end": 2
        }
    ],
    "factoried_schedules": [
        {
            "schedule_start": 10,
            "schedule_end": 20
        }
    ],
    "static_factoried_schedules": [
        {
            "schedule_start": 100,
            "schedule_end": 200
        }
    ]
}
```

### Polymorphic deserialization

Say you have 2 classes that extend a base class. You might receive those as part of a collection and don't know ahead of time if you'll be dealing with
one or the other. For example:

```php
abstract class CatalogObject
{
    use JsonSerialize;

    #[Json]
    protected $id;

    #[Json]
    protected string $type;
}

class CatalogCategory extends CatalogObject
{
    use JsonSerialize;

    #[Json('parent_category_id')]
    protected string $parentCategoryId;
}

class CatalogItem extends CatalogObject
{
    use JsonSerialize;

    #[Json]
    protected string $name;
}
```

You can implement the `fromJsonData(array $array) : static` on `CatalogObject` to discriminate based on the received data and return the correct serialization:

```php
abstract class CatalogObject
{
    use JsonSerialize;

    #[Json]
    protected $id;

    #[Json]
    protected string $type;

    public static function fromJsonData($jd): static
    {
        $t = $jd['type'];

        return match ($t) {
            'category' => CatalogCategory::fromJsonData($jd),
            'item' => CatalogItem::fromJsonData($jd),
        };
    }
}
```
**WARNING:** Make sure that each of the subclasses directly `use JsonSerialize`. Otherwise when they call `::fromJsonData`, they would call the parent on `CatalogObject`
leading to infinite recursion.

With this in place, we can do:

```php
$jsonCat = '{"type": "category", "id": "123", "parent_category_id": "456"}';
$c = CatalogObject::fromJsonString($jsonCat);
$this->assertEquals(CatalogCategory::class, get_class($c));

$jsonItem = '{"type": "item", "id": "123", "name": "Sandals"}';
$c = CatalogObject::fromJsonString($jsonItem);
$this->assertEquals(CatalogItem::class, get_class($c));
```

### Lists

If you're dealing with a list of things to deserialize, you can call `MyClass::listFromJsonString($json)` or `MyClass::listfromJsonData($array)`. For example:

```php
Schedule::listFromJsonString('[
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
```

yields the same as

```php
[
    new Schedule(1, 2),
    new Schedule(11, 22),
    new Schedule(111, 222),
];
```

### Initial path

Somteimes the JSON you care about will be nested under a property but you don't want / need to model the outer layer. For this you can pass a `$path` to the deserializing methods:

```php
Schedule::fromJsonString('{
    "data": {
        "schedule_start": 1,
        "schedule_end": 2
    }
}', path: 'data');

Schedule::fromJsonString('{
    "data": {
        "main": {
            "schedule_start": 1,
            "schedule_end": 2
        }
    }
}', path: ['data', 'main']);
```

### Merging data

Sometimes it is useful to merge some data into the same JSON object while still keeping them as separate PHP objects:

```php
class Decorator
{
    use JsonSerialize;
    
    #[Json(path: [])]
    public Data $decoratedData;

    #[Json]
    public string $additional;
}

class Data
{
    use JsonSerialize;
    
    #[Json]
    public string $value;
}

$data = new Decorator(new Data('myValue'), 'andMore');
$data->toJson(); // {"value": "myValue", "additional": "andMore"}
```

This works for both serializing and deserializing.

### Enums

Backed enums are supported out of the box in PHP 8.1

```php
class Widget
{
    use JsonSerialize;

    #[Json]
    public Status $status;
}

enum Status : string
{
    case ON = 'ON';
    case OFF = 'OFF';
}
$w = new Widget;
$w->status = Status::ON;

$w->toJson(); // {"status": "ON"}
```

And regular enums can be supported via the `JsonSerialize` trait or the `JsonDataSerializable` interface

```php
class Widget
{
    use JsonSerialize;

    #[Json]
    public Size $size;
}

enum Size
{
    use JsonSerialize;

    case BIG;
    case SMALL;

    public static function fromJsonData($d, array|string $path = []): static
    {
        return match ($d) {
            'BIG' => self::BIG,
            'SMALL' => self::SMALL,
            'big' => self::BIG,
            'small' => self::SMALL,
        };
    }

    public function toJsonData()
    {
        return strtolower($this->name);
    }
}

$w = new Widget;
$w->size = Size::BIG;

$w->toJson(); // {"status": "big"}
```

### Required properties
You can mark a property as required for deserialization:
```php
readonly class Token
{
    use JsonSerialize;

    #[Json(required: true)]
    public string $key;
}

$token = Token::fromJsonString('{"key":"data"}'); // successful

Token::fromJsonString('{"other":"has no key"}'); // throws Exception
```

### Scalar <=> Class
In some cases, you might want a scalar value to become a PHP object once deserialized and vice-versa. For example, a `BigInt` class
could hold an int as a string and represent it as a string when serialized to JSON:

```php
class Stats
{
    use JsonSerialize;

    #[Json]
    public BigInt $count;
}

class BigInt implements JsonDataSerializable
{
    public function __construct(
        protected string $value,
    ) {
    }

    public static function fromJsonData($jd, array|string $path = []) : static
    {
        return new BigInt($jd);
    }

    public function toJsonData()
    {
        return $this->value;
    }
}

$stats = new Stats;
$stats->count = new BigInt("123456789876543234567898765432345678976543234567876543212345678765432");
$stats->toJson(); // {"count":"123456789876543234567898765432345678976543234567876543212345678765432"}
```

### Collection Classes

If you wish to use pjson with collection classes

## Use with PHPStan
Using this library, you may have properties that don't appear to be read from or written to anywhere in your code, but
are purely used for JSON serialization. PHPStan will complain about these issues, but you can help PHPStan understand
that this is expected behavior by adding this library's extension in your `phpstan.neon`.

```neon
includes:
  - vendor/square/pjson/extension.neon
```

## Laravel Integration

### via castable

If you wish to cast Eloquent model attributes to classes via Pjson, you might do so with the provided casting utilities:

```php
use Illuminate\Contracts\Database\Eloquent\Castable;
use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;
use Square\Pjson\Integrations\Laravel\JsonCastable;

class Schedule implements Castable // implement the laravel interface
{
    use JsonSerialize;
    use JsonCastable; // use the provided Pjson trait

    #[Json]
    protected int $start;

    #[Json]
    protected int $end;

    public function __construct(int $start, int $end)
    {
        $this->start = $start;
        $this->end = $end;
    }
}
```

Then in your Eloquent model:

```php
$casts = [
    'schedule' => Schedule::class,
];
```

### via cast arguments

Alternatively, you can simply use Laravel's cast arguments. In this case the `Schedule` class stays the way it used to be:

```php
use Square\Pjson\Json;
use Square\Pjson\JsonSerialize;

class Schedule
{
    use JsonSerialize;

    #[Json]
    protected int $start;

    #[Json]
    protected int $end;

    public function __construct(int $start, int $end)
    {
        $this->start = $start;
        $this->end = $end;
    }
}
```

And you provide the class target of the cast like:

```php
$casts = [
    'schedule' => JsonCaster::class.':'.Schedule::class,
];
```
