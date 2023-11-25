<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query;

use Ang3\Component\Odoo\DBAL\Query\Enum\QueryMethod;
use Ang3\Component\Odoo\DBAL\Query\Result\NoResultException;
use Ang3\Component\Odoo\DBAL\Query\Result\NoUniqueResultException;
use Ang3\Component\Odoo\DBAL\Query\Result\Paginator;
use Ang3\Component\Odoo\DBAL\Query\Result\ResultInterface;
use Ang3\Component\Odoo\DBAL\Query\Result\RowResult;
use Ang3\Component\Odoo\DBAL\Query\Result\ScalarResult;
use Ang3\Component\Odoo\DBAL\RecordManagerInterface;

class Query implements QueryInterface
{
    final public function __construct(
        protected readonly RecordManagerInterface $recordManager,
        protected string $name,
        protected string $method,
        protected array $parameters = [],
        protected array $options = []
    ) {
    }

    public static function fromInterface(QueryInterface $query): self
    {
        return new self(
            $query->getRecordManager(),
            $query->getName(),
            $query->getMethod(),
            $query->getParameters(),
            $query->getOptions(),
        );
    }

    public function execute(): mixed
    {
        return $this->recordManager->executeQuery($this);
    }

    public function count(): int
    {
        if ($this->isCount()) {
            $query = $this;
        } else {
            if ($this->isInsertion()) {
                return 1;
            }

            if ($this->isUpdate() || $this->isDeletion()) {
                return \count(array_filter((array) ($this->parameters[0] ?? [])));
            }

            $query = new self($this->recordManager, $this->name, QueryMethod::SearchAndCount->value);
            $query->setParameters($this->parameters);
        }

        return (int) $query->execute();
    }

    public function getSingleScalarResult(array $context = []): bool|float|int|string
    {
        if (!$this->isRead()) {
            throw new QueryException(sprintf('You can get single scalar result with search methods only, but the query method is "%s".', $this->method));
        }

        $result = $this->getOneOrNullScalarResult($context);

        if (!$result) {
            throw new NoResultException();
        }

        return $result;
    }

    public function getOneOrNullScalarResult(array $context = []): null|bool|float|int|string
    {
        if (!$this->isRead()) {
            throw new QueryException(sprintf('You can get single scalar result with search methods only, but the query method is "%s".', $this->method));
        }

        $result = $this->getScalarResult($context);

        if ($result->count() > 1) {
            throw new NoUniqueResultException();
        }

        return $result->first();
    }

    public function getSingleResult(array $context = []): array
    {
        $result = $this->getOneOrNullResult($context);

        if (!$result) {
            throw new NoResultException();
        }

        return $result;
    }

    public function getOneOrNullResult(array $context = []): ?array
    {
        $result = $this->getRowResult($context);

        if ($result->count() > 1) {
            throw new NoUniqueResultException();
        }

        $firstRow = $result->fetchAssociative();

        return false !== $firstRow ? $firstRow : null;
    }

    public function getResult(array $context = []): ResultInterface
    {
        if (!$this->isRead()) {
            throw new QueryException(sprintf('You can get results with select/search queries only, but the query method is "%s".', $this->method));
        }

        return $this->recordManager->getResultFactory()->create($this, (array) $this->execute(), $context);
    }

    public function getScalarResult(array $context = []): ScalarResult
    {
        if (!$this->isRead()) {
            throw new QueryException(sprintf('You can get results with select/search queries only, but the query method is "%s".', $this->method));
        }

        return $this->recordManager->getResultFactory()->createScalarResult($this, (array) $this->execute(), $context);
    }

    public function getRowResult(array $context = []): RowResult
    {
        if (!$this->isSelection()) {
            throw new QueryException(sprintf('You can get results with select queries only, but the query method is "%s".', $this->method));
        }

        return $this->recordManager->getResultFactory()->createRowResult($this, (array) $this->execute(), $context);
    }

    public function paginate(?int $nbItemsPerPage = null, array $context = []): Paginator
    {
        if (!$this->isRead()) {
            throw new QueryException(sprintf('You can get results with select/search queries only, but the query method is "%s".', $this->method));
        }

        return new Paginator($this, $nbItemsPerPage, $context);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters = []): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options = []): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Adds an option on the query.
     */
    public function setOption(string $name, mixed $value): static
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Removes an option from the query.
     */
    public function removeOption(string $name): static
    {
        if ($this->hasOption($name)) {
            unset($this->options[$name]);
        }

        return $this;
    }

    public function getOption(string $name): mixed
    {
        return $this->options[$name] ?? null;
    }

    public function hasOption(string $name): bool
    {
        return \array_key_exists($name, $this->options);
    }

    public function clearOptions(): static
    {
        $this->options = [];

        return $this;
    }

    public function getOffset(): ?int
    {
        $offset = $this->getOption('offset');

        return \is_scalar($offset) ? (int) $offset : null;
    }

    public function setOffset(?int $offset): self
    {
        $this->setOption('offset', $offset);

        return $this;
    }

    public function getLimit(): ?int
    {
        $limit = $this->getOption('limit');

        return \is_scalar($limit) ? (int) $limit : null;
    }

    public function setLimit(?int $limit): self
    {
        $this->setOption('limit', $limit);

        return $this;
    }

    public function isWrite(): bool
    {
        return QueryMethod::tryFrom($this->method)?->isWrite() ?: false;
    }

    public function isInsertion(): bool
    {
        return QueryMethod::tryFrom($this->method)?->isInsertion() ?: false;
    }

    public function isUpdate(): bool
    {
        return QueryMethod::tryFrom($this->method)?->isUpdate() ?: false;
    }

    public function isRead(): bool
    {
        return QueryMethod::tryFrom($this->method)?->isRead() ?: false;
    }

    public function isSelection(): bool
    {
        return QueryMethod::tryFrom($this->method)?->isSelection() ?: false;
    }

    public function isSearch(): bool
    {
        return QueryMethod::tryFrom($this->method)?->isSearch() ?: false;
    }

    public function isCount(): bool
    {
        return QueryMethod::tryFrom($this->method)?->isCount() ?: false;
    }

    public function isDeletion(): bool
    {
        return QueryMethod::Delete->value === $this->method;
    }

    public function getRecordManager(): RecordManagerInterface
    {
        return $this->recordManager;
    }
}
