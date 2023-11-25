<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Result;

use Ang3\Component\Odoo\DBAL\Query\QueryInterface;

class ArrayResult implements ResultInterface
{
    protected QueryInterface $query;
    protected array $data = [];

    public function __construct(QueryInterface $query, array $data = [], protected array $context = [])
    {
        $this->query = clone $query;

        foreach ($data as $key => $value) {
            $this->offsetSet($key, $value);
        }

        $this->rewind();
    }

    public function __debugInfo(): ?array
    {
        return $this->data;
    }

    public function offsetExists(mixed $offset): bool
    {
        $offset = $this->assertOffset($offset);

        return \array_key_exists($offset, $this->data);
    }

    public function offsetGet(mixed $offset): mixed
    {
        $offset = $this->assertOffset($offset);

        return $this->data[$offset] ?? false;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $offset = $this->assertOffset($offset);
        $value = $this->assertValue($value);
        $this->data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        $offset = $this->assertOffset($offset);

        if ($this->offsetExists($offset)) {
            unset($this->data[$offset]);
        }
    }

    public function getIterator(): \Generator
    {
        yield from $this->fetchAll();
    }

    public function first(): mixed
    {
        $this->rewind();

        return $this->current();
    }

    public function current(): mixed
    {
        return current($this->data);
    }

    public function fetchAll(): \Traversable
    {
        $this->rewind();

        while (false !== ($value = $this->current())) {
            yield $this->offset() => $value;
            $this->fetch();
        }
    }

    public function fetch(): mixed
    {
        return next($this->data);
    }

    public function rewind(): void
    {
        reset($this->data);
    }

    public function last(): mixed
    {
        return end($this->data);
    }

    public function offset(): null|int|string
    {
        return key($this->data);
    }

    public function count(): int
    {
        return \count($this->data);
    }

    public function toArray(): array
    {
        return array_map(static fn ($result) => $result instanceof ResultInterface ? $result->toArray() : $result, iterator_to_array($this->fetchAll()));
    }

    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    public function getQuery(): QueryInterface
    {
        return $this->query;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @internal
     */
    protected function assertOffset(mixed $offset): int|string
    {
        if (!\is_int($offset) && !\is_string($offset)) {
            throw ResultException::invalidOffsetType($offset);
        }

        return $offset;
    }

    /**
     * @internal
     */
    protected function assertValue(mixed $value): mixed
    {
        return $value;
    }
}
