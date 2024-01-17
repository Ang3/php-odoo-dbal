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
use Ang3\Component\Odoo\DBAL\Query\Expression\ExpressionBuilder;
use Ang3\Component\Odoo\DBAL\Query\Expression\ExpressionBuilderInterface;
use Ang3\Component\Odoo\DBAL\Query\Factory\QueryFactory;
use Ang3\Component\Odoo\DBAL\Query\Factory\QueryFactoryInterface;
use Ang3\Component\Odoo\DBAL\Query\Factory\ResultFactory;
use Ang3\Component\Odoo\DBAL\Query\Factory\ResultFactoryInterface;
use Ang3\Component\Odoo\DBAL\Query\Normalizer\QueryNormalizer;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\Query\QueryInterface;
use Ang3\Component\Odoo\DBAL\Repository\RecordRepositoryInterface;
use Ang3\Component\Odoo\DBAL\Repository\RepositoryRegistry;
use Ang3\Component\Odoo\DBAL\Schema\Schema;
use Ang3\Component\Odoo\DBAL\Schema\SchemaInterface;
use Ang3\Component\Odoo\DBAL\Types\TypeConverter;
use Ang3\Component\Odoo\DBAL\Types\TypeConverterInterface;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
class RecordManager implements RecordManagerInterface
{
    private readonly Configuration $configuration;
    private SchemaInterface $schema;
    private RepositoryRegistry $repositoryRegistry;
    private TypeConverterInterface $typeConverter;
    private QueryFactoryInterface $queryFactory;
    private ResultFactoryInterface $resultFactory;
    private ExpressionBuilderInterface $expressionBuilder;

    public function __construct(
        private readonly Client $client,
        Configuration $configuration = null,
        TypeConverterInterface $typeConverter = null,
        QueryFactoryInterface $queryFactory = null,
        ResultFactoryInterface $resultFactory = null,
        ExpressionBuilderInterface $expressionBuilder = null
    ) {
        $this->configuration = $configuration ?: new Configuration();
        $this->schema = new Schema($this);
        $this->repositoryRegistry = new RepositoryRegistry($this);
        $this->typeConverter = $typeConverter ?: new TypeConverter();
        $this->queryFactory = $queryFactory ?: new QueryFactory($this, new QueryNormalizer($this->schema, $this->typeConverter));
        $this->resultFactory = $resultFactory ?: new ResultFactory($this->schema);
        $this->expressionBuilder = $expressionBuilder ?: new ExpressionBuilder();
    }

    public function create(string $modelName, array $data): int
    {
        return $this->getRepository($modelName)->insert($data);
    }

    public function read(string $modelName, int $id, ?array $fields = []): array
    {
        return $this->getRepository($modelName)->read($id, $fields);
    }

    public function find(string $modelName, int $id, ?array $fields = []): ?array
    {
        return $this->getRepository($modelName)->find($id, $fields);
    }

    public function update(string $modelName, array|int $ids, array $data): void
    {
        $this->getRepository($modelName)->update($ids, $data);
    }

    public function delete(string $modelName, array|int $ids): void
    {
        $this->getRepository($modelName)->delete($ids);
    }

    public function getRepository(string $modelName): RecordRepositoryInterface
    {
        return $this->repositoryRegistry->get($modelName);
    }

    public function executeQuery(QueryInterface $query): mixed
    {
        $options = $query->getOptions();

        if (!$options) {
            return $this->client->executeKw($query->getName(), $query->getMethod(), $query->getParameters());
        }

        return $this->client->executeKw($query->getName(), $query->getMethod(), $query->getParameters(), $options);
    }

    public function createQueryBuilder(string $modelName): QueryBuilder
    {
        return $this->queryFactory->createQueryBuilder($modelName);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getSchema(): SchemaInterface
    {
        return $this->schema;
    }

    public function getRepositories(): RepositoryRegistry
    {
        return $this->repositoryRegistry;
    }

    public function getTypeConverter(): TypeConverterInterface
    {
        return $this->typeConverter;
    }

    public function getQueryFactory(): QueryFactoryInterface
    {
        return $this->queryFactory;
    }

    public function getResultFactory(): ResultFactoryInterface
    {
        return $this->resultFactory;
    }

    public function getExpressionBuilder(): ExpressionBuilderInterface
    {
        return $this->expressionBuilder;
    }
}
