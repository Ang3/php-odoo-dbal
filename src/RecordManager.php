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
use Ang3\Component\Odoo\DBAL\Query\Enum\OrmQueryMethod;
use Ang3\Component\Odoo\DBAL\Query\Expression\ExpressionBuilder;
use Ang3\Component\Odoo\DBAL\Query\Expression\ExpressionBuilderInterface;
use Ang3\Component\Odoo\DBAL\Query\NativeQuery;
use Ang3\Component\Odoo\DBAL\Query\Normalizer\ResultNormalizer;
use Ang3\Component\Odoo\DBAL\Query\Normalizer\ResultNormalizerInterface;
use Ang3\Component\Odoo\DBAL\Query\OrmQuery;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\Query\QueryInterface;
use Ang3\Component\Odoo\DBAL\Repository\RecordRepositoryInterface;
use Ang3\Component\Odoo\DBAL\Repository\RepositoryRegistry;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;
use Ang3\Component\Odoo\DBAL\Schema\Schema;
use Ang3\Component\Odoo\DBAL\Schema\SchemaInterface;
use Ang3\Component\Odoo\DBAL\Types\TypeConverter;
use Ang3\Component\Odoo\DBAL\Types\TypeConverterInterface;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
class RecordManager
{
    private readonly Configuration $configuration;
    private SchemaInterface $schema;
    private RepositoryRegistry $repositoryRegistry;
    private TypeConverterInterface $typeConverter;
    private ResultNormalizerInterface $resultNormalizer;
    private ExpressionBuilderInterface $expressionBuilder;

    public function __construct(
        private readonly Client $client,
        Configuration $configuration = null,
        TypeConverterInterface $typeConverter = null,
        ExpressionBuilderInterface $expressionBuilder = null
    ) {
        $this->configuration = $configuration ?: new Configuration();
        $this->schema = new Schema($this);
        $this->repositoryRegistry = new RepositoryRegistry($this);
        $this->typeConverter = $typeConverter ?: new TypeConverter();
        $this->resultNormalizer = new ResultNormalizer($this->typeConverter);
        $this->expressionBuilder = $expressionBuilder ?: new ExpressionBuilder();
    }

    public function find(string $modelName, int $id, ?array $fields = []): ?array
    {
        return $this->getRepository($modelName)->find($id, $fields);
    }

    public function createQueryBuilder(string $modelName): QueryBuilder
    {
        return new QueryBuilder($this, $modelName);
    }

    public function createOrmQuery(string $modelName, OrmQueryMethod $method): OrmQuery
    {
        return new OrmQuery($this, $modelName, $method->value);
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

    public function normalizeResult(ModelMetadata|string $model, array $payload, array $context = []): array
    {
        $model = $model instanceof ModelMetadata ? $model : $this->schema->getModel($model);

        return $this->resultNormalizer->normalize($model, $payload, $context);
    }

    public function getRepository(string $modelName): RecordRepositoryInterface
    {
        return $this->repositoryRegistry->get($modelName);
    }

    public function addRepository(RecordRepositoryInterface $repository): self
    {
        $this->repositoryRegistry->add($repository);
        $repository->setRecordManager($this);

        return $this;
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

    public function getRepositoryRegistry(): RepositoryRegistry
    {
        return $this->repositoryRegistry;
    }

    public function getTypeConverter(): TypeConverterInterface
    {
        return $this->typeConverter;
    }

    public function getResultNormalizer(): ResultNormalizerInterface
    {
        return $this->resultNormalizer;
    }

    public function getExpressionBuilder(): ExpressionBuilderInterface
    {
        return $this->expressionBuilder;
    }
}
