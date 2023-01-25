<?php declare(strict_types=1);

namespace Square\Pjson\Tests\Definitions;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

class Collection implements IteratorAggregate
{
    public function __construct(protected array $items)
    {
    }

    public function all()
    {
        return $this->items;
    }

    public static function make($items)
    {
        return new self($items);
    }

    public function makeme($items)
    {
        $this->items = $items;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
