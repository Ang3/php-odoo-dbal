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
use Ang3\Component\Odoo\DBAL\Expression\ExpressionBuilder;
use Ang3\Component\Odoo\DBAL\Query\NativeQuery;
use Ang3\Component\Odoo\DBAL\Query\OrmQuery;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\Query\QueryInterface;
use Ang3\Component\Odoo\DBAL\Repository\RecordRepository;
use Ang3\Component\Odoo\DBAL\Repository\RecordRepositoryInterface;
use Ang3\Component\Odoo\DBAL\Repository\RepositoryRegistry;
use Ang3\Component\Odoo\DBAL\Schema\Schema;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
class RecordManager
{
    private Schema $schema;
    private RepositoryRegistry $repositoryRegistry;
    private ExpressionBuilder $expressionBuilder;

    public function __construct(private readonly Client $client)
    {
        $this->schema = new Schema($this);
        $this->repositoryRegistry = new RepositoryRegistry($this);
        $this->expressionBuilder = new ExpressionBuilder();
    }

    public function find(string $modelName, int $id, ?array $fields = []): ?array
    {
        return $this->getRepository($modelName)->find($id, $fields);
    }

    public function createQueryBuilder(string $modelName): QueryBuilder
    {
        return new QueryBuilder($this, $modelName);
    }

    public function createOrmQuery(string $name, string $method): OrmQuery
    {
        return new OrmQuery($this, $name, $method);
    }

    public function createNativeQuery(string $name, string $method): NativeQuery
    {
        return new NativeQuery($this, $name, $method);
    }

    public function executeQuery(QueryInterface $query): mixed
    {
        $options = $query->getOptions();

        if (!$options) {
            return $this->client->executeKw($query->getName(), $query->getMethod(), $query->getParameters());
        }

        return $this->client->executeKw($query->getName(), $query->getMethod(), $query->getParameters(), $options);
    }

    public function getRepository(string $modelName): RecordRepositoryInterface
    {
        return $this->repositoryRegistry->get($modelName);
    }

    public function getRepositoryRegistry(): RepositoryRegistry
    {
        return $this->repositoryRegistry;
    }

    public function setRepositoryRegistry(RepositoryRegistry $repositoryRegistry): self
    {
        $this->repositoryRegistry = $repositoryRegistry;

        return $this;
    }

    public function addRepository(RecordRepository $repository): self
    {
        $this->repositoryRegistry->add($repository);

        return $this;
    }

    public function getExpressionBuilder(): ExpressionBuilder
    {
        return $this->expressionBuilder;
    }

    public function getSchema(): Schema
    {
        return $this->schema;
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
