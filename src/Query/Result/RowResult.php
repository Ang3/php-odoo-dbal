<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Result;

/**
 * This class represents an row result: an array of rows.
 * Basically, it's an array result where values are only arrays.
 *
 * @method \Generator|array[] getIterator()
 * @method array|false        offsetGet(mixed $offset)
 * @method array|false        first()
 * @method array|false        current()
 * @method array|false        fetch()
 * @method \Generator|array[] fetchAll()
 * @method array|false        last()
 * @method array[]            toArray()
 */
class RowResult extends ArrayResult
{
    /**
     * The number of column(s) in a row.
     * This value is computed on the first inserted row.
     */
    private int $columnCount = 0;

    /**
     * @param array $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        parent::offsetSet($offset, $value);
        $this->columnCount = $this->columnCount ?: \count($value);
    }

    /**
     * Gets the scalar values from the column of each row as a scalar result.
     * If $column is NULL, then the first column will be used.
     *
     * @param int|string|null $column The column name of the value
     *
     * @throws ResultException
     */
    public function scalars(int|string|null $column = null): ScalarResult
    {
        return new ScalarResult($this->query, $this->values($column), $this->context);
    }

    /**
     * Gets the array values from the column of each row as an array result.
     * If $column is NULL, then the first column will be used.
     *
     * @param int|string|null $column The column name of the value
     *
     * @throws ResultException
     */
    public function arrays(int|string|null $column = null): ArrayResult
    {
        return new ArrayResult($this->query, $this->values($column), $this->context);
    }

    /**
     * Gets the array values from the column of each row as a row result.
     * If $column is NULL, then the first column will be used.
     *
     * @param int|string|null $column The column name of the value
     *
     * @throws ResultException
     */
    public function rows(int|string|null $column = null): self
    {
        return new self($this->query, $this->values($column), $this->context);
    }

    /**
     * Gets the values from the column of each rows.
     * If $column is NULL, then the first column will be used.
     *
     * @param int|string|null $column The column name of the value
     *
     * @throws ResultException
     */
    public function values(int|string|null $column = null): array
    {
        return iterator_to_array($this->fetchColumn($column));
    }

    /**
     * Fetches a column value for each row.
     *
     * @return \Generator|mixed[]
     *
     * @throws ResultException
     */
    public function fetchColumn(int|string|null $column = null): \Generator
    {
        foreach ($this->fetchAll() as $offset => $value) {
            $value = $this->assertValue($value);

            if (null === $column) {
                return array_shift($value);
            }

            $offset = $this->assertOffset($offset);

            if (!\array_key_exists($column, $value)) {
                throw ResultException::columnNotFound($column, $offset);
            }

            yield $offset => $value[$column];
        }
    }

    /**
     * Gets the next row as numeric array.
     *
     * @throws ResultException
     */
    public function fetchNumeric(): array|false
    {
        $value = $this->fetch();

        return false !== $value ? array_values($value) : false;
    }

    /**
     * Gets the next row as associative array.
     *
     * @throws ResultException
     */
    public function fetchAssociative(): array|false
    {
        return $this->fetch();
    }

    /**
     * @return \Generator|array[]
     *
     * @throws ResultException
     */
    public function fetchAllNumeric(): \Generator
    {
        foreach ($this->fetchAll() as $key => $value) {
            yield $key => array_values((array) $value);
        }
    }

    /**
     * @return \Generator|array[]
     *
     * @throws ResultException
     */
    public function fetchAllAssociative(): \Generator
    {
        return yield from $this->fetchAll();
    }

    /**
     * Returns the number of columns in each row.
     *
     * @throws ResultException
     */
    public function columnCount(): int
    {
        return $this->columnCount;
    }

    /**
     * @internal
     */
    protected function assertValue(mixed $value): array
    {
        if (!\is_array($value)) {
            throw new \InvalidArgumentException(sprintf('Expected array value, got "%s".', get_debug_type($value)));
        }

        return $value;
    }
}
