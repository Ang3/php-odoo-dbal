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
use Ang3\Component\Odoo\DBAL\Query\Expression\DataNormalizer;
use Ang3\Component\Odoo\DBAL\Query\Expression\ExpressionBuilder;
use Ang3\Component\Odoo\DBAL\Query\Expression\ExpressionBuilderInterface;
use Ang3\Component\Odoo\DBAL\Query\NativeQuery;
use Ang3\Component\Odoo\DBAL\Query\OrmQuery;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\Query\QueryInterface;
use Ang3\Component\Odoo\DBAL\Repository\RecordRepository;
use Ang3\Component\Odoo\DBAL\Repository\RecordRepositoryInterface;
use Ang3\Component\Odoo\DBAL\Repository\RepositoryRegistry;
use Ang3\Component\Odoo\DBAL\Repository\RepositoryRegistryInterface;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\MetadataFactory;
use Ang3\Component\Odoo\DBAL\Schema\Schema;
use Ang3\Component\Odoo\DBAL\Types\TypeConverter;
use Ang3\Component\Odoo\DBAL\Types\TypeRegistry;
use Ang3\Component\Odoo\DBAL\Types\TypeRegistryInterface;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
class RecordManager
{
    private Configuration $configuration;
    private Schema $schema;
    private TypeRegistryInterface $typeRegistry;
    private RepositoryRegistryInterface $repositoryRegistry;
    private ExpressionBuilderInterface $expressionBuilder;
    private TypeConverter $typeConverter;
    private DataNormalizer $dataNormalizer;
    private MetadataFactory $metadataFactory;

    public function __construct(
        private readonly Client $client,
        Configuration $configuration = null,
        TypeRegistryInterface $typeRegistry = null,
        RepositoryRegistryInterface $repositoryRegistry = null,
        ExpressionBuilderInterface $expressionBuilder = null,
        DataNormalizer $dataNormalizer = null,
        MetadataFactory $metadataFactory = null,
    ) {
        $this->configuration = $configuration ?: new Configuration();
        $this->schema = new Schema($this);
        $this->typeRegistry = $typeRegistry ?: new TypeRegistry();
        $this->setRepositoryRegistry($repositoryRegistry);
        $this->expressionBuilder = $expressionBuilder ?: new ExpressionBuilder();
        $this->typeConverter = new TypeConverter();
        $this->dataNormalizer = $dataNormalizer ?: new DataNormalizer();
        $this->metadataFactory = $metadataFactory ?: new MetadataFactory($this);
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

    public function getRepositoryRegistry(): RepositoryRegistryInterface
    {
        return $this->repositoryRegistry;
    }

    public function setRepositoryRegistry(RepositoryRegistryInterface $repositoryRegistry = null): self
    {
        $this->repositoryRegistry = $repositoryRegistry ?: new RepositoryRegistry($this);
        $this->repositoryRegistry->setRecordManager($this);

        return $this;
    }

    public function addRepository(RecordRepository $repository): self
    {
        $this->repositoryRegistry->add($repository);

        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getTypeRegistry(): TypeRegistryInterface
    {
        return $this->typeRegistry;
    }

    public function getTypeConverter(): TypeConverter
    {
        return $this->typeConverter;
    }

    public function getDataNormalizer(): DataNormalizer
    {
        return $this->dataNormalizer;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getSchema(): Schema
    {
        return $this->schema;
    }

    public function getMetadataFactory(): MetadataFactory
    {
        return $this->metadataFactory;
    }

    public function getExpressionBuilder(): ExpressionBuilderInterface
    {
        return $this->expressionBuilder;
    }
}
