<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Utils;

use Ang3\Component\Odoo\DBAL\Query\OrmQuery;

class Paginator implements \IteratorAggregate
{
    public const DEFAULT_PAGE_SIZE = 100;

    private readonly int $pageSize;

    public function __construct(private readonly OrmQuery $query, int $pageSize = null)
    {
        if (!$this->query->isSearch()) {
            throw new \InvalidArgumentException(sprintf('You can paginate only search queries, but the query method is "%s".', $this->query->getMethod()));
        }

        $this->pageSize = $pageSize ?: self::DEFAULT_PAGE_SIZE;
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
        $nbPages = ceil($nbRecords / $this->pageSize);

        for ($i = 0; $i < $nbPages; ++$i) {
            $offset = $i * $this->pageSize;
            $query = $this->query
                ->duplicate()
                ->setOption('offset', $offset)
                ->setOption('limit', $this->pageSize)
            ;

            $result = $query->getResult();

            if (!$result) {
                break;
            }

            yield $query->getResult();
        }
    }

    public function getQuery(): OrmQuery
    {
        return $this->query;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }
}
