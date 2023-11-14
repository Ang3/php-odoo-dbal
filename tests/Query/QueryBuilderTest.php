<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Query;

use Ang3\Component\Odoo\DBAL\Query\Enum\QueryBuilderMethod;
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
        self::assertEquals($modelName, $this->queryBuilder->getFrom());
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
        $this->setQueryBuilderMethod(QueryBuilderMethod::Update);
        $this->assertQueryBuilderValues(type: QueryBuilderMethod::Update);

        $this->queryBuilder->select($fields);
        $this->assertQueryBuilderValues(type: QueryBuilderMethod::Select, select: $expectedResult);
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
        self::assertEquals([$fieldName1], $this->queryBuilder->getSelect());

        $this->queryBuilder->addSelect($fieldName2 = 'field_name2');
        self::assertEquals([$fieldName1, $fieldName2], $this->queryBuilder->getSelect());

        // Deduplication test
        $this->queryBuilder->addSelect($fieldName2);
        self::assertEquals([$fieldName1, $fieldName2], $this->queryBuilder->getSelect());
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
     *           ["insert"]
     *           ["update"]
     *           ["delete"]
     */
    public function testAddSelectWithInvalidQueryType(string $invalidType): void
    {
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($invalidType);
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
        $this->setQueryBuilderMethod(QueryBuilderMethod::Select);
        $this->assertQueryBuilderValues(type: QueryBuilderMethod::Select);

        $this->queryBuilder->search($modelName);
        $this->assertQueryBuilderValues(type: QueryBuilderMethod::Search, from: $modelName);
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
        $this->setQueryBuilderMethod(QueryBuilderMethod::Select);
        $this->assertQueryBuilderValues(type: QueryBuilderMethod::Select);

        $this->queryBuilder->insert($modelName);
        $this->assertQueryBuilderValues(type: QueryBuilderMethod::Insert, from: $modelName);
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
        $this->setQueryBuilderMethod(QueryBuilderMethod::Select);
        $this->assertQueryBuilderValues(type: QueryBuilderMethod::Select);

        $this->queryBuilder->update($modelName);
        $this->assertQueryBuilderValues(type: QueryBuilderMethod::Update, from: $modelName);
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
        $this->setQueryBuilderMethod(QueryBuilderMethod::Select);
        $this->assertQueryBuilderValues(type: QueryBuilderMethod::Select);

        $this->queryBuilder->delete($modelName);
        $this->assertQueryBuilderValues(type: QueryBuilderMethod::Delete, from: $modelName);
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
     * @testWith ["update", null]
     *           ["update", 1]
     *           ["update", 3]
     *           ["update", [1]]
     *           ["update", [1, 3]]
     *           ["update", [1, 3, 3]]
     *           ["delete", null]
     */
    public function testSetIds(string $method, null|array|int $ids): void
    {
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setIds($ids);
        $this->assertQueryBuilderValues(type: $method, ids: array_unique(array_filter(\is_array($ids) ? $ids : [$ids])));
    }

    /**
     * @covers ::setIds
     *
     * @testWith ["insert", null]
     *           ["insert", 1]
     *           ["insert", 3]
     *           ["insert", [1]]
     *           ["insert", [1, 3]]
     *           ["select", null]
     *           ["search", null]
     */
    public function testSetIdsWithInvalidMethod(string $invalidMethod, null|array|int $ids): void
    {
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($invalidMethod);
        $this->queryBuilder->setIds($ids);
    }

    /**
     * @covers ::setIds
     *
     * @testWith ["update", -1]
     *           ["update", 0]
     *           ["update", [-1]]
     *           ["update", [0]]
     */
    public function testSetIdsWithInvalidIds(string $method, null|array|int $ids): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setIds($ids);
    }

    /**
     * @covers ::addId
     *
     * @testWith ["update", 1]
     *           ["update", 3]
     *           ["delete", 1]
     */
    public function testAddId(string $method, int $id): void
    {
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->addId($id);
        $this->assertQueryBuilderValues(type: $method, ids: [$id]);
    }

    /**
     * @covers ::addId
     *
     * @testWith ["search", 1]
     *           ["select", 3]
     *           ["insert", 1]
     */
    public function testAddIdWithInvalidMethod(string $invalidMethod, int $id): void
    {
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($invalidMethod);
        $this->queryBuilder->addId($id);
    }

    /**
     * @covers ::setValues
     *
     * @testWith ["insert", {"foo": true, "bar": 1, "baz": 1.3, "qux": "3", "lux": [1,2,3]}]
     *           ["update", {"foo": true, "bar": 1, "baz": 1.3, "qux": "3", "lux": [1,2,3]}]
     */
    public function testSetValues(string $method, array $values): void
    {
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setValues($values);
        $this->assertQueryBuilderValues(type: $method, values: $values);
    }

    /**
     * @covers ::setValues
     *
     * @testWith ["search", {}]
     *           ["search", {"foo": true, "bar": 1, "baz": 1.3, "qux": "3", "lux": [1,2,3]}]
     *           ["select", {}]
     *           ["select", {"foo": true, "bar": 1, "baz": 1.3, "qux": "3", "lux": [1,2,3]}]
     *           ["delete", {}]
     *           ["delete", {"foo": true, "bar": 1, "baz": 1.3, "qux": "3", "lux": [1,2,3]}]
     */
    public function testSetValuesWithInvalidMethod(string $invalidMethod, array $values): void
    {
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($invalidMethod);
        $this->queryBuilder->setValues($values);
    }

    /**
     * @covers ::set
     *
     * @testWith ["insert", "field_name", 1]
     *           ["update", "field_name", 3]
     */
    public function testSet(string $method, string $fieldName, mixed $value): void
    {
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->set($fieldName, $value);
        $this->assertQueryBuilderValues(type: $method, values: [$fieldName => $value]);
    }

    /**
     * @covers ::set
     *
     * @testWith ["search", "field_name", 1]
     *           ["select", "field_name", 3]
     *           ["delete", "field_name", 3]
     */
    public function testSetWithInvalidMethod(string $invalidMethod, string $fieldName, mixed $value): void
    {
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($invalidMethod);
        $this->queryBuilder->set($fieldName, $value);
    }

    /**
     * @covers ::where
     *
     * @testWith ["search"]
     *           ["select"]
     */
    public function testWhere(string $method): void
    {
        $this->setQueryBuilderMethod($method);
        $domain = $this->createMock(DomainInterface::class);
        $this->queryBuilder->where($domain);
        $this->assertQueryBuilderValues(type: $method, where: $domain);
    }

    /**
     * @covers ::where
     *
     * @testWith ["search"]
     *           ["select"]
     */
    public function testWhereWithEmptyValue(string $method): void
    {
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->where();
        $this->assertQueryBuilderValues(type: $method);
    }

    /**
     * @covers ::where
     *
     * @testWith ["insert"]
     *           ["update"]
     *           ["delete"]
     */
    public function testWhereWithInvalidMethod(string $invalidMethod): void
    {
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($invalidMethod);
        $domain = $this->createMock(DomainInterface::class);
        $this->queryBuilder->where($domain);
    }

    /**
     * @covers ::andWhere
     *
     * @testWith ["search"]
     *           ["select"]
     */
    public function testAndWhere(string $method): void
    {
        $this->setQueryBuilderMethod($method);
        $domain = $this->createMock(DomainInterface::class);
        $this->queryBuilder->andWhere($domain);
        $this->assertQueryBuilderValues(type: $method, where: $domain);
    }

    /**
     * @covers ::andWhere
     *
     * @testWith ["search"]
     *           ["select"]
     */
    public function testAndWhereWithClauseValue(string $method): void
    {
        $domainA = $this->createMock(DomainInterface::class);
        $this->setQueryBuilderMethod($method);
        $this->setQueryBuilderPropertyValue('where', $domainA);

        $domainB = $this->createMock(DomainInterface::class);
        $this->queryBuilder->andWhere($domainB);
        $this->assertQueryBuilderValues(type: $method, where: $this->queryBuilder->expr()->andX($domainA, $domainB));
    }

    /**
     * @covers ::andWhere
     *
     * @testWith ["insert"]
     *           ["update"]
     *           ["delete"]
     */
    public function testAndWhereWithInvalidMethod(string $invalidMethod): void
    {
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($invalidMethod);
        $domain = $this->createMock(DomainInterface::class);
        $this->queryBuilder->andWhere($domain);
    }

    /**
     * @covers ::orWhere
     *
     * @testWith ["search"]
     *           ["select"]
     */
    public function testOrWhere(string $method): void
    {
        $this->setQueryBuilderMethod($method);
        $domain = $this->createMock(DomainInterface::class);
        $this->queryBuilder->orWhere($domain);
        $this->assertQueryBuilderValues(type: $method, where: $domain);
    }

    /**
     * @covers ::orWhere
     *
     * @testWith ["search"]
     *           ["select"]
     */
    public function testOrWhereWithClauseValue(string $method): void
    {
        $domainA = $this->createMock(DomainInterface::class);
        $this->setQueryBuilderMethod($method);
        $this->setQueryBuilderPropertyValue('where', $domainA);

        $domainB = $this->createMock(DomainInterface::class);
        $this->queryBuilder->orWhere($domainB);
        $this->assertQueryBuilderValues(type: $method, where: $this->queryBuilder->expr()->orX($domainA, $domainB));
    }

    /**
     * @covers ::orWhere
     *
     * @testWith ["insert"]
     *           ["update"]
     *           ["delete"]
     */
    public function testOrWhereWithInvalidMethod(string $invalidMethod): void
    {
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($invalidMethod);
        $domain = $this->createMock(DomainInterface::class);
        $this->queryBuilder->orWhere($domain);
    }

    /**
     * @covers ::setOrders
     *
     * @testWith ["search", {}]
     *           ["search", {"foo": "asc", "bar": "desc"}]
     *           ["select", {}]
     *           ["select", {"foo": "asc", "bar": "desc"}]
     */
    public function testSetOrders(string $method, array $orders): void
    {
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setOrders($orders);
        $this->assertQueryBuilderValues(type: $method, orders: $this->normalizeOrders($orders));
    }

    /**
     * @covers ::setOrders
     *
     * @testWith ["insert", {}]
     *           ["insert", {"foo": true, "bar": 1, "baz": 1.3, "qux": "3", "lux": [1,2,3]}]
     *           ["update", {}]
     *           ["update", {"foo": true, "bar": 1, "baz": 1.3, "qux": "3", "lux": [1,2,3]}]
     *           ["delete", {}]
     *           ["delete", {"foo": true, "bar": 1, "baz": 1.3, "qux": "3", "lux": [1,2,3]}]
     */
    public function testSetOrdersWithInvalidMethod(string $invalidMethod, array $orders): void
    {
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($invalidMethod);
        $this->queryBuilder->setOrders($orders);
    }

    /**
     * @covers ::setOrders
     *
     * @testWith ["search", {"": "asc"}]
     *           ["select", {" ": "asc"}]
     *           ["select", {" è ": "asc"}]
     */
    public function testSetOrdersWithInvalidFieldNames(string $method, array $orders): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setOrders($orders);
    }

    /**
     * @covers ::setOrders
     *
     * @testWith ["search", {"foo": "dasc"}]
     *           ["select", {"foo": "esc"}]
     */
    public function testSetOrdersWithInvalidOrders(string $method, array $orders): void
    {
        $this->expectException(\ValueError::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setOrders($orders);
    }

    /**
     * @covers ::orderBy
     *
     * @testWith ["search", "field_name", "asc"]
     *           ["search", "field_name", "desc"]
     *           ["select", "field_name", "asc"]
     *           ["select", "field_name", "desc"]
     */
    public function testOrderBy(string $method, string $fieldName, string $order): void
    {
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->orderBy($fieldName, $order);
        $this->assertQueryBuilderValues(type: $method, orders: $this->normalizeOrders([
            $fieldName => $order,
        ]));
    }

    /**
     * @covers ::orderBy
     *
     * @testWith ["insert", "field_name", "asc"]
     *           ["update", "field_name", "desc"]
     *           ["delete", "field_name", "asc"]
     */
    public function testOrderByWithInvalidMethod(string $invalidMethod, string $fieldName, string $order): void
    {
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($invalidMethod);
        $this->queryBuilder->orderBy($fieldName, $order);
    }

    /**
     * @covers ::orderBy
     *
     * @testWith ["search", "", "asc"]
     *           ["select", " ", "desc"]
     *           ["select", " è ", "desc"]
     */
    public function testOrderByWithInvalidFieldName(string $invalidMethod, string $fieldName, string $order): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->setQueryBuilderMethod($invalidMethod);
        $this->queryBuilder->orderBy($fieldName, $order);
    }

    /**
     * @covers ::addOrderBy
     *
     * @testWith ["search", "field_name2", "asc"]
     *           ["search", "field_name2", "desc"]
     *           ["select", "field_name2", "asc"]
     *           ["select", "field_name2", "desc"]
     */
    public function testAddOrderBy(string $method, string $fieldName, string $order): void
    {
        $this->setQueryBuilderMethod($method);
        $this->setQueryBuilderPropertyValue('orders', $baseOrders = [
            'field_name1' => QueryOrder::ASC,
        ]);

        $this->queryBuilder->addOrderBy($fieldName, $order);
        $this->assertQueryBuilderValues(type: $method, orders: $this->normalizeOrders(array_merge($baseOrders, [
            $fieldName => $order,
        ])));
    }

    /**
     * @covers ::addOrderBy
     *
     * @testWith ["insert", "field_name", "asc"]
     *           ["update", "field_name", "desc"]
     *           ["delete", "field_name", "asc"]
     */
    public function testAddOrderByWithInvalidMethod(string $invalidMethod, string $fieldName, string $order): void
    {
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($invalidMethod);
        $this->queryBuilder->addOrderBy($fieldName, $order);
    }

    /**
     * @covers ::addOrderBy
     *
     * @testWith ["search", "", "asc"]
     *           ["select", " ", "desc"]
     *           ["select", " è ", "desc"]
     */
    public function testAddOrderByWithInvalidFieldName(string $invalidMethod, string $fieldName, string $order): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->setQueryBuilderMethod($invalidMethod);
        $this->queryBuilder->addOrderBy($fieldName, $order);
    }

    /**
     * @covers ::setMaxResults
     *
     * @testWith ["search", null]
     *           ["select", 1]
     *           ["select", 150]
     */
    public function testSetMaxResults(string $method, ?int $maxResults): void
    {
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setMaxResults($maxResults);
        $this->assertQueryBuilderValues($method, maxResults: $maxResults);
    }

    /**
     * @covers ::orderBy
     *
     * @testWith ["insert", null]
     *           ["update", 1]
     *           ["delete", 150]
     */
    public function testSetMaxResultsInvalidMethod(string $invalidMethod, ?int $maxResults): void
    {
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($invalidMethod);
        $this->queryBuilder->setMaxResults($maxResults);
    }

    /**
     * @covers ::setMaxResults
     *
     * @testWith ["search", -1]
     *           ["select", 0]
     */
    public function testSetMaxResultsWithInvalidValue(string $method, int $maxResults): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setMaxResults($maxResults);
    }

    /**
     * @covers ::setFirstResult
     *
     * @testWith ["search", null]
     *           ["select", 0]
     *           ["select", 1]
     *           ["select", 150]
     */
    public function testSetFirstResult(string $method, ?int $firstResult): void
    {
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setFirstResult($firstResult);
        $this->assertQueryBuilderValues($method, firstResult: $firstResult);
    }

    /**
     * @covers ::orderBy
     *
     * @testWith ["insert", null]
     *           ["update", 0]
     *           ["update", 1]
     *           ["delete", 150]
     */
    public function testSetFirstResultInvalidMethod(string $invalidMethod, ?int $firstResult): void
    {
        $this->expectException(QueryException::class);
        $this->setQueryBuilderMethod($invalidMethod);
        $this->queryBuilder->setFirstResult($firstResult);
    }

    /**
     * @covers ::setFirstResult
     *
     * @testWith ["search", -1]
     *           ["select", -3]
     */
    public function testSetFirstResultWithInvalidValue(string $method, int $firstResult): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->setQueryBuilderMethod($method);
        $this->queryBuilder->setFirstResult($firstResult);
    }

    /**
     * @covers ::reset
     */
    public function testReset(): void
    {
        $this->setQueryBuilderMethod(QueryBuilderMethod::Delete);
        $this->setQueryBuilderPropertyValue('select', ['field_name1', 'field_name2']);
        $this->setQueryBuilderPropertyValue('ids', [1, 2, 3]);
        $this->setQueryBuilderPropertyValue('values', ['foo' => 'bar']);
        $this->setQueryBuilderPropertyValue('where', new Comparison('foo', Comparison::EQUAL_TO, 1));
        $this->setQueryBuilderPropertyValue('orders', ['field_name1' => QueryOrder::ASC]);
        $this->setQueryBuilderPropertyValue('maxResults', 100);
        $this->setQueryBuilderPropertyValue('firstResult', 10);

        $this->queryBuilder->reset();
        $this->assertQueryBuilderValues(QueryBuilderMethod::Delete);
    }

    /**
     * @internal
     */
    private function assertQueryBuilderValues(
        QueryBuilderMethod|string $type,
        string $from = null,
        array $select = [],
        array $ids = [],
        array $values = [],
        DomainInterface $where = null,
        array $orders = [],
        int $maxResults = null,
        int $firstResult = null
    ): void {
        self::assertEquals(\is_string($type) ? QueryBuilderMethod::from($type) : $type, $this->queryBuilder->getMethod());
        self::assertEquals($from ?: $this->modelName, $this->queryBuilder->getFrom());
        self::assertEquals($select, $this->queryBuilder->getSelect());
        self::assertEquals($values, $this->queryBuilder->getValues());
        self::assertEquals($ids, $this->queryBuilder->getIds());
        self::assertEquals($where, $this->queryBuilder->getWhere());
        self::assertEquals($orders, $this->queryBuilder->getOrders());
        self::assertEquals($maxResults, $this->queryBuilder->getMaxResults());
        self::assertEquals($firstResult, $this->queryBuilder->getFirstResult());
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
    private function setQueryBuilderMethod(QueryBuilderMethod|string $method): void
    {
        $this->setQueryBuilderPropertyValue('method', $method instanceof QueryBuilderMethod ? $method : QueryBuilderMethod::from($method));
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
