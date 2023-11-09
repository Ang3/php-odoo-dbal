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
use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\DomainInterface;
use Ang3\Component\Odoo\DBAL\Query\Expression\Exception\ConversionException;
use Ang3\Component\Odoo\DBAL\Query\Expression\ExpressionBuilderInterface;
use Ang3\Component\Odoo\DBAL\RecordManager;

use function Symfony\Component\String\s;

class QueryBuilder
{
    /**
     * Query methods.
     */
    public const SELECT = 'select';
    public const SEARCH = 'search';
    public const INSERT = 'insert';
    public const UPDATE = 'update';
    public const DELETE = 'delete';

    /**
     * The type of query this is. Can be select, search, insert, update or delete.
     */
    private string $type = self::SELECT;
    private string $from;

    /**
     * @var string[]
     */
    private array $select = [];
    private array $ids = [];
    private array $values = [];
    private ?DomainInterface $where = null;
    private array $orders = [];
    private ?int $maxResults = null;
    private ?int $firstResult = null;

    public function __construct(private readonly RecordManager $recordManager, string $from)
    {
        $this->from($from);
    }

    private static function isEmptyName(string $fieldName = null): bool
    {
        return '' === s((string) $fieldName)
            ->replaceMatches('#[^a-zA-Z\-\_\.]+#', '')
            ->toString()
        ;
    }

    /**
     * Sets the target model name.
     */
    public function from(string $modelName): self
    {
        if (self::isEmptyName($modelName)) {
            throw new \InvalidArgumentException(sprintf('The model name cannot be empty (value: "%s").', $modelName));
        }

        $this->from = $modelName;

        return $this;
    }

    /**
     * Gets the target model name of the query.
     */
    public function getFrom(): ?string
    {
        return $this->from;
    }

    /**
     * Defines the query of type "SELECT" with selected fields.
     * No fields selected = all fields returned.
     */
    public function select(array|string $fields = null): self
    {
        $this->reset();
        $this->type = self::SELECT;
        $fields = array_filter(\is_array($fields) ? $fields : [$fields], static fn ($value) => null !== $value);

        foreach ($fields as $fieldName) {
            $this->addSelect($fieldName);
        }

        return $this;
    }

    /**
     * Adds a field to select.
     *
     * @throws QueryException when the type of the query is not "SELECT"
     */
    public function addSelect(string $fieldName): self
    {
        $this->assertMethodQueryBuilderType(__METHOD__, self::SELECT);

        if (self::isEmptyName($fieldName)) {
            throw new \InvalidArgumentException(sprintf('The field name cannot be empty (value: "%s").', $fieldName));
        }

        if (!\in_array($fieldName, $this->select, true)) {
            $this->select[] = $fieldName;
        }

        return $this;
    }

    /**
     * Gets selected fields.
     */
    public function getSelect(): array
    {
        return $this->select;
    }

    /**
     * Defines the query of type "SEARCH".
     */
    public function search(string $modelName = null): self
    {
        $this->reset($modelName);
        $this->type = self::SEARCH;

        return $this;
    }

    /**
     * Defines the query of type "INSERT".
     */
    public function insert(string $modelName = null): self
    {
        $this->reset($modelName);
        $this->type = self::INSERT;

        return $this;
    }

    /**
     * Defines the query of type "UPDATE" with ids of records to update and data.
     */
    public function update(string $modelName = null): self
    {
        $this->reset($modelName);
        $this->type = self::UPDATE;

        return $this;
    }

    /**
     * Defines the query of type "DELETE" with ids of records to delete.
     */
    public function delete(string $modelName = null): self
    {
        $this->reset($modelName);
        $this->type = self::DELETE;

        return $this;
    }

    /**
     * Sets target IDs in case of query of type "UPDATE" or "DELETE".
     *
     * @throws QueryException when the type of the query is not "UPDATE" nor "DELETE"
     */
    public function setIds(null|array|int $ids): self
    {
        $this->assertMethodQueryBuilderType(__METHOD__, [self::UPDATE, self::DELETE]);
        $this->ids = [];

        if (null !== $ids) {
            $ids = \is_array($ids) ? $ids : [$ids];

            foreach ($ids as $id) {
                $this->addId($id);
            }
        }

        return $this;
    }

    /**
     * Adds target ID in case of query of type "UPDATE" or "DELETE".
     *
     * @throws QueryException when the type of the query is not "UPDATE" nor "DELETE"
     */
    public function addId(int $id): self
    {
        $this->assertMethodQueryBuilderType(__METHOD__, [self::UPDATE, self::DELETE]);

        if ($id <= 0) {
            throw new \InvalidArgumentException('An IDentifiers cannot be less than or equal to 0.');
        }

        if (!\in_array($id, $this->ids, true)) {
            $this->ids[] = $id;
        }

        return $this;
    }

    /**
     * @return int[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * Sets field values in case of query of type "INSERT" or "UPDATE".
     *
     * @throws QueryException when the type of the query is not "INSERT" nor "UPDATE"
     */
    public function setValues(array $values = []): self
    {
        $this->assertMethodQueryBuilderType(__METHOD__, [self::INSERT, self::UPDATE]);
        $this->values = [];

        foreach ($values as $fieldName => $value) {
            $this->set($fieldName, $value);
        }

        return $this;
    }

    /**
     * Set a field value in case of query of type "INSERT" or "UPDATE".
     *
     * @throws QueryException when the type of the query is not "INSERT" nor "UPDATE"
     */
    public function set(string $fieldName, mixed $value): self
    {
        $this->assertMethodQueryBuilderType(__METHOD__, [self::INSERT, self::UPDATE]);
        $this->values[$fieldName] = $value;

        return $this;
    }

    /**
     * Gets field values set in case of query of type "INSERT" or "UPDATE".
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Sets criteria for queries of type "SELECT" and "SEARCH".
     *
     * @throws QueryException when the type of the query is not "SELECT" not "SEARCH"
     */
    public function where(DomainInterface $domain = null): self
    {
        $this->assertSupportsWhereClause();
        $this->where = $domain;

        return $this;
    }

    /**
     * Takes the WHERE clause and adds a node with logical operator AND.
     *
     * @throws QueryException when the type of the query is not "SELECT" nor "SEARCH"
     */
    public function andWhere(DomainInterface $domain): self
    {
        $this->assertSupportsWhereClause();
        $this->where = $this->where ? $this->expr()->andX($this->where, $domain) : $domain;

        return $this;
    }

    /**
     * Takes the WHERE clause and adds a node with logical operator OR.
     *
     * @throws QueryException when the type of the query is not "SELECT" nor "SEARCH"
     */
    public function orWhere(DomainInterface $domain): self
    {
        $this->assertSupportsWhereClause();
        $this->where = $this->where ? $this->expr()->orX($this->where, $domain) : $domain;

        return $this;
    }

    /**
     * Gets the WHERE clause.
     */
    public function getWhere(): ?DomainInterface
    {
        return $this->where;
    }

    /**
     * @internal
     *
     * @throws QueryException when the type of the query is not "SELECT" nor "SEARCH"
     */
    private function assertSupportsWhereClause(): void
    {
        if (!\in_array($this->type, [self::SELECT, self::SEARCH], true)) {
            throw new QueryException('You can set criteria in query of type "SELECT" or "SEARCH" only.');
        }
    }

    /**
     * Sets orders.
     */
    public function setOrders(array $orders = []): self
    {
        $this->orders = [];

        foreach ($orders as $fieldName => $isAsc) {
            $this->addOrderBy($fieldName, $isAsc);
        }

        return $this;
    }

    /**
     * Clears orders and adds one.
     */
    public function orderBy(string $fieldName, bool $isAsc = true): self
    {
        $this->orders = [];

        return $this->addOrderBy($fieldName, $isAsc);
    }

    /**
     * Adds order.
     *
     * @throws QueryException when the query type is not valid
     */
    public function addOrderBy(string $fieldName, bool $isAsc = true): self
    {
        if (self::isEmptyName($fieldName)) {
            throw new \InvalidArgumentException(sprintf('The field name cannot be empty (value: "%s").', $fieldName));
        }

        if (!\in_array($this->type, [self::SELECT, self::SEARCH], true)) {
            throw new QueryException('You can set orders in query of type "SELECT", "SEARCH" only.');
        }

        $this->orders[$fieldName] = $isAsc;

        return $this;
    }

    /**
     * Gets ordered fields.
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * Sets the max results of the query (limit).
     */
    public function setMaxResults(?int $maxResults): self
    {
        if (null !== $maxResults && $maxResults <= 0) {
            throw new \InvalidArgumentException(sprintf('The first result cannot be less than or equal to 0 (value: "%d").', $maxResults));
        }

        $this->maxResults = $maxResults;

        return $this;
    }

    /**
     * Gets the max results of the query.
     */
    public function getMaxResults(): ?int
    {
        return $this->maxResults;
    }

    /**
     * Sets the first results of the query (offset).
     */
    public function setFirstResult(?int $firstResult): self
    {
        if (null !== $firstResult && $firstResult < 0) {
            throw new \InvalidArgumentException(sprintf('The first result cannot be less than 0 (value: "%d").', $firstResult));
        }

        $this->firstResult = $firstResult;

        return $this;
    }

    /**
     * Gets the first results of the query.
     */
    public function getFirstResult(): ?int
    {
        return $this->firstResult;
    }

    /**
     * Reinitialize the query builder with FROM clause.
     */
    public function reset(string $modelName = null): self
    {
        if (null !== $modelName) {
            $this->from($modelName);
        }

        $this->select = [];
        $this->ids = [];
        $this->values = [];
        $this->where = null;
        $this->orders = [];
        $this->maxResults = null;
        $this->firstResult = null;

        return $this;
    }

    /**
     * Computes and returns the query.
     *
     * @throws QueryException      on invalid query
     * @throws ConversionException on data conversion failure
     */
    public function getQuery(): OrmQuery
    {
        $method = match ($this->type) {
            self::SELECT => OrmQueryMethod::SearchAndRead,
            self::SEARCH => OrmQueryMethod::Search,
            self::INSERT => OrmQueryMethod::Create,
            self::UPDATE => OrmQueryMethod::Write,
            self::DELETE => OrmQueryMethod::Unlink,
            default => throw new \InvalidArgumentException(sprintf('The query type "%s" is not valid.', $this->type)),
        };

        $query = new OrmQuery($this->recordManager, $this->from, $method->value);

        if (\in_array($this->type, [self::SELECT, self::SEARCH], true)) {
            $parameters = $this->expr()->normalizeDomains($this->where);
        } elseif (self::DELETE === $this->type) {
            if (!$this->ids) {
                throw new QueryException('You must set indexes for queries of type "DELETE".');
            }

            $parameters = [$this->ids];
        } else {
            if (!$this->values) {
                throw new QueryException('You must set values for queries of type "INSERT" and "UPDATE".');
            }

            $parameters = $this->expr()->normalizeData($this->values);

            if (self::UPDATE === $this->type) {
                if (!$this->ids) {
                    throw new QueryException('You must set indexes for queries of type "UPDATE".');
                }

                $parameters = [$this->ids, $parameters];
            } else {
                $parameters = [$parameters];
            }
        }

        $query->setParameters($parameters);

        if (\in_array($this->type, [self::SELECT, self::SEARCH], true)) {
            $options = [];

            if (self::SELECT === $this->type && $this->select) {
                $options['fields'] = $this->select;
            }

            $orders = $this->orders;

            if ($orders) {
                foreach ($orders as $fieldName => $isAsc) {
                    $orders[$fieldName] = sprintf('%s %s', $fieldName, $isAsc ? 'asc' : 'desc');
                }

                $options['order'] = implode(', ', $orders);
            }

            if ($this->firstResult) {
                $options['offset'] = $this->firstResult;
            }

            if ($this->maxResults) {
                $options['limit'] = $this->maxResults;
            }

            $query->setOptions($options);
        }

        return $query;
    }

    /**
     * Gets the type of the query.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Gets the related manager of the query.
     */
    public function getRecordManager(): RecordManager
    {
        return $this->recordManager;
    }

    /**
     * Shortcut to the expression builder of the related client.
     */
    public function expr(): ExpressionBuilderInterface
    {
        return $this->recordManager->getExpressionBuilder();
    }

    /**
     * @internal
     */
    private function assertMethodQueryBuilderType(string $methodName, array|string $types): void
    {
        $allowedTypes = \is_array($types) ? $types : [$types];

        if (!\in_array($this->type, $allowedTypes, true)) {
            throw new QueryException(sprintf('You cannot call the method "%s" when the query type is "%s" (possible types: "%s").', $methodName, $this->type, implode('", "', $allowedTypes)));
        }
    }
}
