<?php

namespace Ang3\Component\Odoo\DBAL\Tests;

use Ang3\Component\Odoo\Client;
use Ang3\Component\Odoo\DBAL\Query\Enum\OrmQueryMethod;
use Ang3\Component\Odoo\DBAL\Query\Expression\ExpressionBuilder;
use Ang3\Component\Odoo\DBAL\Query\Expression\ExpressionBuilderInterface;
use Ang3\Component\Odoo\DBAL\Query\NativeQuery;
use Ang3\Component\Odoo\DBAL\Query\OrmQuery;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\Query\QueryException;
use Ang3\Component\Odoo\DBAL\Query\QueryInterface;
use Ang3\Component\Odoo\DBAL\RecordManager;
use Ang3\Component\Odoo\DBAL\Repository\RecordRepositoryInterface;
use Ang3\Component\Odoo\DBAL\Repository\RepositoryRegistry;
use Ang3\Component\Odoo\DBAL\Repository\RepositoryRegistryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\RecordManager
 */
class RecordManagerTest extends TestCase
{
    private RecordManager $recordManager;
    private MockObject $client;
    private MockObject $repositoryRegistry;
    private MockObject $expressionBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(Client::class);
        $this->repositoryRegistry = $this->createMock(RepositoryRegistryInterface::class);
        $this->expressionBuilder = $this->createMock(ExpressionBuilderInterface::class);
        $this->recordManager = new RecordManager($this->client, $this->repositoryRegistry, $this->expressionBuilder);
    }

    /**
     * @covers ::find
     * @testWith [3, "model_name", ["selected_field"]]
     */
    public function testFind(int $id, string $modelName, array $fields): void
    {
        $repository = $this->createMock(RecordRepositoryInterface::class);
        $this->repositoryRegistry->expects($this->once())->method('get')->with($modelName)->willReturn($repository);
        $repository->expects($this->once())->method('find')->with($id, $fields)->willReturn($result = [
            'foo' => 'bar'
        ]);

        self::assertEquals($result, $this->recordManager->find($modelName, $id, $fields));
    }

    /**
     * @covers ::createQueryBuilder
     * @testWith ["model_name"]
     */
    public function testCreateQueryBuilder(string $modelName): void
    {
        self::assertEquals(new QueryBuilder($this->recordManager, $modelName), $this->recordManager->createQueryBuilder($modelName));
    }

    /**
     * @covers ::createOrmQuery
     *
     * @dataProvider provideOrmQueryParameters
     */
    public function testCreateOrmQuery(string $name, string $method): void
    {
        self::assertEquals(new OrmQuery($this->recordManager, $name, $method), $this->recordManager->createOrmQuery($name, $method));
    }

    /**
     * @covers ::createOrmQuery
     * @testWith ["model_name", "invalid_method"]
     */
    public function testCreateOrmQueryWithInvalidMethod(string $name, string $invalidMethod): void
    {
        $this->expectException(QueryException::class);
        $this->recordManager->createOrmQuery($name, $invalidMethod);
    }

    /**
     * @covers ::createNativeQuery
     * @testWith ["model_name", "method_name"]
     */
    public function testCreateNativeQuery(string $name, string $method): void
    {
        self::assertEquals(new NativeQuery($this->recordManager, $name, $method), $this->recordManager->createNativeQuery($name, $method));
    }

    /**
     * @covers ::executeQuery
     * @testWith ["name", "method", {"foo": "bar"}, {}]
     *           ["name", "method", {"foo": "bar"}, {"qux": "lux"}]
     */
    public function testExecuteQuery(string $name, string $method, array $parameters = [], array $options = []): void
    {
        $query = $this->createMock(QueryInterface::class);
        $query->expects($this->once())->method('getName')->willReturn($name);
        $query->expects($this->once())->method('getMethod')->willReturn($method);
        $query->expects($this->once())->method('getParameters')->willReturn($parameters);
        $query->expects($this->once())->method('getOptions')->willReturn($options);

        if ($options) {
            $this->client->expects($this->once())->method('executeKw')->with($name, $method, $parameters, $options)->willReturn($result = 'foo');
        } else {
            $this->client->expects($this->once())->method('executeKw')->with($name, $method, $parameters)->willReturn($result = 'foo');
        }

        self::assertEquals($result, $this->recordManager->executeQuery($query));
    }

    /**
     * @covers ::getRepository
     * @testWith ["model_name"]
     */
    public function testGetRepository(string $modelName): void
    {
        $repository = $this->createMock(RecordRepositoryInterface::class);
        $this->repositoryRegistry->expects($this->once())->method('get')->with($modelName)->willReturn($repository);

        self::assertEquals($repository, $this->recordManager->getRepository($modelName));
    }

    public static function provideOrmQueryParameters(): iterable
    {
        return [
            [ 'model_name', OrmQueryMethod::Create->value ],
            [ 'model_name', OrmQueryMethod::Write->value ],
            [ 'model_name', OrmQueryMethod::Read->value ],
            [ 'model_name', OrmQueryMethod::Search->value ],
            [ 'model_name', OrmQueryMethod::SearchAndCount->value ],
            [ 'model_name', OrmQueryMethod::SearchAndRead->value ],
            [ 'model_name', OrmQueryMethod::Unlink->value ],
        ];
    }
}