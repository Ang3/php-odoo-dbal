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
use Ang3\Component\Odoo\DBAL\Schema\Model;
use Ang3\Component\Odoo\DBAL\Schema\Schema;
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
        $schema = $this->createMock(Schema::class);
        $this->recordManager->expects(self::once())->method('getSchema')->willReturn($schema);

        $expectedResult = $this->createMock(Model::class);
        $schema->expects(self::once())->method('getModel')->with($this->modelName)->willReturn($expectedResult);

        self::assertSame($expectedResult, $this->recordRepository->getMetadata());
    }

    /**
     * @covers ::insert
     * @testWith [{"foo": "bar", "qux": "lux"}]
     */
    public function testInsert(array $data): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::once())->method('select')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('insert')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('setValues')->with($data)->willReturn($queryBuilder);
        $queryBuilder->expects(self::never())->method('update');
        $queryBuilder->expects(self::never())->method('delete');
        $queryBuilder->expects(self::never())->method('where');
        $queryBuilder->expects(self::never())->method('andWhere');
        $queryBuilder->expects(self::never())->method('orWhere');
        $queryBuilder->expects(self::never())->method('setOrders');
        $queryBuilder->expects(self::never())->method('orderBy');
        $queryBuilder->expects(self::never())->method('addOrderBy');
        $queryBuilder->expects(self::never())->method('setFirstResult');
        $queryBuilder->expects(self::never())->method('setMaxResults');
        $this->recordManager->expects(self::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(self::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(self::once())->method('execute')->with()->willReturn((string) ($result = 3));

        self::assertSame($result, $this->recordRepository->insert($data));
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
     * @testWith [[1, 2, 3], {"foo": "bar", "qux": "lux"}]
     */
    public function testUpdate(array $ids, array $data): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::once())->method('select')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('update')->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('setIds')->with($ids)->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('setValues')->with($data)->willReturn($queryBuilder);
        $queryBuilder->expects(self::never())->method('insert');
        $queryBuilder->expects(self::never())->method('delete');
        $queryBuilder->expects(self::never())->method('where');
        $queryBuilder->expects(self::never())->method('andWhere');
        $queryBuilder->expects(self::never())->method('orWhere');
        $queryBuilder->expects(self::never())->method('setOrders');
        $queryBuilder->expects(self::never())->method('orderBy');
        $queryBuilder->expects(self::never())->method('addOrderBy');
        $queryBuilder->expects(self::never())->method('setFirstResult');
        $queryBuilder->expects(self::never())->method('setMaxResults');
        $this->recordManager->expects(self::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(self::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(self::once())->method('execute')->with();

        $this->recordRepository->update($ids, $data);
    }

    /**
     * @covers ::update
     */
    public function testUpdateWithEmptyIds(): void
    {
        $this->recordManager->expects(self::never())->method('createQueryBuilder');
        $this->recordRepository->update([]);
    }

    /**
     * @covers ::update
     * @testWith [[1, 2, 3]]
     */
    public function testUpdateWithEmptyData(array $ids): void
    {
        $this->recordManager->expects(self::never())->method('createQueryBuilder');
        $this->recordRepository->update($ids);
    }

    /**
     * @covers ::delete
     * @testWith [[1, 2, 3]]
     */
    public function testDelete(array $ids): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::once())->method('select')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('delete')->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('setIds')->with($ids)->willReturn($queryBuilder);
        $queryBuilder->expects(self::never())->method('insert');
        $queryBuilder->expects(self::never())->method('setValues');
        $queryBuilder->expects(self::never())->method('update');
        $queryBuilder->expects(self::never())->method('where');
        $queryBuilder->expects(self::never())->method('andWhere');
        $queryBuilder->expects(self::never())->method('orWhere');
        $queryBuilder->expects(self::never())->method('setOrders');
        $queryBuilder->expects(self::never())->method('orderBy');
        $queryBuilder->expects(self::never())->method('addOrderBy');
        $queryBuilder->expects(self::never())->method('setFirstResult');
        $queryBuilder->expects(self::never())->method('setMaxResults');
        $this->recordManager->expects(self::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(self::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(self::once())->method('execute')->with();

        $this->recordRepository->delete($ids);
    }

    /**
     * @covers ::delete
     */
    public function testDeleteWithEmptyIds(): void
    {
        $this->recordManager->expects(self::never())->method('createQueryBuilder');
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
        $queryBuilder->expects(self::once())->method('select')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('search')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('where')->with($criteria ? CompositeDomain::criteria($criteria) : null)->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('setOrders')->with($orders)->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('setMaxResults')->with(1)->willReturn($queryBuilder);
        $queryBuilder->expects(self::never())->method('insert');
        $queryBuilder->expects(self::never())->method('setValues');
        $queryBuilder->expects(self::never())->method('update');
        $queryBuilder->expects(self::never())->method('delete');
        $queryBuilder->expects(self::never())->method('andWhere');
        $queryBuilder->expects(self::never())->method('orWhere');
        $queryBuilder->expects(self::never())->method('orderBy');
        $queryBuilder->expects(self::never())->method('addOrderBy');
        $queryBuilder->expects(self::never())->method('setFirstResult');
        $this->recordManager->expects(self::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(self::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(self::once())->method('getOneOrNullScalarResult')->willReturn($result = 3);

        self::assertSame($result, $this->recordRepository->searchOne($criteria, $orders));
    }

    /**
     * @covers ::searchAll
     *
     * @dataProvider provideFullParameters
     */
    public function testSearchAll(?array $criteria = [], array $fields = [], array $orders = [], int $limit = null, int $offset = null): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::once())->method('select')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('search')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('where')->with(null)->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('setOrders')->with($orders)->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('setFirstResult')->with($offset)->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('setMaxResults')->with($limit)->willReturn($queryBuilder);
        $queryBuilder->expects(self::never())->method('insert');
        $queryBuilder->expects(self::never())->method('setValues');
        $queryBuilder->expects(self::never())->method('update');
        $queryBuilder->expects(self::never())->method('delete');
        $queryBuilder->expects(self::never())->method('andWhere');
        $queryBuilder->expects(self::never())->method('orWhere');
        $queryBuilder->expects(self::never())->method('orderBy');
        $queryBuilder->expects(self::never())->method('addOrderBy');
        $this->recordManager->expects(self::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(self::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(self::once())->method('getScalarResult')->willReturn($result = [1, 2, 3]);

        self::assertSame($result, $this->recordRepository->searchAll($orders, $limit, $offset));
    }

    /**
     * @covers ::search
     *
     * @dataProvider provideFullParameters
     */
    public function testSearch(?array $criteria = [], array $fields = [], array $orders = [], int $limit = null, int $offset = null): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::once())->method('select')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('search')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('where')->with($criteria ? CompositeDomain::criteria($criteria) : null)->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('setOrders')->with($orders)->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('setFirstResult')->with($offset)->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('setMaxResults')->with($limit)->willReturn($queryBuilder);
        $queryBuilder->expects(self::never())->method('insert');
        $queryBuilder->expects(self::never())->method('setValues');
        $queryBuilder->expects(self::never())->method('update');
        $queryBuilder->expects(self::never())->method('delete');
        $queryBuilder->expects(self::never())->method('andWhere');
        $queryBuilder->expects(self::never())->method('orWhere');
        $queryBuilder->expects(self::never())->method('orderBy');
        $queryBuilder->expects(self::never())->method('addOrderBy');
        $this->recordManager->expects(self::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(self::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(self::once())->method('getScalarResult')->willReturn($result = [1, 2, 3]);

        self::assertSame($result, $this->recordRepository->search($criteria, $orders, $limit, $offset));
    }

    /**
     * @covers ::read
     * @testWith [3, ["selected_field"]]
     */
    public function testRead(int $id, array $fields): void
    {
        $queryBuilder = $this->prepareSearchQueryBuilder($this->recordRepository->expr()->eq('id', $id), $fields, [], 1);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(self::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(self::once())->method('getResult')->willReturn([
            $result = [
                'foo' => 'bar',
            ],
            [
                'qux' => 'lux',
            ],
        ]);

        self::assertSame($result, $this->recordRepository->read($id, $fields));
    }

    /**
     * @covers ::read
     * @testWith [3, ["selected_field"]]
     */
    public function testReadWithNoResult(int $id, array $fields): void
    {
        $this->expectException(RecordNotFoundException::class);
        $queryBuilder = $this->prepareSearchQueryBuilder($this->recordRepository->expr()->eq('id', $id), $fields, [], 1);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(self::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(self::once())->method('getResult')->willReturn([]);

        $this->recordRepository->read($id, $fields);
    }

    /**
     * @covers ::find
     * @testWith [3, ["selected_field"]]
     */
    public function testFind(int $id, array $fields): void
    {
        $queryBuilder = $this->prepareSearchQueryBuilder($this->recordRepository->expr()->eq('id', $id), $fields, [], 1);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(self::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(self::once())->method('getResult')->willReturn([
            $result = [
                'foo' => 'bar',
            ],
            [
                'qux' => 'lux',
            ],
        ]);

        self::assertSame($result, $this->recordRepository->find($id, $fields));
    }

    /**
     * @covers ::find
     * @testWith [3, ["selected_field"]]
     */
    public function testFindWithNoResult(int $id, array $fields): void
    {
        $queryBuilder = $this->prepareSearchQueryBuilder($this->recordRepository->expr()->eq('id', $id), $fields, [], 1);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(self::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(self::once())->method('getResult')->willReturn($result = []);

        self::assertNull($this->recordRepository->find($id, $fields));
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
        $queryBuilder->expects(self::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(self::once())->method('getResult')->willReturn([
            $result = [
                'foo' => 'bar',
            ],
            [
                'qux' => 'lux',
            ],
        ]);

        self::assertSame($result, $this->recordRepository->findOneBy($criteria, $fields, $orders, $offset));
    }

    /**
     * @covers ::findOneBy
     *
     * @dataProvider provideFullParameters
     */
    public function testPrepare(?array $criteria = [], array $fields = [], array $orders = [], int $limit = null, int $offset = null): void
    {
        $queryBuilder = $this->prepareSearchQueryBuilder($criteria, $fields, $orders, $limit, $offset);

        self::assertSame($queryBuilder, $this->recordRepository->prepare($criteria, $fields, $orders, $limit, $offset));
    }

    /**
     * @internal
     */
    private function prepareSearchQueryBuilder(array|DomainInterface $criteria = null, array $fields = [], array $orders = [], int $limit = null, int $offset = null): MockObject
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::exactly(2))->method('select')->withConsecutive([null], [$fields])->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('where')->with($this->recordRepository->normalizeCriteria($criteria))->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('setOrders')->with($orders)->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('setFirstResult')->with($offset)->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('setMaxResults')->with($limit)->willReturn($queryBuilder);
        $queryBuilder->expects(self::never())->method('insert');
        $queryBuilder->expects(self::never())->method('search');
        $queryBuilder->expects(self::never())->method('update');
        $queryBuilder->expects(self::never())->method('setValues');
        $queryBuilder->expects(self::never())->method('delete');
        $queryBuilder->expects(self::never())->method('andWhere');
        $queryBuilder->expects(self::never())->method('orWhere');
        $queryBuilder->expects(self::never())->method('orderBy');
        $queryBuilder->expects(self::never())->method('addOrderBy');
        $this->recordManager->expects(self::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        return $queryBuilder;
    }

    /**
     * @covers ::exists
     * @testWith [3]
     */
    public function testExists(int $id): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::once())->method('select')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('where')->with($this->recordRepository->expr()->eq('id', $id))->willReturn($queryBuilder);
        $queryBuilder->expects(self::never())->method('insert');
        $queryBuilder->expects(self::never())->method('search');
        $queryBuilder->expects(self::never())->method('update');
        $queryBuilder->expects(self::never())->method('setValues');
        $queryBuilder->expects(self::never())->method('delete');
        $queryBuilder->expects(self::never())->method('andWhere');
        $queryBuilder->expects(self::never())->method('orWhere');
        $queryBuilder->expects(self::never())->method('setOrders');
        $queryBuilder->expects(self::never())->method('orderBy');
        $queryBuilder->expects(self::never())->method('addOrderBy');
        $queryBuilder->expects(self::never())->method('setFirstResult');
        $queryBuilder->expects(self::never())->method('setMaxResults');
        $this->recordManager->expects(self::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(self::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(self::once())->method('count')->willReturn(1);

        self::assertTrue($this->recordRepository->exists($id));
    }

    /**
     * @covers ::exists
     * @testWith [3]
     */
    public function testExistsWithNotResult(int $id): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::once())->method('select')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('where')->with($this->recordRepository->expr()->eq('id', $id))->willReturn($queryBuilder);
        $queryBuilder->expects(self::never())->method('insert');
        $queryBuilder->expects(self::never())->method('search');
        $queryBuilder->expects(self::never())->method('update');
        $queryBuilder->expects(self::never())->method('setValues');
        $queryBuilder->expects(self::never())->method('delete');
        $queryBuilder->expects(self::never())->method('andWhere');
        $queryBuilder->expects(self::never())->method('orWhere');
        $queryBuilder->expects(self::never())->method('setOrders');
        $queryBuilder->expects(self::never())->method('orderBy');
        $queryBuilder->expects(self::never())->method('addOrderBy');
        $queryBuilder->expects(self::never())->method('setFirstResult');
        $queryBuilder->expects(self::never())->method('setMaxResults');
        $this->recordManager->expects(self::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(self::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(self::once())->method('count')->willReturn(0);

        self::assertFalse($this->recordRepository->exists($id));
    }

    /**
     * @covers ::countAll
     */
    public function testCountAll(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::once())->method('select')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('where')->with(null)->willReturn($queryBuilder);
        $queryBuilder->expects(self::never())->method('insert');
        $queryBuilder->expects(self::never())->method('search');
        $queryBuilder->expects(self::never())->method('update');
        $queryBuilder->expects(self::never())->method('setValues');
        $queryBuilder->expects(self::never())->method('delete');
        $queryBuilder->expects(self::never())->method('andWhere');
        $queryBuilder->expects(self::never())->method('orWhere');
        $queryBuilder->expects(self::never())->method('setOrders');
        $queryBuilder->expects(self::never())->method('orderBy');
        $queryBuilder->expects(self::never())->method('addOrderBy');
        $queryBuilder->expects(self::never())->method('setFirstResult');
        $queryBuilder->expects(self::never())->method('setMaxResults');
        $this->recordManager->expects(self::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(self::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(self::once())->method('count')->willReturn($expectedResult = 3);

        self::assertSame($expectedResult, $this->recordRepository->countAll());
    }

    /**
     * @covers ::count
     *
     * @dataProvider provideFullParameters
     */
    public function testCount(?array $criteria = []): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects(self::once())->method('select')->with()->willReturn($queryBuilder);
        $queryBuilder->expects(self::once())->method('where')->with($this->recordRepository->normalizeCriteria($criteria))->willReturn($queryBuilder);
        $queryBuilder->expects(self::never())->method('insert');
        $queryBuilder->expects(self::never())->method('search');
        $queryBuilder->expects(self::never())->method('update');
        $queryBuilder->expects(self::never())->method('setValues');
        $queryBuilder->expects(self::never())->method('delete');
        $queryBuilder->expects(self::never())->method('andWhere');
        $queryBuilder->expects(self::never())->method('orWhere');
        $queryBuilder->expects(self::never())->method('setOrders');
        $queryBuilder->expects(self::never())->method('orderBy');
        $queryBuilder->expects(self::never())->method('addOrderBy');
        $queryBuilder->expects(self::never())->method('setFirstResult');
        $queryBuilder->expects(self::never())->method('setMaxResults');

        $this->recordManager->expects(self::once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects(self::once())->method('getQuery')->with()->willReturn($query);
        $query->expects(self::once())->method('count')->willReturn($expectedResult = 3);

        self::assertSame($expectedResult, $this->recordRepository->count($criteria));
    }

    /**
     * @internal
     */
    public static function provideFullParameters(): iterable
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
