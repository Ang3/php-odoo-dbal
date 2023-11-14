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
use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;
use Ang3\Component\Odoo\DBAL\Schema\SchemaInterface;
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

        $this->assertResult($this->recordManager, false);
        $result = $query->count();
        self::assertEquals($expectedResult, $result);
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

        $this->assertResult($this->recordManager, false);
        $result = $query->count();
        self::assertEquals($expectedResult, $result);
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

        $this->assertResult($this->recordManager, false);
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
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn($result = [
            [
                'id' => $expectedResult = 1337,
            ],
        ]);

        $this->assertResult($this->recordManager, $result);
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

        $this->assertResult($this->recordManager, false);
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
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn($result = []);

        $this->assertResult($this->recordManager, $result);
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
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn($result = [
            [
                'id' => 1,
            ],
            [
                'id' => 2,
            ],
        ]);

        $this->assertResult($this->recordManager, $result);
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
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn($result = [
            [
                'id' => $expectedResult = 1337,
            ],
        ]);

        $this->assertResult($this->recordManager, $result);
        self::assertEquals($expectedResult, $query->getOneOrNullScalarResult());
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

        $this->assertResult($this->recordManager, false);
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
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn($result = []);

        $this->assertResult($this->recordManager, $result);
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
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn($result = [
            [
                'id' => 1,
            ],
            [
                'id' => 2,
            ],
        ]);

        $this->assertResult($this->recordManager, $result);
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

        $this->assertResult($this->recordManager, $result);
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

        $this->assertResult($this->recordManager, false);
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
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn($result = []);

        $this->assertResult($this->recordManager, $result);
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
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn($result = [
            $firstRow = [
                'non_selected_field_name' => 'foo',
                'selected_field_name' => 'bar',
            ],
        ]);

        $this->assertResult($this->recordManager, $result);
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

        $this->assertResult($this->recordManager, false);
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
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn($result = []);

        $this->assertResult($this->recordManager, $result);
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

        $this->assertResult($this->recordManager, $result);
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
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn($result = [
            $firstRow = [
                'non_selected_field_name' => 'foo',
                'selected_field_name' => 'bar',
            ],
        ]);

        $this->assertResult($this->recordManager, $result);
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

        $this->assertResult($this->recordManager, false);
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
        $this->recordManager->expects(self::once())->method('executeQuery')->with($query)->willReturn($result = []);

        $this->assertResult($this->recordManager, $result);
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

        $this->assertResult($this->recordManager, $result);
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

        $this->assertResult($this->recordManager, $result);
        self::assertEquals($result, $query->getResult());
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

        $this->assertResult($this->recordManager, false);
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

    /**
     * @internal
     */
    protected static function searchMethodsProvider(): iterable
    {
        return [
            [OrmQueryMethod::Search->value],
            [OrmQueryMethod::SearchAndRead->value],
        ];
    }

    /**
     * @internal
     */
    protected static function nonSearchMethodsProvider(): iterable
    {
        return [
            [OrmQueryMethod::Create->value],
            [OrmQueryMethod::Write->value],
            [OrmQueryMethod::Read->value],
            [OrmQueryMethod::Unlink->value],
        ];
    }

    /**
     * @internal
     */
    private function assertResult(MockObject $recordManager, array|false $result): void
    {
        $schema = $this->createMock(SchemaInterface::class);

        if (\is_array($result)) {
            $recordManager->expects(self::once())->method('getSchema')->willReturn($schema);
            $modelMetadata = $this->createMock(ModelMetadata::class);
            $schema->expects(self::once())->method('getModel')->willReturn($modelMetadata);
            $recordManager->expects(self::once())->method('normalizeResult')->with($modelMetadata, $result)->willReturn($result);
        } else {
            $recordManager->expects(self::never())->method('getSchema');
            $schema->expects(self::never())->method('getModel');
            $recordManager->expects(self::never())->method('normalizeResult');
        }
    }
}
