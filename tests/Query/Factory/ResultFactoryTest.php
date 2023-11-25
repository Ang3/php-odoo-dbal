<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Query\Factory;

use Ang3\Component\Odoo\DBAL\Query\Enum\QueryMethod;
use Ang3\Component\Odoo\DBAL\Query\Factory\ResultFactory;
use Ang3\Component\Odoo\DBAL\Query\Factory\ResultFactoryInterface;
use Ang3\Component\Odoo\DBAL\Query\Normalizer\ResultNormalizerInterface;
use Ang3\Component\Odoo\DBAL\Query\QueryInterface;
use Ang3\Component\Odoo\DBAL\Query\Result\RowResult;
use Ang3\Component\Odoo\DBAL\Query\Result\ScalarResult;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;
use Ang3\Component\Odoo\DBAL\Schema\SchemaInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Query\Factory\ResultFactory
 *
 * @internal
 */
final class ResultFactoryTest extends TestCase
{
    private ResultFactory $resultFactory;
    private MockObject $schema;
    private MockObject $resultNormalizer;
    private MockObject $query;

    private array $data = [
        ['foo' => 'bar'],
        ['baz' => 'qux'],
    ];

    private array $defaultContext = [
        ResultFactory::COLUMN_NAME_KEY => null,
        ResultFactory::BUFFER_SIZE_KEY => null,
        'foo' => 'bar',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->schema = $this->createMock(SchemaInterface::class);
        $this->resultNormalizer = $this->createMock(ResultNormalizerInterface::class);
        $this->query = $this->createMock(QueryInterface::class);
        $this->resultFactory = new ResultFactory($this->schema, $this->resultNormalizer, $this->defaultContext);
    }

    /**
     * @covers \Ang3\Component\Odoo\DBAL\Query\Factory\ResultFactory
     */
    public function testInterface(): void
    {
        static::assertInstanceOf(ResultFactoryInterface::class, $this->resultFactory);
    }

    /**
     * @covers ::create
     *
     * @testWith ["search_read"]
     *
     * @depends testCreateRowResult
     */
    public function testCreateForRowResult(string $method): void
    {
        [$modelName, $context] = ['model_name', ['key' => 'value']];
        $this->query->expects(static::once())->method('getMethod')->willReturn($method);
        $this->query->expects(static::once())->method('getName')->willReturn($modelName);
        $model = $this->createMock(ModelMetadata::class);
        $this->schema->expects(static::once())->method('getModel')->with($modelName)->willReturn($model);

        $mergedContext = array_merge($this->defaultContext, $context);
        $this->resultNormalizer->expects(static::exactly(2))->method('normalize')
            ->withConsecutive([$model, $this->data[0], $mergedContext], [$model, $this->data[1], $mergedContext])
            ->willReturnOnConsecutiveCalls($firstUpdatedRow = ['foo' => 'bar_updated'], $secondUpdatedRow = ['baz' => 'qux_updated'])
        ;

        $result = $this->resultFactory->create($this->query, $this->data, $context);
        static::assertInstanceOf(RowResult::class, $result);
        static::assertSame([$firstUpdatedRow, $secondUpdatedRow], $result->toArray());
    }

    /**
     * @covers ::create
     *
     * @testWith ["search"]
     *
     * @depends testCreateRowResult
     */
    public function testCreateForScalarResult(string $method): void
    {
        [$context] = [['key' => 'value']];
        $this->query->expects(static::exactly(2))->method('getMethod')->willReturn($method);

        $result = $this->resultFactory->create($this->query, $ids = [1, 2, 3], $context);
        static::assertInstanceOf(ScalarResult::class, $result);
        static::assertSame($ids, $result->toArray());
    }

    /**
     * @covers ::createScalarResult
     */
    public function testCreateScalarResultOnSearch(): void
    {
        [$context] = [['key' => 'value']];
        $this->query->expects(static::once())->method('getMethod')->willReturn(QueryMethod::Search->value);

        $result = $this->resultFactory->createScalarResult($this->query, [1, 2, 3], $context);
        static::assertInstanceOf(ScalarResult::class, $result);
        static::assertSame([1, 2, 3], $result->toArray());
    }

    /**
     * @covers ::createScalarResult
     */
    public function testCreateScalarResultOnSelectionWithColumnName(): void
    {
        $this->query->expects(static::once())->method('getMethod')->willReturn(QueryMethod::SearchAndRead->value);
        [$data, $context] = [
            [
                ['foo' => 1, 'bar' => 2, 'baz' => 3],
                ['foo' => 2, 'bar' => 2, 'baz' => 3],
                ['foo' => 3, 'bar' => 2, 'baz' => 3],
            ], [
                ResultFactory::COLUMN_NAME_KEY => 'foo',
            ],
        ];

        $model = $this->createMock(ModelMetadata::class);
        $this->schema->expects(static::once())->method('getModel')->with($model->getName())->willReturn($model);

        $mergedContext = array_merge($this->defaultContext, $context);
        $this->resultNormalizer->expects(static::exactly(\count($data)))->method('normalize')
            ->withConsecutive(
                [$model, $data[0], $mergedContext],
                [$model, $data[1], $mergedContext],
                [$model, $data[2], $mergedContext]
            )
            ->willReturnOnConsecutiveCalls(
                $data[0],
                $data[1],
                $data[2]
            )
        ;

        $result = $this->resultFactory->createScalarResult($this->query, $data, $context);

        static::assertInstanceOf(ScalarResult::class, $result);
        static::assertSame([1, 2, 3], $result->toArray());
    }

    /**
     * @covers ::createRowResult
     */
    public function testCreateRowResult(): void
    {
        [$modelName, $context] = ['model_name', ['key' => 'value']];

        $this->query->expects(static::once())->method('getName')->willReturn($modelName);
        $model = $this->createMock(ModelMetadata::class);
        $this->schema->expects(static::once())->method('getModel')->with($modelName)->willReturn($model);

        $mergedContext = array_merge($this->defaultContext, $context);
        $this->resultNormalizer->expects(static::exactly(2))->method('normalize')
            ->withConsecutive([$model, $this->data[0], $mergedContext], [$model, $this->data[1], $mergedContext])
            ->willReturnOnConsecutiveCalls($firstUpdatedRow = ['foo' => $this->data[0]['foo'].'updated'], $secondUpdatedRow = ['baz' => $this->data[1]['baz'].'updated'])
        ;

        $result = $this->resultFactory->createRowResult($this->query, $this->data, $context);
        static::assertInstanceOf(RowResult::class, $result);
        static::assertSame([$firstUpdatedRow, $secondUpdatedRow], $result->toArray());
    }
}
