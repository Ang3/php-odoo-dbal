<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query;

use Ang3\Component\Odoo\DBAL\Query\Enum\OrmQueryMethod;
use Ang3\Component\Odoo\DBAL\Utils\Paginator;

class OrmQuery extends AbstractQuery implements QueryInterface
{
    /**
     * Counts the number of records from parameters.
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @throws QueryException on invalid query method
     */
    public function count(): int
    {
        if (!\in_array($this->method, [OrmQueryMethod::Search->value, OrmQueryMethod::SearchAndRead->value], true)) {
            throw new QueryException(sprintf('You can count results with method "%s" and "%s" only.', OrmQueryMethod::Search->value, OrmQueryMethod::SearchAndRead->value));
        }

        $query = new self($this->recordManager, $this->name, OrmQueryMethod::SearchAndCount->value);
        $query->setParameters($this->parameters);

        return (int) $query->execute();
    }

    /**
     * Gets just ONE scalar result.
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @throws NoUniqueResultException on no unique result
     * @throws NoResultException       on no result
     * @throws QueryException          on invalid query method
     */
    public function getSingleScalarResult(): bool|float|int|string
    {
        $result = $this->getOneOrNullScalarResult();

        if (!$result) {
            throw new NoResultException();
        }

        return $result;
    }

    /**
     * Gets one or NULL scalar result.
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @throws NoUniqueResultException on no unique result
     * @throws QueryException          on invalid query method
     */
    public function getOneOrNullScalarResult(): null|bool|float|int|string
    {
        $result = $this->getScalarResult();

        if (\count($result) > 1) {
            throw new NoUniqueResultException();
        }

        return array_shift($result);
    }

    /**
     * Gets a list of scalar result.
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @return array<bool|int|float|string>
     *
     * @throws QueryException on invalid query method
     */
    public function getScalarResult(): array
    {
        $result = $this->getResult();

        if (OrmQueryMethod::Search->value === $this->method) {
            return $result;
        }

        $selectedFields = $this->options['fields'] ?? [];
        if (\count($selectedFields) > 1) {
            throw new QueryException('More than one field selected.');
        }

        $selectedFieldName = $selectedFields[0] ?? 'id';

        foreach ($result as $key => $value) {
            $value = $value[$selectedFieldName] ?? null;

            if (null !== $value) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Gets one row.
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @throws NoUniqueResultException on no unique result
     * @throws NoResultException       on no result
     * @throws QueryException          on invalid query method
     */
    public function getSingleResult(): array
    {
        $result = $this->getOneOrNullResult();
        $result = \is_array($result) ? array_shift($result) : null;

        if (!$result) {
            throw new NoResultException();
        }

        return $result;
    }

    /**
     * Gets one or NULL row.
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @throws NoUniqueResultException on no unique result
     * @throws QueryException          on invalid query method
     */
    public function getOneOrNullResult(): ?array
    {
        $result = $this->getResult();

        if (\count($result) > 1) {
            throw new NoUniqueResultException();
        }

        return array_shift($result);
    }

    /**
     * Gets all result rows.
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @throws QueryException on invalid query method
     */
    public function getResult(): array
    {
        if (!\in_array($this->method, [OrmQueryMethod::Search->value, OrmQueryMethod::SearchAndRead->value], true)) {
            throw new QueryException(sprintf('You can get results with methods "%s" and "%s" only.', OrmQueryMethod::Search->value, OrmQueryMethod::SearchAndRead->value));
        }

        return (array) $this->execute();
    }

    /**
     * Paginates results from search query.
     * Allowed methods: SEARCH, SEARCH_READ.
     *
     * @throws QueryException on invalid query method
     */
    public function paginate(int $pageSize = null): Paginator
    {
        if (!\in_array($this->method, [OrmQueryMethod::Search->value, OrmQueryMethod::SearchAndRead->value], true)) {
            throw new QueryException(sprintf('You can get results with methods "%s" and "%s" only.', OrmQueryMethod::Search->value, OrmQueryMethod::SearchAndRead->value));
        }

        return new Paginator($this, $pageSize);
    }

    /**
     * @throws QueryException when the ORM method is not valid
     */
    public function setMethod(string $method): static
    {
        if (null === OrmQueryMethod::tryFrom($method)) {
            throw new QueryException(sprintf('The ORM query method "%s" is not valid.', $method));
        }

        $this->method = $method;

        return $this;
    }
}
