<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Query\Expression;

use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\Comparison;
use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\CompositeDomain;
use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\DomainInterface;
use Ang3\Component\Odoo\DBAL\Query\Expression\ExpressionBuilder;
use Ang3\Component\Odoo\DBAL\Query\Expression\Operation\CollectionOperation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Query\Expression\ExpressionBuilder
 *
 * @internal
 */
final class ExpressionBuilderTest extends TestCase
{
    private ExpressionBuilder $expressionBuilder;
    private MockObject $domainA;
    private MockObject $domainB;
    /** @var MockObject[] */
    private array $domains;
    private string $fieldName = 'field_name';
    private string $fieldValue = 'field_value';
    private array $dataSet = [
        'field_name' => 'field_value',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->expressionBuilder = new ExpressionBuilder();
        $this->domainA = $this->createMock(DomainInterface::class);
        $this->domainB = $this->createMock(DomainInterface::class);
        $this->domains = [$this->domainA, $this->domainB];
    }

    /**
     * @covers ::andX
     */
    public function testAndX(): void
    {
        $domain = $this->expressionBuilder->andX($this->domainA, $this->domainB);
        static::assertInstanceOf(CompositeDomain::class, $domain);
        static::assertEquals(CompositeDomain::AND, $domain->getOperator());
        static::assertEquals($this->domains, $domain->getDomains());
    }

    /**
     * @covers ::andX
     */
    public function testOrX(): void
    {
        $domain = $this->expressionBuilder->orX($this->domainA, $this->domainB);
        static::assertInstanceOf(CompositeDomain::class, $domain);
        static::assertEquals(CompositeDomain::OR, $domain->getOperator());
        static::assertEquals($this->domains, $domain->getDomains());
    }

    /**
     * @covers ::andX
     */
    public function testNotX(): void
    {
        $domain = $this->expressionBuilder->notX($this->domainA, $this->domainB);
        static::assertInstanceOf(CompositeDomain::class, $domain);
        static::assertEquals(CompositeDomain::NOT, $domain->getOperator());
        static::assertEquals($this->domains, $domain->getDomains());
    }

    /**
     * @covers ::eq
     */
    public function testEq(): void
    {
        $domain = $this->expressionBuilder->eq($this->fieldName, $this->fieldValue);
        static::assertInstanceOf(Comparison::class, $domain);
        static::assertEquals(Comparison::EQUAL_TO, $domain->getOperator());
        static::assertEquals($this->fieldName, $domain->getFieldName());
        static::assertEquals($this->fieldValue, $domain->getValue());
    }

    /**
     * @covers ::neq
     */
    public function testNeq(): void
    {
        $domain = $this->expressionBuilder->neq($this->fieldName, $this->fieldValue);
        static::assertInstanceOf(Comparison::class, $domain);
        static::assertEquals(Comparison::NOT_EQUAL_TO, $domain->getOperator());
        static::assertEquals($this->fieldName, $domain->getFieldName());
        static::assertEquals($this->fieldValue, $domain->getValue());
    }

    /**
     * @covers ::ueq
     */
    public function testUeq(): void
    {
        $domain = $this->expressionBuilder->ueq($this->fieldName, $this->fieldValue);
        static::assertInstanceOf(Comparison::class, $domain);
        static::assertEquals(Comparison::UNSET_OR_EQUAL_TO, $domain->getOperator());
        static::assertEquals($this->fieldName, $domain->getFieldName());
        static::assertEquals($this->fieldValue, $domain->getValue());
    }

    /**
     * @covers ::lt
     */
    public function testLt(): void
    {
        $domain = $this->expressionBuilder->lt($this->fieldName, $this->fieldValue);
        static::assertInstanceOf(Comparison::class, $domain);
        static::assertEquals(Comparison::LESS_THAN, $domain->getOperator());
        static::assertEquals($this->fieldName, $domain->getFieldName());
        static::assertEquals($this->fieldValue, $domain->getValue());
    }

    /**
     * @covers ::lte
     */
    public function testLte(): void
    {
        $domain = $this->expressionBuilder->lte($this->fieldName, $this->fieldValue);
        static::assertInstanceOf(Comparison::class, $domain);
        static::assertEquals(Comparison::LESS_THAN_OR_EQUAL, $domain->getOperator());
        static::assertEquals($this->fieldName, $domain->getFieldName());
        static::assertEquals($this->fieldValue, $domain->getValue());
    }

    /**
     * @covers ::gt
     */
    public function testGt(): void
    {
        $domain = $this->expressionBuilder->gt($this->fieldName, $this->fieldValue);
        static::assertInstanceOf(Comparison::class, $domain);
        static::assertEquals(Comparison::GREATER_THAN, $domain->getOperator());
        static::assertEquals($this->fieldName, $domain->getFieldName());
        static::assertEquals($this->fieldValue, $domain->getValue());
    }

    /**
     * @covers ::gte
     */
    public function testGte(): void
    {
        $domain = $this->expressionBuilder->gte($this->fieldName, $this->fieldValue);
        static::assertInstanceOf(Comparison::class, $domain);
        static::assertEquals(Comparison::GREATER_THAN_OR_EQUAL, $domain->getOperator());
        static::assertEquals($this->fieldName, $domain->getFieldName());
        static::assertEquals($this->fieldValue, $domain->getValue());
    }

    /**
     * @covers ::like
     *
     * @testWith ["=like", true, true]
     *           ["=ilike", true, false]
     *           ["=like", true, true]
     *           ["=ilike", true, false]
     */
    public function testLike(string $expectedOperator, bool $strict = false, bool $caseSensitive = true): void
    {
        $domain = $this->expressionBuilder->like($this->fieldName, $this->fieldValue, $strict, $caseSensitive);
        static::assertInstanceOf(Comparison::class, $domain);
        static::assertEquals($expectedOperator, $domain->getOperator());
        static::assertEquals($this->fieldName, $domain->getFieldName());
        static::assertEquals($this->fieldValue, $domain->getValue());
    }

    /**
     * @covers ::notLike
     *
     * @testWith ["not like", true]
     *           ["not ilike", false]
     */
    public function testNotLike(string $expectedOperator, bool $caseSensitive = true): void
    {
        $domain = $this->expressionBuilder->notLike($this->fieldName, $this->fieldValue, $caseSensitive);
        static::assertInstanceOf(Comparison::class, $domain);
        static::assertEquals($expectedOperator, $domain->getOperator());
        static::assertEquals($this->fieldName, $domain->getFieldName());
        static::assertEquals($this->fieldValue, $domain->getValue());
    }

    /**
     * @covers ::in
     *
     * @testWith [true, [true]]
     *           [1, [1]]
     *           [1.1, [1.1]]
     *           ["1", ["1"]]
     *           [["1"], ["1"]]
     */
    public function testIn(array|bool|float|int|string $values, array $expectedValues): void
    {
        $domain = $this->expressionBuilder->in($this->fieldName, $values);
        static::assertInstanceOf(Comparison::class, $domain);
        static::assertEquals(Comparison::IN, $domain->getOperator());
        static::assertEquals($this->fieldName, $domain->getFieldName());
        static::assertEquals($expectedValues, $domain->getValue());
    }

    /**
     * @covers ::notIn
     *
     * @testWith [true, [true]]
     *           [1, [1]]
     *           [1.1, [1.1]]
     *           ["1", ["1"]]
     *           [["1"], ["1"]]
     */
    public function testNotIn(array|bool|float|int|string $values, array $expectedValues): void
    {
        $domain = $this->expressionBuilder->notIn($this->fieldName, $values);
        static::assertInstanceOf(Comparison::class, $domain);
        static::assertEquals(Comparison::NOT_IN, $domain->getOperator());
        static::assertEquals($this->fieldName, $domain->getFieldName());
        static::assertEquals($expectedValues, $domain->getValue());
    }

    /**
     * @covers ::createRecord
     */
    public function testCreateRecord(): void
    {
        $operation = $this->expressionBuilder->createRecord($this->dataSet);
        static::assertInstanceOf(CollectionOperation::class, $operation);
        static::assertEquals(CollectionOperation::CREATE, $operation->getType());
        static::assertEquals(0, $operation->getId());
        static::assertEquals($this->dataSet, $operation->getData());
    }

    /**
     * @covers ::updateRecord
     */
    public function testUpdateRecord(): void
    {
        $operation = $this->expressionBuilder->updateRecord($id = 3, $this->dataSet);
        static::assertInstanceOf(CollectionOperation::class, $operation);
        static::assertEquals(CollectionOperation::UPDATE, $operation->getType());
        static::assertEquals($id, $operation->getId());
        static::assertEquals($this->dataSet, $operation->getData());
    }

    /**
     * @covers ::updateRecord
     */
    public function testUpdateRecordWithEmptyData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expressionBuilder->updateRecord($id = 3, []);
    }

    /**
     * @covers ::addRecord
     */
    public function testAddRecord(): void
    {
        $operation = $this->expressionBuilder->addRecord($id = 3);
        static::assertInstanceOf(CollectionOperation::class, $operation);
        static::assertEquals(CollectionOperation::ADD, $operation->getType());
        static::assertEquals($id, $operation->getId());
        static::assertEquals(0, $operation->getData());
    }

    /**
     * @covers ::removeRecord
     */
    public function testRemoveRecord(): void
    {
        $operation = $this->expressionBuilder->removeRecord($id = 3);
        static::assertInstanceOf(CollectionOperation::class, $operation);
        static::assertEquals(CollectionOperation::REMOVE, $operation->getType());
        static::assertEquals($id, $operation->getId());
        static::assertEquals(0, $operation->getData());
    }

    /**
     * @covers ::deleteRecord
     */
    public function testDeleteRecord(): void
    {
        $operation = $this->expressionBuilder->deleteRecord($id = 3);
        static::assertInstanceOf(CollectionOperation::class, $operation);
        static::assertEquals(CollectionOperation::DELETE, $operation->getType());
        static::assertEquals($id, $operation->getId());
        static::assertEquals(0, $operation->getData());
    }

    /**
     * @covers ::clearRecords
     */
    public function testClearRecords(): void
    {
        $operation = $this->expressionBuilder->clearRecords();
        static::assertInstanceOf(CollectionOperation::class, $operation);
        static::assertEquals(CollectionOperation::CLEAR, $operation->getType());
        static::assertEquals(0, $operation->getId());
        static::assertEquals(0, $operation->getData());
    }
}
