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
use Ang3\Component\Odoo\DBAL\Query\Factory\ResultFactoryInterface;
use Ang3\Component\Odoo\DBAL\Query\Query;
use Ang3\Component\Odoo\DBAL\Query\QueryException;
use Ang3\Component\Odoo\DBAL\Query\QueryInterface;
use Ang3\Component\Odoo\DBAL\Query\Result\NoResultException;
use Ang3\Component\Odoo\DBAL\Query\Result\NoUniqueResultException;
use Ang3\Component\Odoo\DBAL\Query\Result\Paginator;
use Ang3\Component\Odoo\DBAL\Query\Result\ResultInterface;
use Ang3\Component\Odoo\DBAL\Query\Result\RowResult;
use Ang3\Component\Odoo\DBAL\Query\Result\ScalarResult;
use Ang3\Component\Odoo\DBAL\RecordManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Query\Query
 *
 * @internal
 */
final class QueryTest extends TestCase
{
    private MockObject $recordManager;
    private MockObject $resultFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recordManager = $this->createMock(RecordManager::class);
        $this->resultFactory = $this->createMock(ResultFactoryInterface::class);
    }

    /**
     * @covers \Ang3\Component\Odoo\DBAL\Query\Query
     */
    public function testQueryInterface(): void
    {
        $class = new \ReflectionClass(Query::class);

        static::assertTrue($class->implementsInterface(QueryInterface::class));
    }

    /**
     * @covers ::count
     */
    public function testCount(): void
    {
        $query = new Query($this->recordManager, 'model_name', QueryMethod::SearchAndCount->value);
        $this->recordManager->expects(static::once())->method('executeQuery')->with($query)->willReturn($expectedResult = 1337);
        $result = $query->count();
        static::assertSame($expectedResult, $result);
    }

    /**
     * @covers ::count
     *
     * @dataProvider searchMethodsProvider
     */
    public function testCountOnSearch(string $method): void
    {
        $query = new Query($this->recordManager, 'model_name', $method);
        $query->setParameters([1, 2, 3])->setOptions([4, 5, 6]);
        $this->recordManager->expects(static::once())->method('executeQuery')->with(static::callback(static function (Query $currentQuery) use ($query) {
            return $query !== $currentQuery
                && $query->getName() === $currentQuery->getName()
                && QueryMethod::SearchAndCount->value === $currentQuery->getMethod()
                && $query->getParameters() === $currentQuery->getParameters();
        }))->willReturn($expectedResult = 1337);
        $result = $query->count();
        static::assertSame($expectedResult, $result);
    }

    /**
     * @covers ::count
     */
    public function testCountOnSearchForInsert(): void
    {
        $query = new Query($this->recordManager, 'model_name', QueryMethod::Insert->value);
        static::assertSame(1, $query->count());
    }

    /**
     * @covers ::count
     */
    public function testCountOnSearchForUpdate(): void
    {
        $query = new Query($this->recordManager, 'model_name', QueryMethod::Update->value);
        $query->setParameters([$ids = [1, 2, 3], ['name' => 'foo']]);
        static::assertSame(\count($ids), $query->count());
    }

    /**
     * @covers ::count
     */
    public function testCountOnSearchForDeletion(): void
    {
        $query = new Query($this->recordManager, 'model_name', QueryMethod::Delete->value);
        $query->setParameters([$ids = [1, 2, 3]]);
        static::assertSame(\count($ids), $query->count());
    }

    /**
     * @covers ::getSingleScalarResult
     *
     * @testWith ["search"]
     *           ["search_read"]
     */
    public function testGetSingleScalarResult(string $method): void
    {
        $query = new Query($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(static::once())->method('getResultFactory')->willReturn($this->resultFactory);
        $scalarResult = $this->createMock(ScalarResult::class);
        $this->resultFactory->expects(static::once())->method('createScalarResult')->willReturn($scalarResult);
        $scalarResult->expects(static::once())->method('count')->willReturn(1);
        $scalarResult->expects(static::once())->method('first')->willReturn($result = 1337);
        static::assertSame($result, $query->getSingleScalarResult());
    }

    /**
     * @covers ::getSingleScalarResult
     *
     * @testWith ["search"]
     *           ["search_read"]
     */
    public function testGetSingleScalarResultWithNoResult(string $method): void
    {
        self::expectException(NoResultException::class);
        $query = new Query($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(static::once())->method('getResultFactory')->willReturn($this->resultFactory);
        $scalarResult = $this->createMock(ScalarResult::class);
        $this->resultFactory->expects(static::once())->method('createScalarResult')->willReturn($scalarResult);
        $scalarResult->expects(static::once())->method('count')->willReturn(0);
        $scalarResult->expects(static::once())->method('first')->willReturn(null);
        $query->getSingleScalarResult();
    }

    /**
     * @covers ::getSingleScalarResult
     *
     * @testWith ["search"]
     *           ["search_read"]
     */
    public function testGetSingleScalarResultWithNoUniqueResult(string $method): void
    {
        self::expectException(NoUniqueResultException::class);
        $query = new Query($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(static::once())->method('getResultFactory')->willReturn($this->resultFactory);
        $scalarResult = $this->createMock(ScalarResult::class);
        $this->resultFactory->expects(static::once())->method('createScalarResult')->willReturn($scalarResult);
        $scalarResult->expects(static::once())->method('count')->willReturn(2);
        $scalarResult->expects(static::never())->method('first');
        $query->getSingleScalarResult();
    }

    /**
     * @covers ::getSingleScalarResult
     *
     * @testWith ["create"]
     *           ["write"]
     *           ["search_count"]
     *           ["unlink"]
     */
    public function testGetSingleScalarResultWithInvalidMethod(string $invalidMethod): void
    {
        self::expectException(QueryException::class);
        $query = new Query($this->recordManager, 'model_name', $invalidMethod);
        $query->getSingleScalarResult();
    }

    /**
     * @covers ::getOneOrNullScalarResult
     *
     * @testWith ["search"]
     *           ["search_read"]
     */
    public function testGetOneOrNullScalarResult(string $method): void
    {
        $query = new Query($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(static::once())->method('getResultFactory')->willReturn($this->resultFactory);
        $scalarResult = $this->createMock(ScalarResult::class);
        $this->resultFactory->expects(static::once())->method('createScalarResult')->willReturn($scalarResult);
        $scalarResult->expects(static::once())->method('count')->willReturn(1);
        $scalarResult->expects(static::once())->method('first')->willReturn($result = 1337);
        static::assertSame($result, $query->getOneOrNullScalarResult());
    }

    /**
     * @covers ::getOneOrNullScalarResult
     *
     * @testWith ["search"]
     *           ["search_read"]
     */
    public function testGetOneOrNullScalarResultWithNoResult(string $method): void
    {
        $query = new Query($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(static::once())->method('getResultFactory')->willReturn($this->resultFactory);
        $scalarResult = $this->createMock(ScalarResult::class);
        $this->resultFactory->expects(static::once())->method('createScalarResult')->willReturn($scalarResult);
        $scalarResult->expects(static::once())->method('count')->willReturn(0);
        $scalarResult->expects(static::once())->method('first')->willReturn(null);
        static::assertNull($query->getOneOrNullScalarResult());
    }

    /**
     * @covers ::getOneOrNullScalarResult
     *
     * @testWith ["search"]
     *           ["search_read"]
     */
    public function testGetOneOrNullScalarResultWithNoUniqueResult(string $method): void
    {
        self::expectException(NoUniqueResultException::class);
        $query = new Query($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(static::once())->method('getResultFactory')->willReturn($this->resultFactory);
        $scalarResult = $this->createMock(ScalarResult::class);
        $this->resultFactory->expects(static::once())->method('createScalarResult')->willReturn($scalarResult);
        $scalarResult->expects(static::once())->method('count')->willReturn(2);
        $scalarResult->expects(static::never())->method('first');
        $query->getOneOrNullScalarResult();
    }

    /**
     * @covers ::getOneOrNullScalarResult
     *
     * @testWith ["create"]
     *           ["write"]
     *           ["search_count"]
     *           ["unlink"]
     */
    public function testGetOneOrNullScalarResultWithInvalidMethod(string $invalidMethod): void
    {
        self::expectException(QueryException::class);
        $query = new Query($this->recordManager, 'model_name', $invalidMethod);
        $query->getOneOrNullScalarResult();
    }

    /**
     * @covers ::getSingleResult
     *
     * @testWith ["search_read"]
     */
    public function testGetSingleResult(string $method): void
    {
        $query = new Query($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(static::once())->method('getResultFactory')->willReturn($this->resultFactory);
        $rowResult = $this->createMock(RowResult::class);
        $this->resultFactory->expects(static::once())->method('createRowResult')->willReturn($rowResult);
        $rowResult->expects(static::once())->method('count')->willReturn(1);
        $rowResult->expects(static::once())->method('fetchAssociative')->willReturn($result = [
            'foo' => 'bar',
        ]);
        static::assertSame($result, $query->getSingleResult());
    }

    /**
     * @covers ::getSingleResult
     *
     * @testWith ["search_read"]
     */
    public function testGetSingleResultWithNoUniqueResult(string $method): void
    {
        self::expectException(NoUniqueResultException::class);
        $query = new Query($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(static::once())->method('getResultFactory')->willReturn($this->resultFactory);
        $rowResult = $this->createMock(RowResult::class);
        $this->resultFactory->expects(static::once())->method('createRowResult')->willReturn($rowResult);
        $rowResult->expects(static::once())->method('count')->willReturn(2);
        $rowResult->expects(static::never())->method('fetchAssociative');
        $query->getSingleResult();
    }

    /**
     * @covers ::getSingleResult
     *
     * @testWith ["search_read"]
     */
    public function testGetSingleResultWithNoResult(string $method): void
    {
        self::expectException(NoResultException::class);
        $query = new Query($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(static::once())->method('getResultFactory')->willReturn($this->resultFactory);
        $rowResult = $this->createMock(RowResult::class);
        $this->resultFactory->expects(static::once())->method('createRowResult')->willReturn($rowResult);
        $rowResult->expects(static::once())->method('count')->willReturn(0);
        $rowResult->expects(static::once())->method('fetchAssociative')->willReturn(false);
        $query->getSingleResult();
    }

    /**
     * @covers ::getSingleResult
     *
     * @testWith ["create"]
     *           ["write"]
     *           ["search"]
     *           ["search_count"]
     *           ["unlink"]
     */
    public function testGetSingleResultWithInvalidMethod(string $invalidMethod): void
    {
        self::expectException(QueryException::class);
        $query = new Query($this->recordManager, 'model_name', $invalidMethod);
        $query->getSingleResult();
    }

    /**
     * @covers ::getOneOrNullResult
     *
     * @testWith ["search_read"]
     */
    public function testGetOneOrNullResult(string $method): void
    {
        $query = new Query($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(static::once())->method('getResultFactory')->willReturn($this->resultFactory);
        $rowResult = $this->createMock(RowResult::class);
        $this->resultFactory->expects(static::once())->method('createRowResult')->willReturn($rowResult);
        $rowResult->expects(static::once())->method('count')->willReturn(1);
        $rowResult->expects(static::once())->method('fetchAssociative')->willReturn($result = [
            'foo' => 'bar',
        ]);
        static::assertSame($result, $query->getOneOrNullResult());
    }

    /**
     * @covers ::getOneOrNullResult
     *
     * @testWith ["search_read"]
     */
    public function testGetOneOrNullResultWithNoResult(string $method): void
    {
        $query = new Query($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(static::once())->method('getResultFactory')->willReturn($this->resultFactory);
        $rowResult = $this->createMock(RowResult::class);
        $this->resultFactory->expects(static::once())->method('createRowResult')->willReturn($rowResult);
        $rowResult->expects(static::once())->method('count')->willReturn(0);
        $rowResult->expects(static::once())->method('fetchAssociative')->willReturn(false);
        static::assertNull($query->getOneOrNullResult());
    }

    /**
     * @covers ::getOneOrNullResult
     *
     * @testWith ["search_read"]
     */
    public function testGetOneOrNullResultWithNoUniqueResult(string $method): void
    {
        self::expectException(NoUniqueResultException::class);
        $query = new Query($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(static::once())->method('getResultFactory')->willReturn($this->resultFactory);
        $rowResult = $this->createMock(RowResult::class);
        $this->resultFactory->expects(static::once())->method('createRowResult')->willReturn($rowResult);
        $rowResult->expects(static::once())->method('count')->willReturn(2);
        $rowResult->expects(static::never())->method('fetchAssociative');
        $query->getOneOrNullResult();
    }

    /**
     * @covers ::getOneOrNullResult
     *
     * @testWith ["create"]
     *           ["write"]
     *           ["search"]
     *           ["search_count"]
     *           ["unlink"]
     */
    public function testGetOneOrNullResultWithInvalidMethod(string $invalidMethod): void
    {
        self::expectException(QueryException::class);
        $query = new Query($this->recordManager, 'model_name', $invalidMethod);
        $query->getOneOrNullResult();
    }

    /**
     * @covers ::getResult
     *
     * @testWith ["search"]
     *           ["search_read"]
     */
    public function testGetResult(string $method): void
    {
        $query = new Query($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(static::once())->method('getResultFactory')->willReturn($this->resultFactory);
        $result = $this->createMock(ResultInterface::class);
        $this->resultFactory->expects(static::once())->method('create')->willReturn($result);
        static::assertSame($result, $query->getResult());
    }

    /**
     * @covers ::getResult
     *
     * @testWith ["create"]
     *           ["write"]
     *           ["search_count"]
     *           ["unlink"]
     */
    public function testGetResultWithInvalidMethod(string $invalidMethod): void
    {
        self::expectException(QueryException::class);
        $query = new Query($this->recordManager, 'model_name', $invalidMethod);
        $query->getResult();
    }

    /**
     * @covers ::getScalarResult
     *
     * @testWith ["search"]
     *           ["search_read"]
     */
    public function testGetScalarResult(string $method): void
    {
        $query = new Query($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(static::once())->method('getResultFactory')->willReturn($this->resultFactory);
        $result = $this->createMock(ScalarResult::class);
        $this->resultFactory->expects(static::once())->method('createScalarResult')->willReturn($result);
        static::assertSame($result, $query->getScalarResult());
    }

    /**
     * @covers ::getScalarResult
     *
     * @testWith ["create"]
     *           ["write"]
     *           ["search_count"]
     *           ["unlink"]
     */
    public function testGetScalarResultWithInvalidMethod(string $invalidMethod): void
    {
        self::expectException(QueryException::class);
        $query = new Query($this->recordManager, 'model_name', $invalidMethod);
        $query->getScalarResult();
    }

    /**
     * @covers ::getRowResult
     *
     * @testWith ["search_read"]
     */
    public function testGetRowResult(string $method): void
    {
        $query = new Query($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(static::once())->method('getResultFactory')->willReturn($this->resultFactory);
        $result = $this->createMock(RowResult::class);
        $this->resultFactory->expects(static::once())->method('createRowResult')->willReturn($result);
        static::assertSame($result, $query->getRowResult());
    }

    /**
     * @covers ::getRowResult
     *
     * @testWith ["create"]
     *           ["write"]
     *           ["search"]
     *           ["search_count"]
     *           ["unlink"]
     */
    public function testGetRowResultWithInvalidMethod(string $invalidMethod): void
    {
        self::expectException(QueryException::class);
        $query = new Query($this->recordManager, 'model_name', $invalidMethod);
        $query->getRowResult();
    }

    /**
     * @covers ::paginate
     *
     * @testWith ["search"]
     *           ["search_read"]
     */
    public function testPaginate(string $invalidMethod): void
    {
        $query = new Query($this->recordManager, 'model_name', $invalidMethod);
        $paginator = $query->paginate($nbItemsPerPage = 3, $context = ['foo' => 'bar']);

        static::assertInstanceOf(Paginator::class, $paginator);
        static::assertNotSame($query, $paginator->getQuery());
        static::assertSame($query->getName(), $paginator->getQuery()->getName());
        static::assertSame($query->getMethod(), $paginator->getQuery()->getMethod());
        static::assertSame($query->getParameters(), $paginator->getQuery()->getParameters());
        static::assertSame($query->getOptions(), $paginator->getQuery()->getOptions());
        static::assertSame($nbItemsPerPage, $paginator->getNbItemsPerPage());
        static::assertSame($context, $paginator->getContext());
    }

    /**
     * @covers ::paginate
     *
     * @testWith ["create"]
     *           ["write"]
     *           ["search_count"]
     *           ["unlink"]
     */
    public function testPaginateWithInvalidMethod(string $invalidMethod): void
    {
        self::expectException(QueryException::class);
        $query = new Query($this->recordManager, 'model_name', $invalidMethod);
        $query->paginate();
    }

    /**
     * @internal
     */
    protected static function searchMethodsProvider(): iterable
    {
        return [
            [QueryMethod::Search->value],
            [QueryMethod::SearchAndRead->value],
        ];
    }

    /**
     * @internal
     */
    protected static function nonSearchMethodsProvider(): iterable
    {
        return [
            [QueryMethod::Insert->value],
            [QueryMethod::Update->value],
            [QueryMethod::Read->value],
            [QueryMethod::Delete->value],
        ];
    }
}
