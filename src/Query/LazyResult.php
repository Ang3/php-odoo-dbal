<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query;

class LazyResult implements \IteratorAggregate
{
    /**
     * Context parameter keys.
     */
    public const BUFFER_SIZE_KEY = 'buffer_size';

    /**
     * Default values.
     */
    public const DEFAULT_BUFFER_SIZE = 100;

    private array $context = [
        self::BUFFER_SIZE_KEY => self::DEFAULT_BUFFER_SIZE,
    ];

    private readonly OrmQuery $query;

    /**
     * @throws QueryException on invalid query method
     */
    public function __construct(OrmQuery $query, ?array $defaultContext = [])
    {
        if (!$query->isSearch()) {
            throw new QueryException(sprintf('You can get lazy result only search queries, but the query method is "%s".', $query->getMethod()));
        }

        $this->query = $query->duplicate();
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
        $bufferSize = $this->getBufferSize();
        $nbRequests = ceil($nbRecords / $bufferSize);

        for ($i = 0; $i < $nbRequests; ++$i) {
            $offset = $i * $bufferSize;
            $query = $this->query
                ->duplicate()
                ->setOption('offset', $offset)
                ->setOption('limit', $bufferSize)
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

    public function getBufferSize(): int
    {
        return (int) ($this->context[self::BUFFER_SIZE_KEY] ?? self::DEFAULT_BUFFER_SIZE);
    }

    public function setBufferSize(int $size): self
    {
        $this->context[self::BUFFER_SIZE_KEY] = $size;

        return $this;
    }
}
