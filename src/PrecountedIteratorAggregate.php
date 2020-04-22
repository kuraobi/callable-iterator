<?php

declare(strict_types=1);

namespace Kuraobi\CallableIterator;

use Countable;
use Iterator;
use IteratorAggregate;

/**
 * @template   T
 * @implements IteratorAggregate<T>
 */
class PrecountedIteratorAggregate implements IteratorAggregate, Countable
{
    /** @var Iterator<T> */
    protected Iterator $iterator;

    protected int $count = 0;

    /** @param Iterator<T> $iterator */
    public function __construct(Iterator $iterator, int $count)
    {
        $this->iterator = $iterator;
        $this->count = $count;
    }

    /** @return Iterator<T> */
    public function getIterator(): Iterator
    {
        return $this->iterator;
    }

    public function count(): int
    {
        return $this->count;
    }
}
