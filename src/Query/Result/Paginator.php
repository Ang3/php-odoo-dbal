<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Result;

use Ang3\Component\Odoo\DBAL\Query\Query;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\Query\QueryInterface;

/**
 * This class represents a paginator: a collection of array/scalar results.
 *
 * @method ResultInterface|null         offsetGet(mixed $offset)
 * @method void                         offsetSet(mixed $offset, ResultInterface $value)
 * @method \Generator|ResultInterface[] fetchAll()
 */
class Paginator extends ArrayResult
{
    /**
     * Default number of items per page for pagination.
     */
    public const DEFAULT_NB_ITEMS_PER_PAGE = 30;

    /**
     * @var Query
     */
    protected QueryInterface $query;
    private int $currentPage = 1;

    /**
     * @var int<0,max>
     */
    private ?int $nbPages = null;

    /**
     * @var int<1,max>
     */
    private int $nbItemsPerPage;

    /**
     * @param int<1,max>|null $nbItemsPerPage
     */
    public function __construct(
        QueryBuilder|QueryInterface $query,
        int $nbItemsPerPage = null,
        array $context = []
    ) {
        parent::__construct($query instanceof QueryInterface ? Query::fromInterface($query) : $query->getQuery(), [], $context);
        $this->setNbItemsPerPage($nbItemsPerPage ?: self::DEFAULT_NB_ITEMS_PER_PAGE);
    }

    /**
     * @return \Generator|ResultInterface[]
     */
    public function getIterator(): \Generator
    {
        $nbPages = $this->getNbPages();

        for ($i = 1; $i <= $nbPages; ++$i) {
            yield $this->getPage($i);
        }
    }

    public function first(): mixed
    {
        $this->rewind();

        return $this->getPage($this->currentPage);
    }

    public function current(): mixed
    {
        return $this->getPage($this->currentPage);
    }

    public function fetch(): false|ResultInterface
    {
        $result = $this->getPage(++$this->currentPage);

        return !$result->isEmpty() ? $result : false;
    }

    public function rewind(): void
    {
        $this->currentPage = 1;
    }

    public function last(): mixed
    {
        $this->currentPage = $this->getNbPages();

        return $this->getPage($this->currentPage);
    }

    public function toArray(): array
    {
        return iterator_to_array($this->fetchValues());
    }

    public function fetchValues(): \Generator
    {
        while ($value = $this->fetchAll()) {
            yield from $value;
        }
    }

    /**
     * @return int<1,max>
     */
    public function getNbPages(bool $forceReload = false): int
    {
        if (!$this->nbPages || $forceReload) {
            $this->nbPages = abs((int) ceil($this->nbTotalItems() / $this->nbItemsPerPage));
        }

        return $this->nbPages ?: 1;
    }

    public function getPage(int $page): ResultInterface
    {
        if ($page < 1) {
            throw new \InvalidArgumentException(sprintf('The page number must be greater than 0, got "%d".', $page));
        }

        return $this->query
            ->setOffset(($page - 1) * $this->nbItemsPerPage)
            ->setLimit($this->nbItemsPerPage)
            ->getResult($this->context)
        ;
    }

    public function count(): int
    {
        return $this->nbTotalItems();
    }

    public function nbTotalItems(): int
    {
        return $this->query->count();
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @return int<1, max>
     */
    public function getNbItemsPerPage(): int
    {
        return $this->nbItemsPerPage;
    }

    public function setNbItemsPerPage(int $nbItemsPerPage): self
    {
        if ($nbItemsPerPage < 1) {
            throw new \InvalidArgumentException(sprintf('The number of items per page must be greater than 0, got "%d".', $nbItemsPerPage));
        }

        $this->nbItemsPerPage = $nbItemsPerPage;
        $this->nbPages = null;

        return $this;
    }
}
