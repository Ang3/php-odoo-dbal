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
        if ($this->isCount()) {
            $query = $this;
        } else {
            if (!$this->isSearch()) {
                throw new QueryException(sprintf('You can count results with search methods only, but the query method is "%s".', $this->method));
            }

            $query = new self($this->recordManager, $this->name, OrmQueryMethod::SearchAndCount->value);
            $query->setParameters($this->parameters);
        }

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
        if (!$this->isSearch()) {
            throw new QueryException(sprintf('You can get single scalar result with search methods only, but the query method is "%s".', $this->method));
        }

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
        if (!$this->isSearch() && !$this->isCount()) {
            throw new QueryException(sprintf('You can get scalar results with search/count methods only, but the query method is "%s".', $this->method));
        }

        $result = $this->getResult();

        if ($this->isCount()) {
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
        if (!$this->isSearch()) {
            throw new QueryException(sprintf('You can get results with search queries only, but the query method is "%s".', $this->method));
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
        if (!$this->isSearch()) {
            throw new QueryException(sprintf('You can get results with search queries only, but the query method is "%s".', $this->method));
        }

        return new Paginator($this, [
            Paginator::PAGE_SIZE_KEY => $pageSize
        ]);
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

    public function getOffset(): ?int
    {
        return $this->getOption('offset');
    }

    public function setOffset(?int $offset): self
    {
        $this->setOption('offset', $offset);

        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->getOption('limit');
    }

    public function setLimit(?int $limit): self
    {
        $this->setOption('limit', $limit);

        return $this;
    }

    public function isWrite(): bool
    {
        return \in_array($this->method, [
            OrmQueryMethod::Create->value,
            OrmQueryMethod::Write->value,
        ], true);
    }

    public function isSearch(): bool
    {
        return \in_array($this->method, [
            OrmQueryMethod::Search->value,
            OrmQueryMethod::SearchAndRead->value,
        ], true);
    }

    public function isCount(): bool
    {
        return OrmQueryMethod::SearchAndCount->value === $this->method;
    }

    public function isDeletion(): bool
    {
        return OrmQueryMethod::Unlink->value === $this->method;
    }
}
