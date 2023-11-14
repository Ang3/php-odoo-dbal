<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Repository;

use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\CompositeDomain;
use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\DomainInterface;
use Ang3\Component\Odoo\DBAL\Query\OrmQuery;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\RecordManager;
use Ang3\Component\Odoo\DBAL\Repository\RecordNotFoundException;
use Ang3\Component\Odoo\DBAL\Repository\RecordRepository;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;
use Ang3\Component\Odoo\DBAL\Schema\SchemaInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Repository\RecordRepository
 *
 * @internal
 */
final class RecordRepositoryTest extends TestCase
{
    private RecordRepository $recordRepository;
    private MockObject $recordManager;
    private string $modelName = 'res.company';

    protected function setUp(): void
    {
        parent::setUp();
        $this->recordManager = $this->createMock(RecordManager::class);
        $this->recordRepository = new RecordRepository($this->recordManager, $this->modelName);
    }

    /**
     * @covers ::getMetadata
     */
    public function testGetMetadata(): void
    {
        $schema = $this->createMock(SchemaInterface::class);
        $this->recordManager->expects(static::once())->method('getSchema')->willReturn($schema);

        $expectedResult = $this->createMock(ModelMetadata::class);
        $schema->expects(static::once())->method('getModel')->with($this->modelName)->willReturn($expectedResult);

        static::assertEquals($expectedResult, $this->recordRepository->getMetadata());
    }

    /**
     * @covers ::insert
     *
     * @testWith [{"foo": "bar", "qux": "lux"}]
     */
    public function testInsert(array $data): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(static::never())->method('select');
        $queryBuilder->expects(static::once())->method('insert')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('setValues')->with($data)->willReturn($queryBuilder);
        $queryBuilder->expects(static::never())->method('update');
        $queryBuilder->expects(static::never())->method('delete');
        $queryBuilder->expects(static::never())->method('where');
        $queryBuilder->expects(static::never())->method('andWhere');
        $queryBuilder->expects(static::never())->method('orWhere');
        $queryBuilder->expects(static::never())->method('setOrders');
        $queryBuilder->expects(static::never())->method('orderBy');
        $queryBuilder->expects(static::never())->method('addOrderBy');
        $queryBuilder->expects(static::never())->method('setFirstResult');
        $queryBuilder->expects(static::never())->method('setMaxResults');
        $this->recordManager->expects(static::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(static::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(static::once())->method('execute')->with()->willReturn((string) ($result = 3));

        static::assertEquals($result, $this->recordRepository->insert($data));
    }

    /**
     * @covers ::insert
     */
    public function testInsertWithEmptyData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->recordRepository->insert([]);
    }

    /**
     * @covers ::update
     *
     * @testWith [[1, 2, 3], {"foo": "bar", "qux": "lux"}]
     */
    public function testUpdate(array $ids, array $data): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(static::never())->method('select');
        $queryBuilder->expects(static::once())->method('update')->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('setIds')->with($ids)->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('setValues')->with($data)->willReturn($queryBuilder);
        $queryBuilder->expects(static::never())->method('insert');
        $queryBuilder->expects(static::never())->method('delete');
        $queryBuilder->expects(static::never())->method('where');
        $queryBuilder->expects(static::never())->method('andWhere');
        $queryBuilder->expects(static::never())->method('orWhere');
        $queryBuilder->expects(static::never())->method('setOrders');
        $queryBuilder->expects(static::never())->method('orderBy');
        $queryBuilder->expects(static::never())->method('addOrderBy');
        $queryBuilder->expects(static::never())->method('setFirstResult');
        $queryBuilder->expects(static::never())->method('setMaxResults');
        $this->recordManager->expects(static::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(static::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(static::once())->method('execute')->with();

        $this->recordRepository->update($ids, $data);
    }

    /**
     * @covers ::update
     */
    public function testUpdateWithEmptyIds(): void
    {
        $this->recordManager->expects(static::never())->method('createQueryBuilder');
        $this->recordRepository->update([]);
    }

    /**
     * @covers ::update
     *
     * @testWith [[1, 2, 3]]
     */
    public function testUpdateWithEmptyData(array $ids): void
    {
        $this->recordManager->expects(static::never())->method('createQueryBuilder');
        $this->recordRepository->update($ids);
    }

    /**
     * @covers ::delete
     *
     * @testWith [[1, 2, 3]]
     */
    public function testDelete(array $ids): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(static::never())->method('select');
        $queryBuilder->expects(static::once())->method('delete')->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('setIds')->with($ids)->willReturn($queryBuilder);
        $queryBuilder->expects(static::never())->method('insert');
        $queryBuilder->expects(static::never())->method('setValues');
        $queryBuilder->expects(static::never())->method('update');
        $queryBuilder->expects(static::never())->method('where');
        $queryBuilder->expects(static::never())->method('andWhere');
        $queryBuilder->expects(static::never())->method('orWhere');
        $queryBuilder->expects(static::never())->method('setOrders');
        $queryBuilder->expects(static::never())->method('orderBy');
        $queryBuilder->expects(static::never())->method('addOrderBy');
        $queryBuilder->expects(static::never())->method('setFirstResult');
        $queryBuilder->expects(static::never())->method('setMaxResults');
        $this->recordManager->expects(static::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(static::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(static::once())->method('execute')->with();

        $this->recordRepository->delete($ids);
    }

    /**
     * @covers ::delete
     */
    public function testDeleteWithEmptyIds(): void
    {
        $this->recordManager->expects(static::never())->method('createQueryBuilder');
        $this->recordRepository->delete([]);
    }

    /**
     * @covers ::searchOne
     *
     * @dataProvider provideFullParameters
     */
    public function testSearchOne(?array $criteria = [], array $fields = [], array $orders = [], int $limit = null): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(static::never())->method('select');
        $queryBuilder->expects(static::once())->method('search')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('where')->with($criteria ? CompositeDomain::criteria($criteria) : null)->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('setOrders')->with($orders)->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('setMaxResults')->with(1)->willReturn($queryBuilder);
        $queryBuilder->expects(static::never())->method('insert');
        $queryBuilder->expects(static::never())->method('setValues');
        $queryBuilder->expects(static::never())->method('update');
        $queryBuilder->expects(static::never())->method('delete');
        $queryBuilder->expects(static::never())->method('andWhere');
        $queryBuilder->expects(static::never())->method('orWhere');
        $queryBuilder->expects(static::never())->method('orderBy');
        $queryBuilder->expects(static::never())->method('addOrderBy');
        $queryBuilder->expects(static::never())->method('setFirstResult');
        $this->recordManager->expects(static::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(static::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(static::once())->method('getOneOrNullScalarResult')->willReturn($result = 3);

        static::assertEquals($result, $this->recordRepository->searchOne($criteria, $orders));
    }

    /**
     * @covers ::searchAll
     *
     * @dataProvider provideFullParameters
     */
    public function testSearchAll(?array $criteria = [], array $fields = [], array $orders = [], int $limit = null, int $offset = null): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(static::never())->method('select');
        $queryBuilder->expects(static::once())->method('search')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('where')->with(null)->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('setOrders')->with($orders)->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('setFirstResult')->with($offset)->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('setMaxResults')->with($limit)->willReturn($queryBuilder);
        $queryBuilder->expects(static::never())->method('insert');
        $queryBuilder->expects(static::never())->method('setValues');
        $queryBuilder->expects(static::never())->method('update');
        $queryBuilder->expects(static::never())->method('delete');
        $queryBuilder->expects(static::never())->method('andWhere');
        $queryBuilder->expects(static::never())->method('orWhere');
        $queryBuilder->expects(static::never())->method('orderBy');
        $queryBuilder->expects(static::never())->method('addOrderBy');
        $this->recordManager->expects(static::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(static::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(static::once())->method('getScalarResult')->willReturn($result = [1, 2, 3]);

        static::assertEquals($result, $this->recordRepository->searchAll($orders, $limit, $offset));
    }

    /**
     * @covers ::search
     *
     * @dataProvider provideFullParameters
     */
    public function testSearch(?array $criteria = [], array $fields = [], array $orders = [], int $limit = null, int $offset = null): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(static::never())->method('select');
        $queryBuilder->expects(static::once())->method('search')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('where')->with($criteria ? CompositeDomain::criteria($criteria) : null)->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('setOrders')->with($orders)->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('setFirstResult')->with($offset)->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('setMaxResults')->with($limit)->willReturn($queryBuilder);
        $queryBuilder->expects(static::never())->method('insert');
        $queryBuilder->expects(static::never())->method('setValues');
        $queryBuilder->expects(static::never())->method('update');
        $queryBuilder->expects(static::never())->method('delete');
        $queryBuilder->expects(static::never())->method('andWhere');
        $queryBuilder->expects(static::never())->method('orWhere');
        $queryBuilder->expects(static::never())->method('orderBy');
        $queryBuilder->expects(static::never())->method('addOrderBy');
        $this->recordManager->expects(static::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(static::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(static::once())->method('getScalarResult')->willReturn($result = [1, 2, 3]);

        static::assertEquals($result, $this->recordRepository->search($criteria, $orders, $limit, $offset));
    }

    /**
     * @covers ::read
     *
     * @testWith [3, ["selected_field"]]
     */
    public function testRead(int $id, array $fields): void
    {
        $queryBuilder = $this->prepareSearchQueryBuilder($this->recordRepository->expr()->eq('id', $id), $fields, [], 1);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(static::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(static::once())->method('getResult')->willReturn($result = [
            $firstRow = [
                'foo' => 'bar',
            ],
            [
                'qux' => 'lux',
            ],
        ]);

        static::assertEquals($firstRow, $this->recordRepository->read($id, $fields));
    }

    /**
     * @covers ::read
     *
     * @testWith [3, ["selected_field"]]
     */
    public function testReadWithNoResult(int $id, array $fields): void
    {
        $this->expectException(RecordNotFoundException::class);
        $queryBuilder = $this->prepareSearchQueryBuilder($this->recordRepository->expr()->eq('id', $id), $fields, [], 1);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(static::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(static::once())->method('getResult')->willReturn([]);

        $this->recordRepository->read($id, $fields);
    }

    /**
     * @covers ::find
     *
     * @testWith [3, ["selected_field"]]
     */
    public function testFind(int $id, array $fields): void
    {
        $queryBuilder = $this->prepareSearchQueryBuilder($this->recordRepository->expr()->eq('id', $id), $fields, [], 1);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(static::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(static::once())->method('getResult')->willReturn([
            $result = [
                'foo' => 'bar',
            ],
            [
                'qux' => 'lux',
            ],
        ]);

        static::assertEquals($result, $this->recordRepository->find($id, $fields));
    }

    /**
     * @covers ::find
     *
     * @testWith [3, ["selected_field"]]
     */
    public function testFindWithNoResult(int $id, array $fields): void
    {
        $queryBuilder = $this->prepareSearchQueryBuilder($this->recordRepository->expr()->eq('id', $id), $fields, [], 1);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(static::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(static::once())->method('getResult')->willReturn($result = []);

        static::assertNull($this->recordRepository->find($id, $fields));
    }

    /**
     * @covers ::findOneBy
     *
     * @dataProvider provideFullParameters
     */
    public function testFindOneBy(?array $criteria = [], array $fields = [], array $orders = [], int $limit = null, int $offset = null): void
    {
        $queryBuilder = $this->prepareSearchQueryBuilder($criteria, $fields, $orders, 1, $offset);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(static::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(static::once())->method('getResult')->willReturn([
            $result = [
                'foo' => 'bar',
            ],
            [
                'qux' => 'lux',
            ],
        ]);

        static::assertEquals($result, $this->recordRepository->findOneBy($criteria, $fields, $orders, $offset));
    }

    /**
     * @covers ::findOneBy
     *
     * @dataProvider provideFullParameters
     */
    public function testPrepare(?array $criteria = [], array $fields = [], array $orders = [], int $limit = null, int $offset = null): void
    {
        $queryBuilder = $this->prepareSearchQueryBuilder($criteria, $fields, $orders, $limit, $offset);

        static::assertEquals($queryBuilder, $this->recordRepository->prepare($criteria, $fields, $orders, $limit, $offset));
    }

    /**
     * @internal
     */
    private function prepareSearchQueryBuilder(array|DomainInterface $criteria = null, array $fields = [], array $orders = [], int $limit = null, int $offset = null): MockObject
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(static::once())->method('select')->with(array_filter($fields))->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('where')->with($this->recordRepository->normalizeCriteria($criteria))->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('setOrders')->with($orders)->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('setFirstResult')->with($offset)->willReturn($queryBuilder);
        $queryBuilder->expects(static::once())->method('setMaxResults')->with($limit)->willReturn($queryBuilder);
        $queryBuilder->expects(static::never())->method('insert');
        $queryBuilder->expects(static::never())->method('search');
        $queryBuilder->expects(static::never())->method('update');
        $queryBuilder->expects(static::never())->method('setValues');
        $queryBuilder->expects(static::never())->method('delete');
        $queryBuilder->expects(static::never())->method('andWhere');
        $queryBuilder->expects(static::never())->method('orWhere');
        $queryBuilder->expects(static::never())->method('orderBy');
        $queryBuilder->expects(static::never())->method('addOrderBy');
        $this->recordManager->expects(static::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        return $queryBuilder;
    }

    /**
     * @covers ::exists
     *
     * @testWith [3]
     */
    public function testExists(int $id): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(static::never())->method('select');
        $queryBuilder->expects(static::once())->method('where')->with($this->recordRepository->expr()->eq('id', $id))->willReturn($queryBuilder);
        $queryBuilder->expects(static::never())->method('insert');
        $queryBuilder->expects(static::never())->method('search');
        $queryBuilder->expects(static::never())->method('update');
        $queryBuilder->expects(static::never())->method('setValues');
        $queryBuilder->expects(static::never())->method('delete');
        $queryBuilder->expects(static::never())->method('andWhere');
        $queryBuilder->expects(static::never())->method('orWhere');
        $queryBuilder->expects(static::never())->method('setOrders');
        $queryBuilder->expects(static::never())->method('orderBy');
        $queryBuilder->expects(static::never())->method('addOrderBy');
        $queryBuilder->expects(static::never())->method('setFirstResult');
        $queryBuilder->expects(static::never())->method('setMaxResults');
        $this->recordManager->expects(static::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(static::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(static::once())->method('count')->willReturn(1);

        static::assertTrue($this->recordRepository->exists($id));
    }

    /**
     * @covers ::exists
     *
     * @testWith [3]
     */
    public function testExistsWithNotResult(int $id): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(static::never())->method('select');
        $queryBuilder->expects(static::once())->method('where')->with($this->recordRepository->expr()->eq('id', $id))->willReturn($queryBuilder);
        $queryBuilder->expects(static::never())->method('insert');
        $queryBuilder->expects(static::never())->method('search');
        $queryBuilder->expects(static::never())->method('update');
        $queryBuilder->expects(static::never())->method('setValues');
        $queryBuilder->expects(static::never())->method('delete');
        $queryBuilder->expects(static::never())->method('andWhere');
        $queryBuilder->expects(static::never())->method('orWhere');
        $queryBuilder->expects(static::never())->method('setOrders');
        $queryBuilder->expects(static::never())->method('orderBy');
        $queryBuilder->expects(static::never())->method('addOrderBy');
        $queryBuilder->expects(static::never())->method('setFirstResult');
        $queryBuilder->expects(static::never())->method('setMaxResults');
        $this->recordManager->expects(static::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(static::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(static::once())->method('count')->willReturn(0);

        static::assertFalse($this->recordRepository->exists($id));
    }

    /**
     * @covers ::countAll
     */
    public function testCountAll(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(static::never())->method('select');
        $queryBuilder->expects(static::once())->method('where')->with(null)->willReturn($queryBuilder);
        $queryBuilder->expects(static::never())->method('insert');
        $queryBuilder->expects(static::never())->method('search');
        $queryBuilder->expects(static::never())->method('update');
        $queryBuilder->expects(static::never())->method('setValues');
        $queryBuilder->expects(static::never())->method('delete');
        $queryBuilder->expects(static::never())->method('andWhere');
        $queryBuilder->expects(static::never())->method('orWhere');
        $queryBuilder->expects(static::never())->method('setOrders');
        $queryBuilder->expects(static::never())->method('orderBy');
        $queryBuilder->expects(static::never())->method('addOrderBy');
        $queryBuilder->expects(static::never())->method('setFirstResult');
        $queryBuilder->expects(static::never())->method('setMaxResults');
        $this->recordManager->expects(static::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(static::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(static::once())->method('count')->willReturn($expectedResult = 3);

        static::assertEquals($expectedResult, $this->recordRepository->countAll());
    }

    /**
     * @covers ::count
     *
     * @dataProvider provideFullParameters
     */
    public function testCount(?array $criteria = []): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(static::never())->method('select');
        $queryBuilder->expects(static::once())->method('where')->with($this->recordRepository->normalizeCriteria($criteria))->willReturn($queryBuilder);
        $queryBuilder->expects(static::never())->method('insert');
        $queryBuilder->expects(static::never())->method('search');
        $queryBuilder->expects(static::never())->method('update');
        $queryBuilder->expects(static::never())->method('setValues');
        $queryBuilder->expects(static::never())->method('delete');
        $queryBuilder->expects(static::never())->method('andWhere');
        $queryBuilder->expects(static::never())->method('orWhere');
        $queryBuilder->expects(static::never())->method('setOrders');
        $queryBuilder->expects(static::never())->method('orderBy');
        $queryBuilder->expects(static::never())->method('addOrderBy');
        $queryBuilder->expects(static::never())->method('setFirstResult');
        $queryBuilder->expects(static::never())->method('setMaxResults');

        $this->recordManager->expects(static::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(static::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(static::once())->method('count')->willReturn($expectedResult = 3);

        static::assertEquals($expectedResult, $this->recordRepository->count($criteria));
    }

    /**
     * @internal
     */
    protected static function provideFullParameters(): iterable
    {
        return [
            [[], [], [], null, null],
            [['foo' => 'bar'], [], [], null, null],
            [['foo' => 'bar'], ['qux'], [], null, null],
            [['foo' => 'bar'], ['qux'], ['lux' => 'ASC'], null, null],
            [['foo' => 'bar'], ['qux'], ['lux' => 'ASC'], 100, null],
            [['foo' => 'bar'], ['qux'], ['lux' => 'ASC'], 100, 10],
        ];
    }
}
