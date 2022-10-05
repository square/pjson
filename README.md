A **simple** library for JSON to PHP Objects conversions

Often times, we interact with an API, or data source that returns JSON.
PHP only offers the possibility to deserialize that json into an array or objects of type `stdClass`.

This library helps deserializing JSON into actual objects of custom defined classes.
It does so by using PHP8's attributes on class properties.

## Examples

### Simple serialization of a class

```php
use Squareup\Pjson\Json;
use Squareup\Pjson\JsonSerialize;

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
use Squareup\Pjson\Json;
use Squareup\Pjson\JsonSerialize;

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

You can implement the `fromJsonArray(array $array) : static` on `CatalogObject` to discriminate based on the received data and return the correct serialization:

```php
abstract class CatalogObject
{
    use JsonSerialize;

    #[Json]
    protected $id;

    #[Json]
    protected string $type;

    public static function fromJsonArray(array $jd): static
    {
        $t = $jd['type'];

        return match ($t) {
            'category' => CatalogCategory::fromJsonArray($jd),
            'item' => CatalogItem::fromJsonArray($jd),
        };
    }
}
```
**WARNING:** Make sure that each of the subclasses directly `use JsonSerialize`. Otherwise when they call `::fromJsonArray`, they would call the parent on `CatalogObject`
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

If you're dealing with a list of things to deserialize, you can call `MyClass::listFromJsonString($json)` or `MyClass::listFromJsonArray($array)`. For example:

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
