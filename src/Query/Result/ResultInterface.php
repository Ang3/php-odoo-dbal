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

/**
 * This interface represents a data result.
 * A result is an array of values.
 */
interface ResultInterface extends \IteratorAggregate, \ArrayAccess, \Countable
{
    /**
     * Gets the first element, or FALSE if there is no value.
     *
     * @throws ResultException
     */
    public function first(): mixed;

    /**
     * Gets the current value.
     *
     * @throws ResultException
     */
    public function current(): mixed;

    /**
     * Returns the next value in the result or FALSE if there are no more values.
     *
     * @throws ResultException
     */
    public function fetch(): mixed;

    /**
     * Iterates on all values, and returns FALSE if there is no more values.
     *
     * @throws ResultException
     */
    public function fetchAll(): iterable;

    /**
     * Rewinds fetching by resetting the cursor at the beginning of the array.
     *
     * @throws ResultException
     */
    public function rewind(): void;

    /**
     * Gets the last value.
     *
     * @throws ResultException
     */
    public function last(): mixed;

    /**
     * Gets the index of the next value to fetch.
     * It returns NULL if there is no more offset.
     *
     * @return int|string|null The current value offset
     */
    public function offset(): int|string|null;

    /**
     * Returns the number of values in the result.
     * For query of type INSERT or UPDATE, it returns the number of records affected.
     *
     * @return int The number of rows in the result. If the columns cannot be counted,
     *             this method must return 0.
     *
     * @throws ResultException
     */
    public function count(): int;

    /**
     * Returns ALL the result as array of values.
     *
     * @throws ResultException
     */
    public function toArray(): array;

    /**
     * Returns TRUE if the result is empty.
     */
    public function isEmpty(): bool;

    /**
     * Gets the related query of the result.
     */
    public function getQuery(): QueryInterface;

    /**
     * Gets the context of the result.
     */
    public function getContext(): array;
}
