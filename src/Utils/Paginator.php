<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Utils;

use Ang3\Component\Odoo\DBAL\Query\Enum\OrmQueryMethod;
use Ang3\Component\Odoo\DBAL\Query\OrmQuery;

class Paginator implements \IteratorAggregate
{
    public const DEFAULT_PAGE_SIZE = 100;

    private readonly int $pageSize;

    public function __construct(private readonly OrmQuery $query, int $pageSize = null)
    {
        if (!\in_array($this->query->getMethod(), [OrmQueryMethod::Search->value, OrmQueryMethod::SearchAndRead->value], true)) {
            throw new \LogicException(sprintf('You can paginate only queries of method "search|search_read", got "%s".', $this->query->getMethod()));
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
