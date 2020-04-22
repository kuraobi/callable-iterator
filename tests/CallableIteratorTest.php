<?php

declare(strict_types=1);

namespace Kuraobi\CallableIterator\Tests;

use ArrayIterator;
use Iterator;
use IteratorAggregate;
use Kuraobi\CallableIterator\CallableIterator;
use Kuraobi\CallableIterator\PrecountedIteratorAggregate;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

class CallableIteratorTest extends TestCase
{
    public function testWithIteratorAggregate(): void
    {
        $values = [1, 2, 3, 4, 5];
        $iterators = array_map(fn(array $chunk): Iterator => new ArrayIterator($chunk), array_chunk($values, 3));
        $iterators[] = new ArrayIterator();
        array_unshift($iterators, new ArrayIterator());
        $iteratorAggregate = $this->createMock(PrecountedIteratorAggregate::class);
        $iteratorAggregate->method('getIterator')->willReturnOnConsecutiveCalls(...$iterators);
        $iteratorAggregate->method('count')->willReturn(count($values));
        $CallableIterator = new CallableIterator(fn(): IteratorAggregate => $iteratorAggregate);
        $pageChanges = 0;
        $CallableIterator->setOnPageChange(static function () use (&$pageChanges): void {
            $pageChanges++;
        });
        $i = null;
        $CallableIterator->rewind();
        Assert::assertCount(5, $CallableIterator);
        foreach ($CallableIterator as $i => $value) {
            $CallableIterator->setLastId($i);
            Assert::assertSame($i, $CallableIterator->getLastId());
            Assert::assertCount(5, $CallableIterator);
            Assert::assertSame($values[$i], $value);
        }
        Assert::assertCount(5, $CallableIterator);
        Assert::assertSame(count($values) - 1, $i);
        Assert::assertSame(2, $pageChanges);
    }

    public function testWithIterators(): void
    {
        $values = [1, 2, 3, 4, 5];
        $pageChanges = 0;
        $CallableIterator = new CallableIterator(static function ($lastId) use ($values): Iterator {
            if ($lastId === 0) {
                return new ArrayIterator($values);
            }
            if ($lastId === 4) {
                return new ArrayIterator();
            }
            throw new AssertionFailedError('Unexpected $lastId value: '.$lastId);
        });

        $CallableIterator->setOnPageChange(static function () use (&$pageChanges): void {
            $pageChanges++;
        });
        $i = null;
        $CallableIterator->rewind();
        Assert::assertCount(5, $CallableIterator);
        foreach ($CallableIterator as $i => $value) {
            $CallableIterator->setLastId($i);
            Assert::assertSame($i, $CallableIterator->getLastId());
            Assert::assertCount(5, $CallableIterator);
            Assert::assertSame($values[$i], $value);
            Assert::assertInstanceOf(ArrayIterator::class, $CallableIterator->getInnerIterator());
        }
        Assert::assertCount(0, $CallableIterator);
        Assert::assertSame(count($values) - 1, $i);
        Assert::assertSame(1, $pageChanges);
    }

    public function testWithArrays(): void
    {
        $values = [1, 2, 3, 4, 5];
        $pageChanges = 0;
        $CallableIterator = new CallableIterator(static function ($lastId) use ($values): array {
            if ($lastId === 0) {
                return $values;
            }
            if ($lastId === 4) {
                return [];
            }
            throw new AssertionFailedError('Unexpected $lastId value: '.$lastId);
        });

        $CallableIterator->setOnPageChange(static function () use (&$pageChanges): void {
            $pageChanges++;
        });
        $i = null;
        $CallableIterator->rewind();
        Assert::assertCount(5, $CallableIterator);
        foreach ($CallableIterator as $i => $value) {
            $CallableIterator->setLastId($i);
            Assert::assertSame($i, $CallableIterator->getLastId());
            Assert::assertCount(5, $CallableIterator);
            Assert::assertSame($values[$i], $value);
        }
        Assert::assertCount(0, $CallableIterator);
        Assert::assertSame(count($values) - 1, $i);
        Assert::assertSame(1, $pageChanges);
    }
}
