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
use Ang3\Component\Odoo\DBAL\Query\Enum\OrmQueryMethod;
use Ang3\Component\Odoo\DBAL\Query\Expression\ExpressionBuilderInterface;
use Ang3\Component\Odoo\DBAL\Query\NativeQuery;
use Ang3\Component\Odoo\DBAL\Query\OrmQuery;
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
    private MockObject $expressionBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(Client::class);
        $this->configuration = $this->createMock(Configuration::class);
        $this->schema = $this->createMock(SchemaInterface::class);
        $this->repositoryRegistry = $this->createMock(RepositoryRegistry::class);
        $this->typeConverter = $this->createMock(TypeConverterInterface::class);
        $this->expressionBuilder = $this->createMock(ExpressionBuilderInterface::class);
        $this->recordManager = new RecordManager($this->client, $this->configuration, $this->typeConverter);

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
        $this->repositoryRegistry->expects(self::once())->method('get')->with($modelName)->willReturn($repository);
        $repository->expects(self::once())->method('find')->with($id, $fields)->willReturn($result = [
            'foo' => 'bar',
        ]);

        self::assertSame($result, $this->recordManager->find($modelName, $id, $fields));
    }

    /**
     * @covers ::createQueryBuilder
     *
     * @testWith ["model_name"]
     */
    public function testCreateQueryBuilder(string $modelName): void
    {
        $queryBuilder = $this->recordManager->createQueryBuilder($modelName);
        self::assertInstanceOf(QueryBuilder::class, $queryBuilder);
        self::assertSame($modelName, $queryBuilder->getFrom());
    }

    /**
     * @covers ::createOrmQuery
     *
     * @dataProvider provideCreateOrmQueryCases
     */
    public function testCreateOrmQuery(string $name, string $method): void
    {
        $ormQuery = $this->recordManager->createOrmQuery($name, OrmQueryMethod::from($method));
        self::assertInstanceOf(OrmQuery::class, $ormQuery);
        self::assertSame($method, $ormQuery->getMethod());
    }

    /**
     * @covers ::createNativeQuery
     *
     * @testWith ["model_name", "method_name"]
     */
    public function testCreateNativeQuery(string $name, string $method): void
    {
        $nativeQuery = $this->recordManager->createNativeQuery($name, $method);
        self::assertInstanceOf(NativeQuery::class, $nativeQuery);
        self::assertSame($name, $nativeQuery->getName());
        self::assertSame($method, $nativeQuery->getMethod());
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
        $query->expects(self::once())->method('getName')->willReturn($name);
        $query->expects(self::once())->method('getMethod')->willReturn($method);
        $query->expects(self::once())->method('getParameters')->willReturn($parameters);
        $query->expects(self::once())->method('getOptions')->willReturn($options);

        if ($options) {
            $this->client->expects(self::once())->method('executeKw')->with($name, $method, $parameters, $options)->willReturn($result = 'foo');
        } else {
            $this->client->expects(self::once())->method('executeKw')->with($name, $method, $parameters)->willReturn($result = 'foo');
        }

        self::assertSame($result, $this->recordManager->executeQuery($query));
    }

    /**
     * @covers ::getRepository
     *
     * @testWith ["model_name"]
     */
    public function testGetRepository(string $modelName): void
    {
        $repository = $this->createMock(RecordRepositoryInterface::class);
        $this->repositoryRegistry->expects(self::once())->method('get')->with($modelName)->willReturn($repository);

        self::assertSame($repository, $this->recordManager->getRepository($modelName));
    }

    /**
     * @internal
     */
    protected static function provideCreateOrmQueryCases(): iterable
    {
        return [
            ['model_name', OrmQueryMethod::Create->value],
            ['model_name', OrmQueryMethod::Write->value],
            ['model_name', OrmQueryMethod::Read->value],
            ['model_name', OrmQueryMethod::Search->value],
            ['model_name', OrmQueryMethod::SearchAndCount->value],
            ['model_name', OrmQueryMethod::SearchAndRead->value],
            ['model_name', OrmQueryMethod::Unlink->value],
        ];
    }
}
