<?php

namespace Ang3\Component\Odoo\DBAL\Tests\Repository;

use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\CompositeDomain;
use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\DomainInterface;
use Ang3\Component\Odoo\DBAL\Query\NoResultException;
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
 */
class RecordRepositoryTest extends TestCase
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
        $this->recordManager->expects($this->once())->method('getSchema')->willReturn($schema);

        $expectedResult = $this->createMock(Model::class);
        $schema->expects($this->once())->method('getModel')->with($this->modelName)->willReturn($expectedResult);

        self::assertEquals($expectedResult, $this->recordRepository->getMetadata());
    }

    /**
     * @covers ::insert
     */
    public function testInsert(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $this->recordManager->expects($this->once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $data = ['foo' => 'bar', 'qux' => 'lux'];
        $queryBuilder->expects($this->once())->method('select')->with()->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('insert')->with()->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('setValues')->with($data)->willReturn($queryBuilder);
        $queryBuilder->expects($this->never())->method('update');
        $queryBuilder->expects($this->never())->method('delete');
        $queryBuilder->expects($this->never())->method('where');
        $queryBuilder->expects($this->never())->method('andWhere');
        $queryBuilder->expects($this->never())->method('orWhere');
        $queryBuilder->expects($this->never())->method('setOrders');
        $queryBuilder->expects($this->never())->method('orderBy');
        $queryBuilder->expects($this->never())->method('addOrderBy');
        $queryBuilder->expects($this->never())->method('setFirstResult');
        $queryBuilder->expects($this->never())->method('setMaxResults');

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects($this->once())->method('getQuery')->with()->willReturn($query);
        $query->expects($this->once())->method('execute')->with()->willReturn((string) ($result = 3));

        self::assertEquals($result, $this->recordRepository->insert($data));
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
     */
    public function testUpdate(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $this->recordManager->expects($this->once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        list($ids, $data) = [[1, 2, 3], ['foo' => 'bar', 'qux' => 'lux']];
        $queryBuilder->expects($this->once())->method('select')->with()->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('update')->with($ids, $data)->willReturn($queryBuilder);
        $queryBuilder->expects($this->never())->method('insert');
        $queryBuilder->expects($this->never())->method('setValues');
        $queryBuilder->expects($this->never())->method('delete');
        $queryBuilder->expects($this->never())->method('where');
        $queryBuilder->expects($this->never())->method('andWhere');
        $queryBuilder->expects($this->never())->method('orWhere');
        $queryBuilder->expects($this->never())->method('setOrders');
        $queryBuilder->expects($this->never())->method('orderBy');
        $queryBuilder->expects($this->never())->method('addOrderBy');
        $queryBuilder->expects($this->never())->method('setFirstResult');
        $queryBuilder->expects($this->never())->method('setMaxResults');

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects($this->once())->method('getQuery')->with()->willReturn($query);
        $query->expects($this->once())->method('execute')->with();

        $this->recordRepository->update($ids, $data);
    }

    /**
     * @covers ::update
     */
    public function testUpdateWithEmptyIds(): void
    {
        $this->recordManager->expects($this->never())->method('createQueryBuilder');
        $this->recordRepository->update([]);
    }

    /**
     * @covers ::update
     */
    public function testUpdateWithEmptyData(): void
    {
        $this->recordManager->expects($this->never())->method('createQueryBuilder');
        $this->recordRepository->update([1, 2, 3]);
    }

    /**
     * @covers ::delete
     */
    public function testDelete(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $this->recordManager->expects($this->once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        $ids = [1, 2, 3];
        $queryBuilder->expects($this->once())->method('select')->with()->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('delete')->with($ids)->willReturn($queryBuilder);
        $queryBuilder->expects($this->never())->method('insert');
        $queryBuilder->expects($this->never())->method('setValues');
        $queryBuilder->expects($this->never())->method('update');
        $queryBuilder->expects($this->never())->method('where');
        $queryBuilder->expects($this->never())->method('andWhere');
        $queryBuilder->expects($this->never())->method('orWhere');
        $queryBuilder->expects($this->never())->method('setOrders');
        $queryBuilder->expects($this->never())->method('orderBy');
        $queryBuilder->expects($this->never())->method('addOrderBy');
        $queryBuilder->expects($this->never())->method('setFirstResult');
        $queryBuilder->expects($this->never())->method('setMaxResults');

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects($this->once())->method('getQuery')->with()->willReturn($query);
        $query->expects($this->once())->method('execute')->with();

        $this->recordRepository->delete($ids);
    }

    /**
     * @covers ::delete
     */
    public function testDeleteWithEmptyIds(): void
    {
        $this->recordManager->expects($this->never())->method('createQueryBuilder');
        $this->recordRepository->delete([]);
    }

    /**
     * @covers ::searchOne
     */
    public function testSearchOne(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $this->recordManager->expects($this->once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        list($criteria, $orders) = [[], []];
        $queryBuilder->expects($this->once())->method('select')->with()->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('search')->with()->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('where')->with($criteria ? CompositeDomain::criteria($criteria) : null)->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('setOrders')->with($orders)->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('setMaxResults')->with(1)->willReturn($queryBuilder);
        $queryBuilder->expects($this->never())->method('insert');
        $queryBuilder->expects($this->never())->method('setValues');
        $queryBuilder->expects($this->never())->method('update');
        $queryBuilder->expects($this->never())->method('delete');
        $queryBuilder->expects($this->never())->method('andWhere');
        $queryBuilder->expects($this->never())->method('orWhere');
        $queryBuilder->expects($this->never())->method('orderBy');
        $queryBuilder->expects($this->never())->method('addOrderBy');
        $queryBuilder->expects($this->never())->method('setFirstResult');

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects($this->once())->method('getQuery')->with()->willReturn($query);
        $query->expects($this->once())->method('getOneOrNullScalarResult')->willReturn($result = 3);

        self::assertEquals($result, $this->recordRepository->searchOne($criteria, $orders));
    }

    /**
     * @covers ::searchAll
     */
    public function testSearchAll(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $this->recordManager->expects($this->once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        list($orders, $limit, $offset) = [[], null, null];
        $queryBuilder->expects($this->once())->method('select')->with()->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('search')->with()->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('where')->with(null)->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('setOrders')->with($orders)->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('setFirstResult')->with($offset)->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('setMaxResults')->with($limit)->willReturn($queryBuilder);
        $queryBuilder->expects($this->never())->method('insert');
        $queryBuilder->expects($this->never())->method('setValues');
        $queryBuilder->expects($this->never())->method('update');
        $queryBuilder->expects($this->never())->method('delete');
        $queryBuilder->expects($this->never())->method('andWhere');
        $queryBuilder->expects($this->never())->method('orWhere');
        $queryBuilder->expects($this->never())->method('orderBy');
        $queryBuilder->expects($this->never())->method('addOrderBy');

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects($this->once())->method('getQuery')->with()->willReturn($query);
        $query->expects($this->once())->method('getScalarResult')->willReturn($result = [1, 2, 3]);

        self::assertEquals($result, $this->recordRepository->searchAll($orders, $limit, $offset));
    }

    /**
     * @covers ::search
     */
    public function testSearch(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $this->recordManager->expects($this->once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        list($criteria, $orders, $limit, $offset) = [[], [], null, null];
        $queryBuilder->expects($this->once())->method('select')->with()->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('search')->with()->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('where')->with($criteria ? CompositeDomain::criteria($criteria) : null)->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('setOrders')->with($orders)->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('setFirstResult')->with($offset)->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('setMaxResults')->with($limit)->willReturn($queryBuilder);
        $queryBuilder->expects($this->never())->method('insert');
        $queryBuilder->expects($this->never())->method('setValues');
        $queryBuilder->expects($this->never())->method('update');
        $queryBuilder->expects($this->never())->method('delete');
        $queryBuilder->expects($this->never())->method('andWhere');
        $queryBuilder->expects($this->never())->method('orWhere');
        $queryBuilder->expects($this->never())->method('orderBy');
        $queryBuilder->expects($this->never())->method('addOrderBy');

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects($this->once())->method('getQuery')->with()->willReturn($query);
        $query->expects($this->once())->method('getScalarResult')->willReturn($result = [1, 2, 3]);

        self::assertEquals($result, $this->recordRepository->search($criteria, $orders, $limit, $offset));
    }

    /**
     * @covers ::read
     */
    public function testRead(): void
    {
        list($id, $fields) = [3, ['selected_field']];
        $queryBuilder = $this->prepareSearchQueryBuilder($this->recordRepository->expr()->eq('id', $id), $fields, [], 1);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects($this->once())->method('getQuery')->with()->willReturn($query);
        $query->expects($this->once())->method('getResult')->willReturn([
            $result = [
                'foo' => 'bar',
            ],
            [
                'qux' => 'lux'
            ]
        ]);

        self::assertEquals($result, $this->recordRepository->read($id, $fields));
    }

    /**
     * @covers ::read
     */
    public function testReadWithNoResult(): void
    {
        $this->expectException(RecordNotFoundException::class);

        list($id, $fields) = [3, ['selected_field']];
        $queryBuilder = $this->prepareSearchQueryBuilder($this->recordRepository->expr()->eq('id', $id), $fields, [], 1);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects($this->once())->method('getQuery')->with()->willReturn($query);
        $query->expects($this->once())->method('getResult')->willReturn([]);

        $this->recordRepository->read($id, $fields);
    }

    /**
     * @covers ::find
     */
    public function testFind(): void
    {
        list($id, $fields) = [3, ['selected_field']];
        $queryBuilder = $this->prepareSearchQueryBuilder($this->recordRepository->expr()->eq('id', $id), $fields, [], 1);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects($this->once())->method('getQuery')->with()->willReturn($query);
        $query->expects($this->once())->method('getResult')->willReturn([
            $result = [
                'foo' => 'bar',
            ],
            [
                'qux' => 'lux'
            ]
        ]);

        self::assertEquals($result, $this->recordRepository->find($id, $fields));
    }

    /**
     * @covers ::find
     */
    public function testFindWithNoResult(): void
    {
        list($id, $fields) = [3, ['selected_field']];
        $queryBuilder = $this->prepareSearchQueryBuilder($this->recordRepository->expr()->eq('id', $id), $fields, [], 1);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects($this->once())->method('getQuery')->with()->willReturn($query);
        $query->expects($this->once())->method('getResult')->willReturn($result = []);

        self::assertNull($this->recordRepository->find($id, $fields));
    }

    /**
     * @covers ::findOneBy
     */
    public function testFindOneBy(): void
    {
        list($criteria, $fields, $orders, $offset) = [[], [], [], null];
        $queryBuilder = $this->prepareSearchQueryBuilder($criteria, $fields, $orders, 1, $offset);

        $query = $this->createMock(OrmQuery::class);
        $queryBuilder->expects($this->once())->method('getQuery')->with()->willReturn($query);
        $query->expects($this->once())->method('getResult')->willReturn([
            $result = [
                'foo' => 'bar',
            ],
            [
                'qux' => 'lux'
            ]
        ]);

        self::assertEquals($result, $this->recordRepository->findOneBy($criteria, $fields, $orders, $offset));
    }

    /**
     * @covers ::findOneBy
     */
    public function testPrepare(): void
    {
        list($criteria, $fields, $orders, $limit, $offset) = [[], [], [], null, null];
        $queryBuilder = $this->prepareSearchQueryBuilder($criteria, $fields, $orders, $limit, $offset);

        self::assertEquals($queryBuilder, $this->recordRepository->prepare($criteria, $fields, $orders, $limit, $offset));
    }

    /**
     * @internal
     */
    private function prepareSearchQueryBuilder(array|DomainInterface $criteria = null, array $fields = [], array $orders = [], int $limit = null, int $offset = null): MockObject
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->exactly(2))->method('select')->withConsecutive([null], [$fields])->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('where')->with($this->recordRepository->normalizeCriteria($criteria))->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('setOrders')->with($orders)->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('setFirstResult')->with($offset)->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('setMaxResults')->with($limit)->willReturn($queryBuilder);
        $queryBuilder->expects($this->never())->method('insert');
        $queryBuilder->expects($this->never())->method('search');
        $queryBuilder->expects($this->never())->method('update');
        $queryBuilder->expects($this->never())->method('setValues');
        $queryBuilder->expects($this->never())->method('delete');
        $queryBuilder->expects($this->never())->method('andWhere');
        $queryBuilder->expects($this->never())->method('orWhere');
        $queryBuilder->expects($this->never())->method('orderBy');
        $queryBuilder->expects($this->never())->method('addOrderBy');

        $this->recordManager->expects($this->once())->method('createQueryBuilder')->with($this->modelName)->willReturn($queryBuilder);

        return $queryBuilder;
    }
}