<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL;

use Ang3\Component\Odoo\Client;
use Ang3\Component\Odoo\DBAL\Query\Expression\ExpressionBuilderInterface;
use Ang3\Component\Odoo\DBAL\Query\Factory\QueryFactoryInterface;
use Ang3\Component\Odoo\DBAL\Query\Factory\ResultFactoryInterface;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\Query\QueryInterface;
use Ang3\Component\Odoo\DBAL\Query\RecordNotFoundException;
use Ang3\Component\Odoo\DBAL\Repository\RecordRepositoryInterface;
use Ang3\Component\Odoo\DBAL\Repository\RepositoryRegistry;
use Ang3\Component\Odoo\DBAL\Schema\SchemaInterface;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
interface RecordManagerInterface
{
    /**
     * Creates a new record and returns the ID.
     */
    public function create(string $modelName, array $data): int;

    /**
     * Gets a record by ID.
     * You can specify which fields to select as third argument.
     * An exception is thrown if the record is not found.
     *
     * @throws RecordNotFoundException when the record was not found
     */
    public function read(string $modelName, int $id, ?array $fields = []): array;

    /**
     * Finds a record by ID.
     * You can specify which fields to select as third argument.
     */
    public function find(string $modelName, int $id, ?array $fields = []): ?array;

    /**
     * Updates a record by ID (PATCH).
     * Pass data to update as third argument.
     */
    public function update(string $modelName, array|int $ids, array $data): void;

    /**
     * Deletes records by ID.
     */
    public function delete(string $modelName, array|int $ids): void;

    /**
     * Gets the repository of a given model.
     */
    public function getRepository(string $modelName): RecordRepositoryInterface;

    /**
     * Creates a query builder to build query.
     */
    public function createQueryBuilder(string $modelName): QueryBuilder;

    /**
     * Executes a query.
     */
    public function executeQuery(QueryInterface $query): mixed;

    /**
     * Gets the client.
     */
    public function getClient(): Client;

    /**
     * Gets the configuration.
     */
    public function getConfiguration(): Configuration;

    /**
     * Gets the schema.
     */
    public function getSchema(): SchemaInterface;

    /**
     * Gets the repository registry.
     */
    public function getRepositories(): RepositoryRegistry;

    /**
     * Gets the query factory.
     */
    public function getQueryFactory(): QueryFactoryInterface;

    /**
     * Gets the result factory.
     */
    public function getResultFactory(): ResultFactoryInterface;

    /**
     * Gets the expression builder.
     */
    public function getExpressionBuilder(): ExpressionBuilderInterface;
}
