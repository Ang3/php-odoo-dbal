<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests;

use Ang3\Component\Odoo\Client;
use Ang3\Component\Odoo\DBAL\Configuration;
use Ang3\Component\Odoo\DBAL\Query\Enum\QueryMethod;
use Ang3\Component\Odoo\DBAL\Query\Expression\ExpressionBuilderInterface;
use Ang3\Component\Odoo\DBAL\Query\Factory\QueryFactoryInterface;
use Ang3\Component\Odoo\DBAL\Query\Factory\ResultFactoryInterface;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\Query\QueryInterface;
use Ang3\Component\Odoo\DBAL\RecordManager;
use Ang3\Component\Odoo\DBAL\Repository\RecordRepositoryInterface;
use Ang3\Component\Odoo\DBAL\Repository\RepositoryRegistry;
use Ang3\Component\Odoo\DBAL\Schema\SchemaInterface;
use Ang3\Component\Odoo\DBAL\Types\TypeConverterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\RecordManager
 *
 * @internal
 */
final class RecordManagerTest extends TestCase
{
    private RecordManager $recordManager;
    private MockObject $client;
    private MockObject $configuration;
    private MockObject $schema;
    private MockObject $repositoryRegistry;
    private MockObject $typeConverter;
    private MockObject $queryFactory;
    private MockObject $resultFactory;
    private MockObject $expressionBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(Client::class);
        $this->configuration = $this->createMock(Configuration::class);
        $this->schema = $this->createMock(SchemaInterface::class);
        $this->repositoryRegistry = $this->createMock(RepositoryRegistry::class);
        $this->typeConverter = $this->createMock(TypeConverterInterface::class);
        $this->queryFactory = $this->createMock(QueryFactoryInterface::class);
        $this->resultFactory = $this->createMock(ResultFactoryInterface::class);
        $this->expressionBuilder = $this->createMock(ExpressionBuilderInterface::class);
        $this->recordManager = new RecordManager($this->client, $this->configuration, $this->typeConverter, $this->queryFactory, $this->resultFactory, $this->expressionBuilder);

        // Override internal dependencies
        (new \ReflectionProperty(RecordManager::class, 'schema'))->setValue($this->recordManager, $this->schema);
        (new \ReflectionProperty(RecordManager::class, 'repositoryRegistry'))->setValue($this->recordManager, $this->repositoryRegistry);
    }

    /**
     * @covers ::find
     *
     * @testWith [3, "model_name", ["selected_field"]]
     */
    public function testFind(int $id, string $modelName, array $fields): void
    {
        $repository = $this->createMock(RecordRepositoryInterface::class);
        $this->repositoryRegistry->expects(static::once())->method('get')->with($modelName)->willReturn($repository);
        $repository->expects(static::once())->method('find')->with($id, $fields)->willReturn($result = [
            'foo' => 'bar',
        ]);

        static::assertSame($result, $this->recordManager->find($modelName, $id, $fields));
    }

    /**
     * @covers ::createQueryBuilder
     *
     * @testWith ["model_name"]
     */
    public function testCreateQueryBuilder(string $modelName): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $this->queryFactory->expects(static::once())->method('createQueryBuilder')->with($modelName)->willReturn($queryBuilder);
        static::assertSame($queryBuilder, $this->recordManager->createQueryBuilder($modelName));
    }

    /**
     * @covers ::executeQuery
     *
     * @testWith ["name", "method", {"foo": "bar"}, {}]
     *           ["name", "method", {"foo": "bar"}, {"qux": "lux"}]
     */
    public function testExecuteQuery(string $name, string $method, array $parameters = [], array $options = []): void
    {
        $query = $this->createMock(QueryInterface::class);
        $query->expects(static::once())->method('getName')->willReturn($name);
        $query->expects(static::once())->method('getMethod')->willReturn($method);
        $query->expects(static::once())->method('getParameters')->willReturn($parameters);
        $query->expects(static::once())->method('getOptions')->willReturn($options);

        if ($options) {
            $this->client->expects(static::once())->method('executeKw')->with($name, $method, $parameters, $options)->willReturn($result = 'foo');
        } else {
            $this->client->expects(static::once())->method('executeKw')->with($name, $method, $parameters)->willReturn($result = 'foo');
        }

        static::assertSame($result, $this->recordManager->executeQuery($query));
    }

    /**
     * @covers ::getRepository
     *
     * @testWith ["model_name"]
     */
    public function testGetRepository(string $modelName): void
    {
        $repository = $this->createMock(RecordRepositoryInterface::class);
        $this->repositoryRegistry->expects(static::once())->method('get')->with($modelName)->willReturn($repository);

        static::assertSame($repository, $this->recordManager->getRepository($modelName));
    }

    /**
     * @internal
     */
    protected static function provideCreateOrmQueryCases(): iterable
    {
        return [
            ['model_name', QueryMethod::Insert->value],
            ['model_name', QueryMethod::Update->value],
            ['model_name', QueryMethod::Read->value],
            ['model_name', QueryMethod::Search->value],
            ['model_name', QueryMethod::SearchAndCount->value],
            ['model_name', QueryMethod::SearchAndRead->value],
            ['model_name', QueryMethod::Delete->value],
        ];
    }
}
