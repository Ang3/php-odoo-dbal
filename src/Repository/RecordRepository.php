<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Repository;

use Ang3\Component\Odoo\DBAL\Expression\Domain\CompositeDomain;
use Ang3\Component\Odoo\DBAL\Expression\Domain\DomainInterface;
use Ang3\Component\Odoo\DBAL\Expression\ExpressionBuilder;
use Ang3\Component\Odoo\DBAL\Query\OrmQuery;
use Ang3\Component\Odoo\DBAL\Query\Paginator;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\RecordManager;
use Ang3\Component\Odoo\DBAL\Schema\Model;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
class RecordRepository implements RecordRepositoryInterface
{
    public function __construct(private RecordManager $recordManager, private readonly string $modelName)
    {
        $recordManager->addRepository($this);
    }

    public function getMetadata(): Model
    {
        return $this->recordManager->getSchema()->getModel($this->modelName);
    }

    public function insert(array $data): int
    {
        if (!$data) {
            throw new \InvalidArgumentException('Data cannot be empty');
        }

        $result = $this
            ->createQueryBuilder()
            ->insert()
            ->setValues($data)
            ->getQuery()
            ->execute()
        ;

        return \is_scalar($result) ? (int) $result : 0;
    }

    public function update(array|int $ids, array $data = []): void
    {
        if (!$data) {
            return;
        }

        $this
            ->createQueryBuilder()
            ->update((array) $ids)
            ->setValues($data)
            ->getQuery()
            ->execute()
        ;
    }

    public function delete(array|int $ids): void
    {
        if (!$ids) {
            return;
        }

        $this
            ->createQueryBuilder()
            ->delete((array) $ids)
            ->getQuery()
            ->execute()
        ;
    }

    public function searchOne(array|DomainInterface $criteria = null): ?int
    {
        return (int) $this
            ->createQueryBuilder()
            ->search()
            ->where($this->normalizeCriteria($criteria))
            ->getQuery()
            ->getOneOrNullScalarResult()
        ;
    }

    public function searchAll(array $orders = [], int $limit = null, int $offset = null): array
    {
        return $this->search(null, $orders, $limit, $offset);
    }

    public function search(array|DomainInterface $criteria = null, array $orders = [], int $limit = null, int $offset = null): array
    {
        return $this
            ->createQueryBuilder()
            ->search()
            ->where($this->normalizeCriteria($criteria))
            ->setOrders($orders)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getScalarResult()
        ;
    }

    public function read(int $id, ?array $fields = []): array
    {
        $record = $this->find($id, $fields);

        if (!$record) {
            throw new RecordNotFoundException($this->modelName, $id);
        }

        return $record;
    }

    public function find(int $id, ?array $fields = []): ?array
    {
        return $this->findOneBy($this->expr()->eq('id', $id), $fields);
    }

    public function findOneBy(array|DomainInterface $criteria = null, ?array $fields = [], array $orders = [], int $offset = null): ?array
    {
        $result = $this->findBy($criteria, $fields, $orders, 1, $offset);

        return array_pop($result);
    }

    public function findAll(?array $fields = [], array $orders = [], int $limit = null, int $offset = null): array
    {
        return $this->findBy(null, $fields, $orders, $limit, $offset);
    }

    public function findBy(array|DomainInterface $criteria = null, ?array $fields = [], array $orders = [], int $limit = null, int $offset = null): array
    {
        return $this
            ->prepare($criteria, $fields, $orders, $limit, $offset)
            ->getResult()
        ;
    }

    public function prepare(array|DomainInterface $criteria = null, ?array $fields = [], array $orders = [], int $limit = null, int $offset = null): OrmQuery
    {
        return $this
            ->createQueryBuilder()
            ->select($fields)
            ->where($this->normalizeCriteria($criteria))
            ->setOrders($orders)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
        ;
    }

    public function exists(int $id): bool
    {
        return 1 === $this->count($this->expr()->eq('id', $id));
    }

    public function countAll(): int
    {
        return $this->count();
    }

    public function count(array|DomainInterface $criteria = null): int
    {
        return $this
            ->createQueryBuilder()
            ->select()
            ->where($this->normalizeCriteria($criteria))
            ->getQuery()
            ->count()
        ;
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->recordManager
            ->createQueryBuilder($this->modelName)
            ->select()
        ;
    }

    public function setRecordManager(RecordManager $recordManager): self
    {
        $this->recordManager = $recordManager;

        return $this;
    }

    public function getRecordManager(): RecordManager
    {
        return $this->recordManager;
    }

    public function getModelName(): string
    {
        return $this->modelName;
    }

    public function expr(): ExpressionBuilder
    {
        return $this->recordManager->getExpressionBuilder();
    }

    public function normalizeCriteria(array|DomainInterface $criteria = null): ?DomainInterface
    {
        return \is_array($criteria) ? CompositeDomain::criteria($criteria) : $criteria;
    }
}
