<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Repository;

use Ang3\Component\Odoo\DBAL\Query\Enum\OrmQueryMethod;
use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\DomainInterface;
use Ang3\Component\Odoo\DBAL\Query\OrmQuery;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\RecordManager;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
interface RecordRepositoryInterface
{
    /**
     * Register the related record manager of the repository.
     */
    public function setRecordManager(RecordManager $recordManager): void;

    /**
     * Gets the model metadata from the schema.
     */
    public function getMetadata(): ModelMetadata;

    /**
     * Insert a new record.
     *
     * @return int the ID of the new record
     *
     * @throws \InvalidArgumentException when $data is empty
     */
    public function insert(array $data): int;

    /**
     * Update record(s).
     *
     * NB: It is not currently possible to perform “computed” updates
     * (where the value being set depends on an existing value of a record).
     */
    public function update(array|int $ids, array $data = []): void;

    /**
     * Delete record(s).
     */
    public function delete(array|int $ids): void;

    /**
     * Search one ID of record by criteria.
     */
    public function searchOne(array|DomainInterface $criteria = null): ?int;

    /**
     * Search all ID of record(s).
     *
     * @return int[]
     */
    public function searchAll(array $orders = [], int $limit = null, int $offset = null): array;

    /**
     * Search ID of record(s) by criteria.
     *
     * @return int[]
     */
    public function search(array|DomainInterface $criteria = null, array $orders = [], int $limit = null, int $offset = null): array;

    /**
     * Find ONE record by ID.
     *
     * @throws RecordNotFoundException when the record was not found
     */
    public function read(int $id, ?array $fields = []): array;

    /**
     * Find ONE record by ID.
     */
    public function find(int $id, ?array $fields = []): ?array;

    /**
     * Find ONE record by criteria.
     */
    public function findOneBy(array|DomainInterface $criteria = null, ?array $fields = [], array $orders = [], int $offset = null): ?array;

    /**
     * Find all records.
     *
     * @return array[]
     */
    public function findAll(?array $fields = [], array $orders = [], int $limit = null, int $offset = null): array;

    /**
     * Find record(s) by criteria.
     *
     * @return array[]
     */
    public function findBy(array|DomainInterface $criteria = null, ?array $fields = [], array $orders = [], int $limit = null, int $offset = null): array;

    /**
     * Prepares and creates ORM query by criteria and/or others parameters.
     */
    public function prepare(array|DomainInterface $criteria = null, ?array $fields = [], array $orders = [], int $limit = null, int $offset = null): QueryBuilder;

    /**
     * Check if a record exists.
     */
    public function exists(int $id): bool;

    /**
     * Count number of all records for the model.
     */
    public function countAll(): int;

    /**
     * Count number of records for a model and criteria.
     */
    public function count(array|DomainInterface $criteria = null): int;

    public function createQueryBuilder(): QueryBuilder;

    public function createOrmQuery(OrmQueryMethod $method): OrmQuery;

    public function getRecordManager(): RecordManager;

    public function getModelName(): string;
}
