<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query;

use Ang3\Component\Odoo\DBAL\Query\Result\NoResultException;
use Ang3\Component\Odoo\DBAL\Query\Result\NoUniqueResultException;
use Ang3\Component\Odoo\DBAL\Query\Result\Paginator;
use Ang3\Component\Odoo\DBAL\Query\Result\ResultException;
use Ang3\Component\Odoo\DBAL\Query\Result\ResultInterface;
use Ang3\Component\Odoo\DBAL\Query\Result\RowResult;
use Ang3\Component\Odoo\DBAL\Query\Result\ScalarResult;
use Ang3\Component\Odoo\DBAL\RecordManagerInterface;

interface QueryInterface
{
    /**
     * Gets the related model name of the query.
     */
    public function getName(): string;

    /**
     * Gets the query method to use.
     */
    public function getMethod(): string;

    /**
     * Gets query parameters.
     */
    public function getParameters(): array;

    /**
     * Gets query options.
     */
    public function getOptions(): array;

    /**
     * Gets the value of an option.
     */
    public function getOption(string $name): mixed;

    /**
     * Checks if an option is defined.
     */
    public function hasOption(string $name): bool;

    /**
     * Executes the query and returns result.
     * Allowed methods: all.
     */
    public function execute(): mixed;

    /**
     * Counts the number of records from parameters.
     *
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @throws QueryException on invalid query method
     */
    public function count(): int;

    /**
     * Gets just ONE scalar result from the FIRST row result.
     *
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @param array $context The context for results
     *
     * @throws NoUniqueResultException on no unique result
     * @throws NoResultException       on no result
     * @throws QueryException          on invalid query method
     */
    public function getSingleScalarResult(array $context = []): bool|float|int|string;

    /**
     * Gets just ONE scalar result from the FIRST row result, or NULL if no result.
     *
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @param array $context The context for results
     *
     * @throws NoUniqueResultException on no unique result
     * @throws NoResultException       on no result
     * @throws QueryException          on invalid query method
     */
    public function getOneOrNullScalarResult(array $context = []): null|bool|float|int|string;

    /**
     * Gets the FIRST row.
     *
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @param array $context The context for results
     *
     * @throws NoUniqueResultException on no unique result
     * @throws NoResultException       on no result
     * @throws QueryException          on invalid query method
     */
    public function getSingleResult(array $context = []): array;

    /**
     * Gets the FIRST row, or NULL if empty.
     *
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @param array $context The context for results
     *
     * @throws NoUniqueResultException on no unique result
     * @throws QueryException          on invalid query method
     */
    public function getOneOrNullResult(array $context = []): ?array;

    /**
     * Gets ALL result rows or values depending on query method.
     *
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @param array $context The context for results
     *
     * @throws QueryException  on invalid query method
     * @throws ResultException on result error
     */
    public function getResult(array $context = []): ResultInterface;

    /**
     * Gets ALL single scalar result values.
     *
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @param array $context The context for results
     *
     * @throws QueryException  on invalid query method
     * @throws ResultException on result error
     */
    public function getScalarResult(array $context = []): ScalarResult;

    /**
     * Gets ALL result rows as row result: a collection of rows.
     *
     * Allowed methods: SEARCH_READ.
     *
     * @param array $context The context for results
     *
     * @throws QueryException  on invalid query method
     * @throws ResultException on result error
     */
    public function getRowResult(array $context = []): RowResult;

    /**
     * Gets result into a paginator.
     *
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @param int<1, max>|null $nbItemsPerPage The number of items
     * @param array            $context        The context for results
     *
     * @throws QueryException on invalid query method
     */
    public function paginate(int $nbItemsPerPage = null, array $context = []): Paginator;

    /**
     * Gets the related record manager.
     */
    public function getRecordManager(): RecordManagerInterface;
}
