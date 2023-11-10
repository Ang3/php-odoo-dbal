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
use Ang3\Component\Odoo\DBAL\Query\Expression\Operation\CollectionOperation;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
class ExpressionBuilder implements ExpressionBuilderInterface
{
    public function andX(DomainInterface ...$domains): CompositeDomain
    {
        return new CompositeDomain(CompositeDomain::AND, $domains ?: []);
    }

    public function orX(DomainInterface ...$domains): CompositeDomain
    {
        return new CompositeDomain(CompositeDomain::OR, $domains ?: []);
    }

    public function notX(DomainInterface ...$domains): CompositeDomain
    {
        return new CompositeDomain(CompositeDomain::NOT, $domains ?: []);
    }

    public function eq(string $fieldName, mixed $value): Comparison
    {
        return new Comparison($fieldName, Comparison::EQUAL_TO, $value);
    }

    public function neq(string $fieldName, mixed $value): Comparison
    {
        return new Comparison($fieldName, Comparison::NOT_EQUAL_TO, $value);
    }

    public function ueq(string $fieldName, mixed $value): Comparison
    {
        return new Comparison($fieldName, Comparison::UNSET_OR_EQUAL_TO, $value);
    }

    public function lt(string $fieldName, mixed $value): Comparison
    {
        return new Comparison($fieldName, Comparison::LESS_THAN, $value);
    }

    public function lte(string $fieldName, mixed $value): Comparison
    {
        return new Comparison($fieldName, Comparison::LESS_THAN_OR_EQUAL, $value);
    }

    public function gt(string $fieldName, mixed $value): Comparison
    {
        return new Comparison($fieldName, Comparison::GREATER_THAN, $value);
    }

    public function gte(string $fieldName, mixed $value): Comparison
    {
        return new Comparison($fieldName, Comparison::GREATER_THAN_OR_EQUAL, $value);
    }

    public function like(string $fieldName, mixed $value, bool $strict = false, bool $caseSensitive = true): Comparison
    {
        if ($strict) {
            $operator = $caseSensitive ? Comparison::EQUAL_LIKE : Comparison::INSENSITIVE_EQUAL_LIKE;
        } else {
            $operator = $caseSensitive ? Comparison::LIKE : Comparison::INSENSITIVE_LIKE;
        }

        return new Comparison($fieldName, $operator, $value);
    }

    public function notLike(string $fieldName, mixed $value, bool $caseSensitive = true): Comparison
    {
        $operator = $caseSensitive ? Comparison::NOT_LIKE : Comparison::INSENSITIVE_NOT_LIKE;

        return new Comparison($fieldName, $operator, $value);
    }

    public function in(string $fieldName, array|bool|float|int|string $values): Comparison
    {
        return new Comparison($fieldName, Comparison::IN, $this->getValues($values));
    }

    public function notIn(string $fieldName, array|bool|float|int|string $values): Comparison
    {
        return new Comparison($fieldName, Comparison::NOT_IN, $this->getValues($values));
    }

    public function createRecord(array $data): CollectionOperation
    {
        return CollectionOperation::create($data);
    }

    public function updateRecord(int $id, array $data): CollectionOperation
    {
        if (!$data) {
            throw new \InvalidArgumentException('Data cannot be empty');
        }

        return CollectionOperation::update($id, $data);
    }

    public function addRecord(int $id): CollectionOperation
    {
        return CollectionOperation::add($id);
    }

    public function removeRecord(int $id): CollectionOperation
    {
        return CollectionOperation::remove($id);
    }

    public function deleteRecord(int $id): CollectionOperation
    {
        return CollectionOperation::delete($id);
    }

    public function replaceRecords(array $ids = []): CollectionOperation
    {
        return CollectionOperation::replace($ids);
    }

    public function clearRecords(): CollectionOperation
    {
        return CollectionOperation::clear();
    }

    /**
     * @internal
     */
    private function getValues(array|bool|float|int|string $values): array
    {
        return \is_array($values) ? $values : [$values];
    }
}
