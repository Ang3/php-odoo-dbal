<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Query\Result;

use Ang3\Component\Odoo\DBAL\Query\QueryInterface;
use Ang3\Component\Odoo\DBAL\Query\Result\ArrayResult;
use Ang3\Component\Odoo\DBAL\Query\Result\ResultException;
use Ang3\Component\Odoo\DBAL\Query\Result\ResultInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Query\Result\ArrayResult
 *
 * @internal
 */
final class ArrayResultTest extends TestCase
{
    private MockObject $query;
    private ArrayResult $result;
    private array $data = [
        true,               // #0
        3,                  // #1
        3.14,               // #2
        'foo',              // #3
        ['bar' => 'baz'],   // #4
    ];
    private array $context = [
        ['qux' => 'lux'],
    ];

    private \ReflectionProperty $dataProperty;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->createMock(QueryInterface::class);
        $this->result = new ArrayResult($this->query, $this->data, $this->context);
        $this->dataProperty = new \ReflectionProperty($this->result, 'data');
    }

    /**
     * @covers \Ang3\Component\Odoo\DBAL\Query\Result\ArrayResult
     */
    public function testResultInterfaces(): void
    {
        $class = new \ReflectionClass(ArrayResult::class);

        static::assertTrue($class->implementsInterface(ResultInterface::class));
        static::assertTrue($class->implementsInterface(\IteratorAggregate::class));
        static::assertTrue($class->implementsInterface(\ArrayAccess::class));
        static::assertTrue($class->implementsInterface(\Countable::class));
    }

    /**
     * @covers \Ang3\Component\Odoo\DBAL\Query\Result\ArrayResult
     */
    public function testGetters(): void
    {
        static::assertInstanceOf(QueryInterface::class, $this->result->getQuery());
        static::assertSame($this->data, $this->dataProperty->getValue($this->result));
        static::assertSame($this->context, $this->result->getContext());
    }

    /**
     * @covers ::offsetExists
     *
     * @testWith [0, true]
     *           [1, true]
     *           [2, true]
     *           [3, true]
     *           [4, true]
     *           [5, false]
     *           ["0", true]
     *           ["1", true]
     *           ["2", true]
     *           ["3", true]
     *           ["4", true]
     *           ["5", false]
     */
    public function testOffsetExists(int|string $offset, bool $result): void
    {
        static::assertSame($result, $this->result->offsetExists($offset));
    }

    /**
     * @covers ::offsetExists
     *
     * @testWith [true]
     *           [1.1]
     */
    public function testOffsetExistsWithInvalidType(bool|float $offset): void
    {
        self::expectException(ResultException::class);
        $this->result->offsetExists($offset);
    }

    /**
     * @covers ::offsetGet
     *
     * @testWith [0, true]
     *           [1, 3]
     *           [2, 3.14]
     *           [3, "foo"]
     *           [4, {"bar": "baz"}]
     *           [5, false]
     *           ["0", true]
     *           ["1", 3]
     *           ["2", 3.14]
     *           ["3", "foo"]
     *           ["4", {"bar": "baz"}]
     *           ["5", false]
     */
    public function testOffsetGet(int|string $offset, mixed $result): void
    {
        static::assertSame($result, $this->result->offsetGet($offset));
    }

    /**
     * @covers ::offsetSet
     *
     * @depends testOffsetGet
     *
     * @testWith [0, false]
     *           [1, 6]
     *           [2, 6.28]
     *           [3, "bar"]
     *           [4, {"baz": "qux"}]
     *           ["0", false]
     *           ["1", 6]
     *           ["2", 6.28]
     *           ["3", "bar"]
     *           ["4", {"baz": "qux"}]
     */
    public function testOffsetSet(int|string $offset, mixed $value): void
    {
        $this->result->offsetSet($offset, $value);
        static::assertSame($value, $this->result->offsetGet($offset));
    }

    /**
     * @covers ::offsetSet
     *
     * @testWith [true]
     *           [1.1]
     */
    public function testOffsetSetWithInvalidType(bool|float $offset): void
    {
        self::expectException(ResultException::class);
        $this->result->offsetSet($offset, 'new_value');
    }

    /**
     * @covers ::offsetUnset
     *
     * @depends testOffsetGet
     *
     * @testWith [0, false]
     *           [5, false]
     *           ["0", false]
     *           ["5", false]
     */
    public function testOffsetUnset(int|string $offset): void
    {
        $this->result->offsetUnset($offset);
        static::assertFalse($this->result->offsetGet($offset));
    }

    /**
     * @covers ::current
     * @covers ::fetch
     * @covers ::first
     * @covers ::last
     * @covers ::offset
     * @covers ::rewind
     */
    public function testFetching(): void
    {
        static::assertSame($this->data[0], $this->result->current());
        static::assertSame(0, $this->result->offset());

        static::assertSame($this->data[1], $this->result->fetch());
        static::assertSame(1, $this->result->offset());

        static::assertSame($this->data[2], $this->result->fetch());
        static::assertSame(2, $this->result->offset());

        static::assertSame($this->data[3], $this->result->fetch());
        static::assertSame(3, $this->result->offset());

        static::assertSame($this->data[4], $this->result->fetch());
        static::assertSame(4, $this->result->offset());

        static::assertFalse($this->result->fetch());
        static::assertNull($this->result->offset());

        static::assertSame($this->data[0], $this->result->first());
        static::assertSame(0, $this->result->offset());

        static::assertSame($this->data[4], $this->result->last());
        static::assertSame(4, $this->result->offset());

        static::assertFalse($this->result->fetch());
        static::assertNull($this->result->offset());

        $this->result->rewind();
        static::assertSame(0, $this->result->offset());
    }

    /**
     * @covers ::fetchAll
     *
     * @depends testFetching
     */
    public function testFetchAll(): void
    {
        $currentOffset = 0;

        // We fetch to be sure the current data key is not at the beginning
        // By this way, we ensure that the cursor has been rewound.
        $this->result->fetch();

        foreach ($this->result->fetchAll() as $key => $value) {
            static::assertSame($key, $currentOffset);
            static::assertSame($this->data[$currentOffset], $value);
            ++$currentOffset;
        }

        static::assertSame(5, $currentOffset);
    }

    /**
     * @covers ::count
     */
    public function testCount(): void
    {
        static::assertSame(\count($this->data), $this->result->count());
    }

    /**
     * @covers ::toArray
     */
    public function testToArray(): void
    {
        static::assertSame($this->data, $this->result->toArray());
    }

    /**
     * @covers ::isEmpty
     *
     * @depends testOffsetSet
     */
    public function testIsEmpty(): void
    {
        static::assertFalse($this->result->isEmpty());

        $this->dataProperty->setValue($this->result, []);
        static::assertTrue($this->result->isEmpty());

        $this->result->offsetSet(0, 1);
        static::assertFalse($this->result->isEmpty());
    }
}
