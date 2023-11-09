<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Expression;

use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\Comparison;
use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\CompositeDomain;
use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\DomainInterface;
use Ang3\Component\Odoo\DBAL\Query\Expression\Exception\ConversionException;
use Ang3\Component\Odoo\DBAL\Query\Expression\Operation\CollectionOperation;

interface ExpressionBuilderInterface
{
    /**
     * Create a logical operation "AND".
     */
    public function andX(DomainInterface ...$domains): CompositeDomain;

    /**
     * Create a logical operation "OR".
     */
    public function orX(DomainInterface ...$domains): CompositeDomain;

    /**
     * Create a logical operation "NOT".
     */
    public function notX(DomainInterface ...$domains): CompositeDomain;

    /**
     * Check if the field is EQUAL TO the value.
     */
    public function eq(string $fieldName, mixed $value): Comparison;

    /**
     * Check if the field is NOT EQUAL TO the value.
     */
    public function neq(string $fieldName, mixed $value): Comparison;

    /**
     * Check if the field is UNSET OR EQUAL TO the value.
     */
    public function ueq(string $fieldName, mixed $value): Comparison;

    /**
     * Check if the field is LESS THAN the value.
     */
    public function lt(string $fieldName, mixed $value): Comparison;

    /**
     * Check if the field is LESS THAN OR EQUAL the value.
     */
    public function lte(string $fieldName, mixed $value): Comparison;

    /**
     * Check if the field is GREATER THAN the value.
     */
    public function gt(string $fieldName, mixed $value): Comparison;

    /**
     * Check if the field is GREATER THAN OR EQUAL the value.
     */
    public function gte(string $fieldName, mixed $value): Comparison;

    /**
     * Check if the variable is LIKE the value.
     *
     * An underscore _ in the pattern stands for (matches) any single character
     * A percent sign % matches any string of zero or more characters.
     *
     * If $strict is set to FALSE, the value pattern is "%value%" (automatically wrapped into signs %).
     */
    public function like(string $fieldName, mixed $value, bool $strict = false, bool $caseSensitive = true): Comparison;

    /**
     * Check if the field is IS NOT LIKE the value.
     */
    public function notLike(string $fieldName, mixed $value, bool $caseSensitive = true): Comparison;

    /**
     * Check if the field is IN values list.
     */
    public function in(string $fieldName, array|bool|float|int|string $values): Comparison;

    /**
     * Check if the field is NOT IN values list.
     */
    public function notIn(string $fieldName, array|bool|float|int|string $values): Comparison;

    /**
     * Adds a new record created from data.
     *
     * @throws \InvalidArgumentException when $data is empty
     */
    public function createRecord(array $data): CollectionOperation;

    /**
     * Updates an existing record of id $id with data.
     * /!\ Can not be used in record create operation.
     *
     * @throws \InvalidArgumentException when $data is empty
     */
    public function updateRecord(int $id, array $data): CollectionOperation;

    /**
     * Adds an existing record of id $id to the collection.
     */
    public function addRecord(int $id): CollectionOperation;

    /**
     * Removes the record of id $id from the collection, but does not delete it.
     * /!\ Can not be used in record create operation.
     */
    public function removeRecord(int $id): CollectionOperation;

    /**
     * Removes the record of id $id from the collection, then deletes it from the database.
     * /!\ Can not be used in record create operation.
     */
    public function deleteRecord(int $id): CollectionOperation;

    /**
     * Replaces all existing records in the collection by the $ids list,
     * Equivalent to using the command "clear" followed by a command "add" for each id in $ids.
     */
    public function replaceRecords(array $ids = []): CollectionOperation;

    /**
     * Removes all records from the collection, equivalent to using the command "remove" on every record explicitly.
     * /!\ Can not be used in record create operation.
     */
    public function clearRecords(): CollectionOperation;

    /**
     * @throws \InvalidArgumentException when $criteria value is not valid
     * @throws ConversionException       on data conversion failure
     */
    public function normalizeDomains(iterable $criteria = null): array;

    /**
     * @throws ConversionException on data conversion failure
     */
    public function normalizeData(array $data = []): array;
}
