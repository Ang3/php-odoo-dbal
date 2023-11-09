<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Query;

use Ang3\Component\Odoo\DBAL\Query\Enum\OrmQueryMethod;
use Ang3\Component\Odoo\DBAL\Query\LazyResult;
use Ang3\Component\Odoo\DBAL\Query\NoResultException;
use Ang3\Component\Odoo\DBAL\Query\NoUniqueResultException;
use Ang3\Component\Odoo\DBAL\Query\OrmQuery;
use Ang3\Component\Odoo\DBAL\Query\QueryException;
use Ang3\Component\Odoo\DBAL\Query\QueryInterface;
use Ang3\Component\Odoo\DBAL\RecordManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Query\OrmQuery
 *
 * @internal
 */
final class OrmQueryTest extends TestCase
{
    private MockObject $recordManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recordManager = $this->createMock(RecordManager::class);
    }

    public function testQueryInterface(): void
    {
        $class = new \ReflectionClass(OrmQuery::class);

        self::assertTrue($class->implementsInterface(QueryInterface::class));
    }

    /**
     * @covers ::count
     */
    public function testCount(): void
    {
        $query = new OrmQuery($this->recordManager, 'model_name', OrmQueryMethod::SearchAndCount->value);
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn($expectedResult = 1337);

        $result = $query->count();
        self::assertEquals($expectedResult, $result);
    }

    public static function searchMethodsProvider(): iterable
    {
        return [
            [OrmQueryMethod::Search->value],
            [OrmQueryMethod::SearchAndRead->value],
        ];
    }

    /**
     * @covers ::count
     *
     * @dataProvider searchMethodsProvider
     */
    public function testCountOnSearch(string $method): void
    {
        $query = new OrmQuery($this->recordManager, 'model_name', $method);
        $query->setParameters([1, 2, 3])->setOptions([4, 5, 6]);
        $this->recordManager->expects(self::once())->method('executeQuery')->with(self::callback(static function (OrmQuery $ormQuery) use ($query) {
            return $query !== $ormQuery
                && $query->getName() === $ormQuery->getName()
                && OrmQueryMethod::SearchAndCount->value === $ormQuery->getMethod()
                && $query->getParameters() === $ormQuery->getParameters();
        }))->willReturn($expectedResult = 1337);

        $result = $query->count();
        self::assertEquals($expectedResult, $result);
    }

    public static function nonSearchMethodsProvider(): iterable
    {
        return [
            [OrmQueryMethod::Create->value],
            [OrmQueryMethod::Write->value],
            [OrmQueryMethod::Read->value],
            [OrmQueryMethod::Unlink->value],
        ];
    }

    /**
     * @covers ::count
     *
     * @dataProvider nonSearchMethodsProvider
     */
    public function testCountOnSearchWithInvalidMethod(string $invalidMethod): void
    {
        $this->expectException(QueryException::class);
        $query = new OrmQuery($this->recordManager, 'model_name', $invalidMethod);

        $query->count();
    }

    /**
     * @covers ::getSingleScalarResult
     *
     * @dataProvider searchMethodsProvider
     */
    public function testGetSingleScalarResult(string $method): void
    {
        $query = new OrmQuery($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn([
            [
                'id' => $expectedResult = 1337,
            ],
        ]);

        self::assertEquals($expectedResult, $query->getSingleScalarResult());
    }

    /**
     * @covers ::getSingleScalarResult
     *
     * @dataProvider nonSearchMethodsProvider
     */
    public function testGetSingleScalarResultWithInvalidMethod(string $invalidMethod): void
    {
        $this->expectException(QueryException::class);
        $query = new OrmQuery($this->recordManager, 'model_name', $invalidMethod);

        $query->getSingleScalarResult();
    }

    /**
     * @covers ::getSingleScalarResult
     *
     * @dataProvider searchMethodsProvider
     */
    public function testGetSingleScalarResultWithNoResult(string $method): void
    {
        $this->expectException(NoResultException::class);
        $query = new OrmQuery($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn([]);

        $query->getSingleScalarResult();
    }

    /**
     * @covers ::getSingleScalarResult
     *
     * @dataProvider searchMethodsProvider
     */
    public function testGetSingleScalarResultWithNonUniqueResult(string $method): void
    {
        $this->expectException(NoUniqueResultException::class);
        $query = new OrmQuery($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn([
            [
                'id' => 1,
            ],
            [
                'id' => 2,
            ],
        ]);

        $query->getSingleScalarResult();
    }

    /**
     * @covers ::getOneOrNullScalarResult
     *
     * @dataProvider searchMethodsProvider
     */
    public function testGetOneOrNullScalarResult(string $method): void
    {
        $query = new OrmQuery($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn([
            [
                'id' => $expectedResult = 1337,
            ],
        ]);

        self::assertSame($expectedResult, $query->getOneOrNullScalarResult());
    }

    /**
     * @covers ::getOneOrNullScalarResult
     *
     * @dataProvider nonSearchMethodsProvider
     */
    public function testGetOneOrNullScalarResultWithInvalidMethod(string $invalidMethod): void
    {
        $this->expectException(QueryException::class);
        $query = new OrmQuery($this->recordManager, 'model_name', $invalidMethod);

        $query->getOneOrNullScalarResult();
    }

    /**
     * @covers ::getOneOrNullScalarResult
     *
     * @dataProvider searchMethodsProvider
     */
    public function testGetOneOrNullScalarResultWithNoResult(string $method): void
    {
        $query = new OrmQuery($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn([]);

        $result = $query->getOneOrNullScalarResult();
        self::assertNull($result);
    }

    /**
     * @covers ::getOneOrNullScalarResult
     *
     * @dataProvider searchMethodsProvider
     */
    public function testGetOneOrNullScalarResultWithNonUniqueResult(string $method): void
    {
        $this->expectException(NoUniqueResultException::class);
        $query = new OrmQuery($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn([
            [
                'id' => 1,
            ],
            [
                'id' => 2,
            ],
        ]);

        $query->getOneOrNullScalarResult();
    }

    /**
     * @covers ::getScalarResult
     *
     * @dataProvider searchMethodsProvider
     */
    public function testGetScalarResult(string $method): void
    {
        $query = new OrmQuery($this->recordManager, 'model_name', $method);
        $query->setOption('fields', ['selected_field_name']);
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn([
            [
                'non_selected_field_name' => 'foo',
                'selected_field_name' => 'bar',
            ],
            [
                'non_selected_field_name' => 'qux',
                'selected_field_name' => 'lux',
            ],
        ]);

        self::assertEquals(['bar', 'lux'], $query->getScalarResult());
    }

    /**
     * @covers ::getScalarResult
     *
     * @dataProvider nonSearchMethodsProvider
     */
    public function testGetScalarResultWithInvalidMethod(string $invalidMethod): void
    {
        $this->expectException(QueryException::class);
        $query = new OrmQuery($this->recordManager, 'model_name', $invalidMethod);

        $query->getScalarResult();
    }

    /**
     * @covers ::getScalarResult
     *
     * @dataProvider searchMethodsProvider
     */
    public function testGetScalarResultWithNoResult(string $method): void
    {
        $query = new OrmQuery($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn([]);

        $result = $query->getScalarResult();
        self::assertEquals([], $result);
    }

    /**
     * @covers ::getSingleResult
     *
     * @dataProvider searchMethodsProvider
     */
    public function testGetSingleResult(string $method): void
    {
        $query = new OrmQuery($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn([
            $firstRow = [
                'non_selected_field_name' => 'foo',
                'selected_field_name' => 'bar',
            ],
        ]);

        self::assertEquals($firstRow, $query->getSingleResult());
    }

    /**
     * @covers ::getSingleResult
     *
     * @dataProvider nonSearchMethodsProvider
     */
    public function testGetSingleResultWithInvalidMethod(string $invalidMethod): void
    {
        $this->expectException(QueryException::class);
        $query = new OrmQuery($this->recordManager, 'model_name', $invalidMethod);

        $query->getSingleResult();
    }

    /**
     * @covers ::getSingleResult
     *
     * @dataProvider searchMethodsProvider
     */
    public function testGetSingleResultWithNoResult(string $method): void
    {
        $this->expectException(NoResultException::class);
        $query = new OrmQuery($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn([]);

        $query->getSingleResult();
    }

    /**
     * @covers ::getSingleResult
     *
     * @dataProvider searchMethodsProvider
     */
    public function testGetSingleResultWithNonUniqueResult(string $method): void
    {
        $this->expectException(NoUniqueResultException::class);
        $query = new OrmQuery($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn([
            [
                'non_selected_field_name' => 'foo',
                'selected_field_name' => 'bar',
            ],
            [
                'non_selected_field_name' => 'qux',
                'selected_field_name' => 'lux',
            ],
        ]);
        $query->getSingleResult();
    }

    /**
     * @covers ::getOneOrNullResult
     *
     * @dataProvider searchMethodsProvider
     */
    public function testGetOneOrNullResult(string $method): void
    {
        $query = new OrmQuery($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn([
            $firstRow = [
                'non_selected_field_name' => 'foo',
                'selected_field_name' => 'bar',
            ],
        ]);

        self::assertEquals($firstRow, $query->getOneOrNullResult());
    }

    /**
     * @covers ::getOneOrNullResult
     *
     * @dataProvider nonSearchMethodsProvider
     */
    public function testGetOneOrNullResultWithInvalidMethod(string $invalidMethod): void
    {
        $this->expectException(QueryException::class);
        $query = new OrmQuery($this->recordManager, 'model_name', $invalidMethod);
        $query->getOneOrNullResult();
    }

    /**
     * @covers ::getOneOrNullResult
     *
     * @dataProvider searchMethodsProvider
     */
    public function testGetOneOrNullResultWithNoResult(string $method): void
    {
        $query = new OrmQuery($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn([]);

        $result = $query->getOneOrNullResult();
        self::assertNull($result);
    }

    /**
     * @covers ::getOneOrNullResult
     *
     * @dataProvider searchMethodsProvider
     */
    public function testGetOneOrNullResultWithNonUniqueResult(string $method): void
    {
        $this->expectException(NoUniqueResultException::class);
        $query = new OrmQuery($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn([
            [
                'non_selected_field_name' => 'foo',
                'selected_field_name' => 'bar',
            ],
            [
                'non_selected_field_name' => 'qux',
                'selected_field_name' => 'lux',
            ],
        ]);
        $query->getOneOrNullResult();
    }

    /**
     * @covers ::getResult
     *
     * @dataProvider searchMethodsProvider
     */
    public function testGetResult(string $method): void
    {
        $query = new OrmQuery($this->recordManager, 'model_name', $method);
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn($result = [
            [
                'non_selected_field_name' => 'foo',
                'selected_field_name' => 'bar',
            ],
            [
                'non_selected_field_name' => 'qux',
                'selected_field_name' => 'lux',
            ],
        ]);

        self::assertSame($result, $query->getResult());
    }

    /**
     * @covers ::getResult
     *
     * @dataProvider nonSearchMethodsProvider
     */
    public function testGetResultWithInvalidMethod(string $invalidMethod): void
    {
        $this->expectException(QueryException::class);
        $query = new OrmQuery($this->recordManager, 'model_name', $invalidMethod);

        $query->getResult();
    }

    /**
     * @covers ::getLazyResult
     *
     * @dataProvider searchMethodsProvider
     */
    public function testGetLazyResult(string $method): void
    {
        $query = new OrmQuery($this->recordManager, 'model_name', $method);

        $result = $query->getLazyResult();
        self::assertInstanceOf(LazyResult::class, $result);
        self::assertEquals(new LazyResult($query), $result);
    }

    /**
     * @covers ::getLazyResult
     *
     * @dataProvider searchMethodsProvider
     */
    public function testGetLazyResultWithBufferSize(string $method): void
    {
        $query = new OrmQuery($this->recordManager, 'model_name', $method);

        $result = $query->getLazyResult($bufferSize = 150);
        self::assertInstanceOf(LazyResult::class, $result);
        self::assertEquals(new LazyResult($query, [LazyResult::BUFFER_SIZE_KEY => $bufferSize]), $result);
    }

    /**
     * @covers ::getLazyResult
     *
     * @dataProvider nonSearchMethodsProvider
     */
    public function testGetLazyResultWithInvalidMethod(string $invalidMethod): void
    {
        $this->expectException(QueryException::class);
        $query = new OrmQuery($this->recordManager, 'model_name', $invalidMethod);

        $query->getLazyResult();
    }
}
