<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query;

class Paginator implements \IteratorAggregate
{
    /**
     * Context parameter keys.
     */
    public const PAGE_SIZE_KEY = 'page_size';

    /**
     * Default values.
     */
    public const DEFAULT_PAGE_SIZE = 100;

    private array $context = [
        self::PAGE_SIZE_KEY => self::DEFAULT_PAGE_SIZE
    ];

    public function __construct(private readonly OrmQuery $query, ?array $defaultContext = [])
    {
        if (!$this->query->isSearch()) {
            throw new \InvalidArgumentException(sprintf('You can paginate only search queries, but the query method is "%s".', $this->query->getMethod()));
        }

        $this->context = array_merge($this->context, $defaultContext ?: []);
    }

    public function getIterator(): \Generator
    {
        yield from $this->iterate();
    }

    /**
     * @return \Generator|array[]
     */
    private function iterate(): \Generator
    {
        $nbRecords = $this->query->count();
        $pageSize = $this->getPageSize();

        $nbPages = ceil($nbRecords / $pageSize);

        for ($i = 0; $i < $nbPages; ++$i) {
            $offset = $i * $pageSize;
            $query = $this->query
                ->duplicate()
                ->setOption('offset', $offset)
                ->setOption('limit', $pageSize)
            ;

            $result = $query->getResult();

            if (!$result) {
                break;
            }

            foreach ($result as $row) {
                yield $row;
            }
        }
    }

    public function getQuery(): OrmQuery
    {
        return $this->query;
    }

    public function getPageSize(): int
    {
        return (int) ($this->context[self::PAGE_SIZE_KEY] ?? self::DEFAULT_PAGE_SIZE);
    }

    public function setPageSize(int $pageSize): self
    {
        $this->context[self::PAGE_SIZE_KEY] = $pageSize;

        return $this;
    }
}
