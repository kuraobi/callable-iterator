<?php

declare(strict_types=1);

namespace Kuraobi\CallableIterator;

use Countable;
use Iterator;
use IteratorAggregate;
use OuterIterator;
use UnexpectedValueException;

/**
 * @template T
 */
class CallableIterator implements Countable, OuterIterator
{
    /** @var callable */
    private $onPageChange;
    private int $lastId = 0;
    private int $key = 0;
    /** @var callable(int): iterable<T> */
    private $callable;
    private int $totalCount = 0;
    /** @var Iterator<T>|null */
    private ?Iterator $iterator = null;

    /** @param callable(int): iterable<T> $getIterable */
    public function __construct(callable $getIterable)
    {
        $this->callable = $getIterable;
    }

    public function next(): void
    {
        if (null !== $this->iterator) {
            $this->iterator->next();
        }
        ++$this->key;
    }

    public function count(): int
    {
        return $this->totalCount;
    }

    /** @return T|null */
    public function current()
    {
        if (null === $this->iterator) {
            return null;
        }

        return $this->iterator->current();
    }

    public function key(): int
    {
        return $this->key;
    }

    public function valid(): bool
    {
        if (null === $this->iterator) {
            return false;
        }
        if ($this->iterator->valid()) {
            return true;
        }
        if (null !== $this->onPageChange) {
            ($this->onPageChange)();
        }
        $this->nextIterator();

        return null !== $this->iterator && $this->iterator->valid();
    }

    public function rewind(): void
    {
        $this->lastId = 0;
        $this->key = 0;
        $this->nextIterator();
    }

    public function setLastId(int $lastId): void
    {
        $this->lastId = $lastId;
    }

    public function setOnPageChange(callable $onPageChange): void
    {
        $this->onPageChange = $onPageChange;
    }

    public function getLastId(): int
    {
        return $this->lastId;
    }

    /** @return Iterator<T> */
    public function getInnerIterator(): Iterator
    {
        if (null === $this->iterator) {
            throw new UnexpectedValueException('No iterator set');
        }

        return $this->iterator;
    }

    private function nextIterator(): void
    {
        $iterator = ($this->callable)($this->lastId);
        $count = 0;
        if (is_countable($iterator)) {
            $count = count($iterator);
        }
        if ($iterator instanceof IteratorAggregate) {
            $iterator = $iterator->getIterator();
        }
        if (!$iterator instanceof Iterator) {
            if (!is_iterable($iterator)) {
                throw new UnexpectedValueException('The value is not iterable');
            }
            $iterable = $iterator;
            $iterator = (static function () use ($iterable): Iterator {
                yield from $iterable;
            })();
        }
        if (0 === $count && is_countable($iterator)) {
            /** @var Countable&Iterator<T> $iterator */
            $count = count($iterator);
        }
        $this->totalCount = $count;
        $this->iterator = $iterator;
    }
}
