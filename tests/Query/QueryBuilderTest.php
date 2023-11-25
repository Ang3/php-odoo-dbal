<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Query;

use Ang3\Component\Odoo\DBAL\Query\Enum\QueryMethod;
use Ang3\Component\Odoo\DBAL\Query\Enum\QueryOrder;
use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\Comparison;
use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\DomainInterface;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\Query\QueryException;
use Ang3\Component\Odoo\DBAL\RecordManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Query\QueryBuilder
 *
 * @internal
 */
final class QueryBuilderTest extends TestCase
{
    private QueryBuilder $queryBuilder;
    private MockObject $recordManager;
    private string $modelName = 'model_name';

    protected function setUp(): void
    {
        parent::setUp();
        $this->recordManager = $this->createMock(RecordManager::class);
        $this->queryBuilder = new QueryBuilder($this->recordManager, $this->modelName);
    }

    /**
     * @covers ::from
     * @covers ::getFrom
     */
    public function testFrom(): void
    {
        $this->queryBuilder->from($modelName = 'res.company');
        static::assertSame($modelName, $this->queryBuilder->getFrom());
    }

    /**
     * @covers ::select
     *
     * @testWith [null, []]
     *           ["field_name", ["field_name"]]
     *           [["field_name1", "field_name2"], ["field_name1", "field_name2"]]
     *           [["field_name1", "field_name2", "field_name1"], ["field_name1", "field_name2"]]
     */
    public function testSelect(array|string $fields = null, array $expectedResult = []): void
    {
        $this->setQueryBuilderMethod(QueryMethod::Update);
        $this->assertQueryBuilderValues(method: QueryMethod::Update);

        $this->queryBuilder->select($fields);
        $this->assertQueryBuilderValues(method: QueryMethod::SearchAndRead, select: $expectedResult);
    }

    /**
     * @covers ::select
     *
     * @testWith [""]
     *           [" "]
     *           [" è "]
     *           [["", "field_name"]]
     *           [[" ", "field_name"]]
     *           [[" è ", "field_name"]]
     */
    public function testSelectWithInvalidFieldName(array|string $fields = null): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->queryBuilder->select($fields);
    }

    /**
     * @covers ::addSelect
     */
    public function testAddSelect(): void
    {
        $this->queryBuilder->addSelect($fieldName1 = 'field_name1');
        static::assertSame([$fieldName1], $this->queryBuilder->getSelect());

        $this->queryBuilder->addSelect($fieldName2 = 'field_name2');
        static::assertSame([$fieldName1, $fieldName2], $this->queryBuilder->getSelect());

        // Deduplication test
        $this->queryBuilder->addSelect($fieldName2);
        static::assertSame([$fieldName1, $fieldName2], $this->queryBuilder->getSelect());
    }

    /**
     * @covers ::addSelect
     *
     * @testWith [""]
     *           [" "]
     *           [" è "]
     */
    public function testAddSelectWithEmptyFieldName(string $invalidFieldName): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->queryBuilder->addSelect($invalidFieldName);
    }

    /**
     * @covers ::addSelect
     *
     * @testWith ["search"]
     *           ["create"]
     *           ["write"]
     *           ["unlink"]
     */
    public function testAddSelectWithInvalidQueryType(string $invalidType): void
    {
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod(QueryMethod::from($invalidType));
        $this->queryBuilder->addSelect('field_name');
    }

    /**
     * @covers ::search
     *
     * @testWith [null]
     *           ["model_name"]
     */
    public function testSearch(string $modelName = null): void
    {
        $this->setQueryBuilderMethod(QueryMethod::SearchAndRead);
        $this->assertQueryBuilderValues(method: QueryMethod::SearchAndRead);

        $this->queryBuilder->search($modelName);
        $this->assertQueryBuilderValues(method: QueryMethod::Search, from: $modelName);
    }

    /**
     * @covers ::search
     *
     * @testWith [""]
     *           [" "]
     *           [" è "]
     */
    public function testSearchWithInvalidModelName(string $invalidModelName): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->queryBuilder->search($invalidModelName);
    }

    /**
     * @covers ::insert
     *
     * @testWith [null]
     *           ["model_name"]
     */
    public function testInsert(string $modelName = null): void
    {
        $this->setQueryBuilderMethod(QueryMethod::SearchAndRead);
        $this->assertQueryBuilderValues(method: QueryMethod::SearchAndRead);

        $this->queryBuilder->insert($modelName);
        $this->assertQueryBuilderValues(method: QueryMethod::Insert, from: $modelName);
    }

    /**
     * @covers ::insert
     *
     * @testWith [""]
     *           [" "]
     *           [" è "]
     */
    public function testInsertWithInvalidModelName(string $invalidModelName): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->queryBuilder->insert($invalidModelName);
    }

    /**
     * @covers ::update
     *
     * @testWith [null]
     *           ["model_name"]
     */
    public function testUpdate(string $modelName = null): void
    {
        $this->setQueryBuilderMethod(QueryMethod::SearchAndRead);
        $this->assertQueryBuilderValues(method: QueryMethod::SearchAndRead);

        $this->queryBuilder->update($modelName);
        $this->assertQueryBuilderValues(method: QueryMethod::Update, from: $modelName);
    }

    /**
     * @covers ::update
     *
     * @testWith [""]
     *           [" "]
     *           [" è "]
     */
    public function testUpdateWithInvalidModelName(string $invalidModelName): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->queryBuilder->update($invalidModelName);
    }

    /**
     * @covers ::delete
     *
     * @testWith [null]
     *           ["model_name"]
     */
    public function testDelete(string $modelName = null): void
    {
        $this->setQueryBuilderMethod(QueryMethod::SearchAndRead);
        $this->assertQueryBuilderValues(method: QueryMethod::SearchAndRead);

        $this->queryBuilder->delete($modelName);
        $this->assertQueryBuilderValues(method: QueryMethod::Delete, from: $modelName);
    }

    /**
     * @covers ::delete
     *
     * @testWith [""]
     *           [" "]
     *           [" è "]
     */
    public function testDeleteWithInvalidModelName(string $invalidModelName): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->queryBuilder->delete($invalidModelName);
    }

    /**
     * @covers ::setIds
     *
     * @testWith ["write", null]
     *           ["write", 1]
     *           ["write", 3]
     *           ["write", [1]]
     *           ["write", [1, 3]]
     *           ["write", [1, 3, 3]]
     *           ["unlink", null]
     */
    public function testSetIds(string $method, null|array|int $ids): void
    {
        $method = QueryMethod::from($method);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setIds($ids);
        $this->assertQueryBuilderValues(method: $method, ids: array_unique(array_filter(\is_array($ids) ? $ids : [$ids])));
    }

    /**
     * @covers ::setIds
     *
     * @testWith ["create", null]
     *           ["create", 1]
     *           ["create", 3]
     *           ["create", [1]]
     *           ["create", [1, 3]]
     *           ["search_read", null]
     *           ["search", null]
     */
    public function testSetIdsWithInvalidMethod(string $invalidMethod, null|array|int $ids): void
    {
        $method = QueryMethod::from($invalidMethod);
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setIds($ids);
    }

    /**
     * @covers ::setIds
     *
     * @testWith ["write", -1]
     *           ["write", 0]
     *           ["write", [-1]]
     *           ["write", [0]]
     */
    public function testSetIdsWithInvalidIds(string $method, null|array|int $ids): void
    {
        $method = QueryMethod::from($method);
        $this->expectException(\InvalidArgumentException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setIds($ids);
    }

    /**
     * @covers ::addId
     *
     * @testWith ["write", 1]
     *           ["write", 3]
     *           ["unlink", 1]
     */
    public function testAddId(string $method, int $id): void
    {
        $method = QueryMethod::from($method);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->addId($id);
        $this->assertQueryBuilderValues(method: $method, ids: [$id]);
    }

    /**
     * @covers ::addId
     *
     * @testWith ["search", 1]
     *           ["search_read", 3]
     *           ["create", 1]
     */
    public function testAddIdWithInvalidMethod(string $invalidMethod, int $id): void
    {
        $method = QueryMethod::from($invalidMethod);
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->addId($id);
    }

    /**
     * @covers ::setValues
     *
     * @testWith ["create", {"foo": true, "bar": 1, "baz": 1.3, "qux": "3", "lux": [1,2,3]}]
     *           ["write", {"foo": true, "bar": 1, "baz": 1.3, "qux": "3", "lux": [1,2,3]}]
     */
    public function testSetValues(string $method, array $values): void
    {
        $method = QueryMethod::from($method);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setValues($values);
        $this->assertQueryBuilderValues(method: $method, values: $values);
    }

    /**
     * @covers ::setValues
     *
     * @testWith ["search", {}]
     *           ["search", {"foo": true, "bar": 1, "baz": 1.3, "qux": "3", "lux": [1,2,3]}]
     *           ["search_read", {}]
     *           ["search_read", {"foo": true, "bar": 1, "baz": 1.3, "qux": "3", "lux": [1,2,3]}]
     *           ["unlink", {}]
     *           ["unlink", {"foo": true, "bar": 1, "baz": 1.3, "qux": "3", "lux": [1,2,3]}]
     */
    public function testSetValuesWithInvalidMethod(string $invalidMethod, array $values): void
    {
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod(QueryMethod::from($invalidMethod));
        $this->queryBuilder->setValues($values);
    }

    /**
     * @covers ::set
     *
     * @testWith ["create", "field_name", 1]
     *           ["write", "field_name", 3]
     */
    public function testSet(string $method, string $fieldName, mixed $value): void
    {
        $method = QueryMethod::from($method);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->set($fieldName, $value);
        $this->assertQueryBuilderValues(method: $method, values: [$fieldName => $value]);
    }

    /**
     * @covers ::set
     *
     * @testWith ["search", "field_name", 1]
     *           ["search_read", "field_name", 3]
     *           ["unlink", "field_name", 3]
     */
    public function testSetWithInvalidMethod(string $invalidMethod, string $fieldName, mixed $value): void
    {
        $method = QueryMethod::from($invalidMethod);
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->set($fieldName, $value);
    }

    /**
     * @covers ::where
     *
     * @testWith ["search"]
     *           ["search_read"]
     */
    public function testWhere(string $method): void
    {
        $method = QueryMethod::from($method);
        $this->setQueryBuilderMethod($method);
        $domain = $this->createMock(DomainInterface::class);
        $this->queryBuilder->where($domain);
        $this->assertQueryBuilderValues(method: $method, where: $domain);
    }

    /**
     * @covers ::where
     *
     * @testWith ["search"]
     *           ["search_read"]
     */
    public function testWhereWithEmptyValue(string $method): void
    {
        $method = QueryMethod::from($method);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->where();
        $this->assertQueryBuilderValues(method: $method);
    }

    /**
     * @covers ::where
     *
     * @testWith ["create"]
     *           ["write"]
     *           ["unlink"]
     */
    public function testWhereWithInvalidMethod(string $invalidMethod): void
    {
        $method = QueryMethod::from($invalidMethod);
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($method);
        $domain = $this->createMock(DomainInterface::class);
        $this->queryBuilder->where($domain);
    }

    /**
     * @covers ::andWhere
     *
     * @testWith ["search"]
     *           ["search_read"]
     */
    public function testAndWhere(string $method): void
    {
        $method = QueryMethod::from($method);
        $this->setQueryBuilderMethod($method);
        $domain = $this->createMock(DomainInterface::class);
        $this->queryBuilder->andWhere($domain);
        $this->assertQueryBuilderValues(method: $method, where: $domain);
    }

    /**
     * @covers ::andWhere
     *
     * @testWith ["search"]
     *           ["search_read"]
     */
    public function testAndWhereWithClauseValue(string $method): void
    {
        $method = QueryMethod::from($method);
        $domainA = $this->createMock(DomainInterface::class);
        $this->setQueryBuilderMethod($method);
        $this->setQueryBuilderPropertyValue('where', $domainA);

        $domainB = $this->createMock(DomainInterface::class);
        $this->queryBuilder->andWhere($domainB);
        $this->assertQueryBuilderValues(method: $method, where: $this->queryBuilder->expr()->andX($domainA, $domainB));
    }

    /**
     * @covers ::andWhere
     *
     * @testWith ["create"]
     *           ["write"]
     *           ["unlink"]
     */
    public function testAndWhereWithInvalidMethod(string $invalidMethod): void
    {
        $method = QueryMethod::from($invalidMethod);
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($method);
        $domain = $this->createMock(DomainInterface::class);
        $this->queryBuilder->andWhere($domain);
    }

    /**
     * @covers ::orWhere
     *
     * @testWith ["search"]
     *           ["search_read"]
     */
    public function testOrWhere(string $method): void
    {
        $method = QueryMethod::from($method);
        $this->setQueryBuilderMethod($method);
        $domain = $this->createMock(DomainInterface::class);
        $this->queryBuilder->orWhere($domain);
        $this->assertQueryBuilderValues(method: $method, where: $domain);
    }

    /**
     * @covers ::orWhere
     *
     * @testWith ["search"]
     *           ["search_read"]
     */
    public function testOrWhereWithClauseValue(string $method): void
    {
        $method = QueryMethod::from($method);
        $domainA = $this->createMock(DomainInterface::class);
        $this->setQueryBuilderMethod($method);
        $this->setQueryBuilderPropertyValue('where', $domainA);

        $domainB = $this->createMock(DomainInterface::class);
        $this->queryBuilder->orWhere($domainB);
        $this->assertQueryBuilderValues(method: $method, where: $this->queryBuilder->expr()->orX($domainA, $domainB));
    }

    /**
     * @covers ::orWhere
     *
     * @testWith ["create"]
     *           ["write"]
     *           ["unlink"]
     */
    public function testOrWhereWithInvalidMethod(string $invalidMethod): void
    {
        $method = QueryMethod::from($invalidMethod);
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($method);
        $domain = $this->createMock(DomainInterface::class);
        $this->queryBuilder->orWhere($domain);
    }

    /**
     * @covers ::setOrders
     *
     * @testWith ["search", {}]
     *           ["search", {"foo": "asc", "bar": "desc"}]
     *           ["search_read", {}]
     *           ["search_read", {"foo": "asc", "bar": "desc"}]
     */
    public function testSetOrders(string $method, array $orders): void
    {
        $method = QueryMethod::from($method);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setOrders($orders);
        $this->assertQueryBuilderValues(method: $method, orders: $this->normalizeOrders($orders));
    }

    /**
     * @covers ::setOrders
     *
     * @testWith ["create", {}]
     *           ["create", {"foo": true, "bar": 1, "baz": 1.3, "qux": "3", "lux": [1,2,3]}]
     *           ["write", {}]
     *           ["write", {"foo": true, "bar": 1, "baz": 1.3, "qux": "3", "lux": [1,2,3]}]
     *           ["unlink", {}]
     *           ["unlink", {"foo": true, "bar": 1, "baz": 1.3, "qux": "3", "lux": [1,2,3]}]
     */
    public function testSetOrdersWithInvalidMethod(string $invalidMethod, array $orders): void
    {
        $method = QueryMethod::from($invalidMethod);
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setOrders($orders);
    }

    /**
     * @covers ::setOrders
     *
     * @testWith ["search", {"": "asc"}]
     *           ["search_read", {" ": "asc"}]
     *           ["search_read", {" è ": "asc"}]
     */
    public function testSetOrdersWithInvalidFieldNames(string $method, array $orders): void
    {
        $method = QueryMethod::from($method);
        $this->expectException(\InvalidArgumentException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setOrders($orders);
    }

    /**
     * @covers ::setOrders
     *
     * @testWith ["search", {"foo": "dasc"}]
     *           ["search_read", {"foo": "esc"}]
     */
    public function testSetOrdersWithInvalidOrders(string $method, array $orders): void
    {
        $method = QueryMethod::from($method);
        $this->expectException(\ValueError::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setOrders($orders);
    }

    /**
     * @covers ::orderBy
     *
     * @testWith ["search", "field_name", "asc"]
     *           ["search", "field_name", "desc"]
     *           ["search_read", "field_name", "asc"]
     *           ["search_read", "field_name", "desc"]
     */
    public function testOrderBy(string $method, string $fieldName, string $order): void
    {
        $method = QueryMethod::from($method);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->orderBy($fieldName, $order);
        $this->assertQueryBuilderValues(method: $method, orders: $this->normalizeOrders([
            $fieldName => $order,
        ]));
    }

    /**
     * @covers ::orderBy
     *
     * @testWith ["create", "field_name", "asc"]
     *           ["write", "field_name", "desc"]
     *           ["unlink", "field_name", "asc"]
     */
    public function testOrderByWithInvalidMethod(string $invalidMethod, string $fieldName, string $order): void
    {
        $method = QueryMethod::from($invalidMethod);
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->orderBy($fieldName, $order);
    }

    /**
     * @covers ::orderBy
     *
     * @testWith ["search", "", "asc"]
     *           ["search_read", " ", "desc"]
     *           ["search_read", " è ", "desc"]
     */
    public function testOrderByWithInvalidFieldName(string $invalidMethod, string $fieldName, string $order): void
    {
        $method = QueryMethod::from($invalidMethod);
        $this->expectException(\InvalidArgumentException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->orderBy($fieldName, $order);
    }

    /**
     * @covers ::addOrderBy
     *
     * @testWith ["search", "field_name2", "asc"]
     *           ["search", "field_name2", "desc"]
     *           ["search_read", "field_name2", "asc"]
     *           ["search_read", "field_name2", "desc"]
     */
    public function testAddOrderBy(string $method, string $fieldName, string $order): void
    {
        $method = QueryMethod::from($method);
        $this->setQueryBuilderMethod($method);
        $this->setQueryBuilderPropertyValue('orders', $baseOrders = [
            'field_name1' => QueryOrder::ASC,
        ]);

        $this->queryBuilder->addOrderBy($fieldName, $order);
        $this->assertQueryBuilderValues(method: $method, orders: $this->normalizeOrders(array_merge($baseOrders, [
            $fieldName => $order,
        ])));
    }

    /**
     * @covers ::addOrderBy
     *
     * @testWith ["create", "field_name", "asc"]
     *           ["write", "field_name", "desc"]
     *           ["unlink", "field_name", "asc"]
     */
    public function testAddOrderByWithInvalidMethod(string $invalidMethod, string $fieldName, string $order): void
    {
        $method = QueryMethod::from($invalidMethod);
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->addOrderBy($fieldName, $order);
    }

    /**
     * @covers ::addOrderBy
     *
     * @testWith ["search", "", "asc"]
     *           ["search_read", " ", "desc"]
     *           ["search_read", " è ", "desc"]
     */
    public function testAddOrderByWithInvalidFieldName(string $invalidMethod, string $fieldName, string $order): void
    {
        $method = QueryMethod::from($invalidMethod);
        $this->expectException(\InvalidArgumentException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->addOrderBy($fieldName, $order);
    }

    /**
     * @covers ::setMaxResults
     *
     * @testWith ["search", null]
     *           ["search_read", 1]
     *           ["search_read", 150]
     */
    public function testSetMaxResults(string $method, ?int $maxResults): void
    {
        $method = QueryMethod::from($method);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setMaxResults($maxResults);
        $this->assertQueryBuilderValues($method, maxResults: $maxResults);
    }

    /**
     * @covers ::orderBy
     *
     * @testWith ["create", null]
     *           ["write", 1]
     *           ["unlink", 150]
     */
    public function testSetMaxResultsInvalidMethod(string $invalidMethod, ?int $maxResults): void
    {
        $method = QueryMethod::from($invalidMethod);
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setMaxResults($maxResults);
    }

    /**
     * @covers ::setMaxResults
     *
     * @testWith ["search", -1]
     *           ["search_read", 0]
     */
    public function testSetMaxResultsWithInvalidValue(string $method, int $maxResults): void
    {
        $method = QueryMethod::from($method);
        $this->expectException(\InvalidArgumentException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setMaxResults($maxResults);
    }

    /**
     * @covers ::setFirstResult
     *
     * @testWith ["search", null]
     *           ["search_read", 0]
     *           ["search_read", 1]
     *           ["search_read", 150]
     */
    public function testSetFirstResult(string $method, ?int $firstResult): void
    {
        $method = QueryMethod::from($method);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setFirstResult($firstResult);
        $this->assertQueryBuilderValues($method, firstResult: $firstResult);
    }

    /**
     * @covers ::orderBy
     *
     * @testWith ["create", null]
     *           ["write", 0]
     *           ["write", 1]
     *           ["unlink", 150]
     */
    public function testSetFirstResultInvalidMethod(string $invalidMethod, ?int $firstResult): void
    {
        $method = QueryMethod::from($invalidMethod);
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setFirstResult($firstResult);
    }

    /**
     * @covers ::setFirstResult
     *
     * @testWith ["search", -1]
     *           ["search_read", -3]
     */
    public function testSetFirstResultWithInvalidValue(string $method, int $firstResult): void
    {
        $method = QueryMethod::from($method);
        $this->expectException(\InvalidArgumentException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setFirstResult($firstResult);
    }

    /**
     * @covers ::reset
     */
    public function testReset(): void
    {
        $this->setQueryBuilderMethod(QueryMethod::Delete);
        $this->setQueryBuilderPropertyValue('select', ['field_name1', 'field_name2']);
        $this->setQueryBuilderPropertyValue('ids', [1, 2, 3]);
        $this->setQueryBuilderPropertyValue('values', ['foo' => 'bar']);
        $this->setQueryBuilderPropertyValue('where', new Comparison('foo', Comparison::EQUAL_TO, 1));
        $this->setQueryBuilderPropertyValue('orders', ['field_name1' => QueryOrder::ASC]);
        $this->setQueryBuilderPropertyValue('maxResults', 100);
        $this->setQueryBuilderPropertyValue('firstResult', 10);

        $this->queryBuilder->reset();
        $this->assertQueryBuilderValues(QueryMethod::Delete);
    }

    /**
     * @internal
     */
    private function assertQueryBuilderValues(
        QueryMethod $method,
        string $from = null,
        array $select = [],
        array $ids = [],
        array $values = [],
        DomainInterface $where = null,
        array $orders = [],
        int $maxResults = null,
        int $firstResult = null
    ): void {
        static::assertSame($method, $this->queryBuilder->getMethod());
        static::assertSame($from ?: $this->modelName, $this->queryBuilder->getFrom());
        static::assertSame($select, $this->queryBuilder->getSelect());
        static::assertSame($values, $this->queryBuilder->getValues());
        static::assertSame($ids, $this->queryBuilder->getIds());

        if ($where) {
            static::assertSame($where->toArray(), $this->queryBuilder->getWhere()->toArray());
        }

        static::assertSame($orders, $this->queryBuilder->getOrders());
        static::assertSame($maxResults, $this->queryBuilder->getMaxResults());
        static::assertSame($firstResult, $this->queryBuilder->getFirstResult());
    }

    /**
     * @internal
     */
    private function normalizeOrders(array $orders): array
    {
        return array_map(static fn ($value) => $value instanceof QueryOrder ? $value : QueryOrder::from(strtolower($value)), $orders);
    }

    /**
     * @internal
     *
     * @throws \ReflectionException
     */
    private function setQueryBuilderMethod(QueryMethod $method): void
    {
        $this->setQueryBuilderPropertyValue('method', $method);
    }

    /**
     * @internal
     *
     * @throws \ReflectionException
     */
    private function setQueryBuilderPropertyValue(string $propertyName, mixed $value): void
    {
        (new \ReflectionProperty(QueryBuilder::class, $propertyName))->setValue($this->queryBuilder, $value);
    }
}
