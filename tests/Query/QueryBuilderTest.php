<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Query;

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

    public function testFrom(): void
    {
        $this->queryBuilder->from($modelName = 'res.company');
        self::assertSame($modelName, $this->queryBuilder->getFrom());
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
        $this->setQueryBuilderType('foo');
        $this->assertQueryBuilderValues(type: 'foo');

        $this->queryBuilder->select($fields);
        $this->assertQueryBuilderValues(type: QueryBuilder::SELECT, select: $expectedResult);
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
        self::assertSame([$fieldName1], $this->queryBuilder->getSelect());

        $this->queryBuilder->addSelect($fieldName2 = 'field_name2');
        self::assertSame([$fieldName1, $fieldName2], $this->queryBuilder->getSelect());

        // Deduplication test
        $this->queryBuilder->addSelect($fieldName2);
        self::assertSame([$fieldName1, $fieldName2], $this->queryBuilder->getSelect());
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
        $this->setQueryBuilderType($invalidType);
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
        $this->setQueryBuilderType('foo');
        $this->assertQueryBuilderValues(type: 'foo');

        $this->queryBuilder->search($modelName);
        $this->assertQueryBuilderValues(type: QueryBuilder::SEARCH, from: $modelName);
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
        $this->setQueryBuilderType('foo');
        $this->assertQueryBuilderValues(type: 'foo');

        $this->queryBuilder->insert($modelName);
        $this->assertQueryBuilderValues(type: QueryBuilder::INSERT, from: $modelName);
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
        $this->setQueryBuilderType('foo');
        $this->assertQueryBuilderValues(type: 'foo');

        $this->queryBuilder->update($modelName);
        $this->assertQueryBuilderValues(type: QueryBuilder::UPDATE, from: $modelName);
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
        $this->setQueryBuilderType('foo');
        $this->assertQueryBuilderValues(type: 'foo');

        $this->queryBuilder->delete($modelName);
        $this->assertQueryBuilderValues(type: QueryBuilder::DELETE, from: $modelName);
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
        $this->setQueryBuilderType($method);
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
    public function testSetIdsWithInvalidMethod(string $method, null|array|int $ids): void
    {
        $this->expectException(QueryException::class);
        $this->setQueryBuilderType($method);
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
        $this->setQueryBuilderType($method);
        $this->queryBuilder->setIds($ids);
    }

    /**
     * @internal
     */
    private function assertQueryBuilderValues(
        string $type,
        string $from = null,
        array $select = [],
        array $ids = [],
        array $values = [],
        DomainInterface $where = null,
        array $orders = [],
        int $maxResults = null,
        int $firstResult = null
    ): void {
        self::assertSame($type, $this->queryBuilder->getType());
        self::assertSame($from ?: $this->modelName, $this->queryBuilder->getFrom());
        self::assertSame($select, $this->queryBuilder->getSelect());
        self::assertSame($values, $this->queryBuilder->getValues());
        self::assertSame($ids, $this->queryBuilder->getIds());
        self::assertSame($where, $this->queryBuilder->getWhere());
        self::assertSame($orders, $this->queryBuilder->getOrders());
        self::assertSame($maxResults, $this->queryBuilder->getMaxResults());
        self::assertSame($firstResult, $this->queryBuilder->getFirstResult());
    }

    /**
     * @internal
     */
    private function setQueryBuilderType(string $type): void
    {
        (new \ReflectionProperty(QueryBuilder::class, 'type'))->setValue($this->queryBuilder, $type);
    }
}
