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
use Ang3\Component\Odoo\DBAL\Query\Factory\QueryFactory;
use Ang3\Component\Odoo\DBAL\Query\Factory\QueryFactoryInterface;
use Ang3\Component\Odoo\DBAL\Query\Normalizer\QueryNormalizerInterface;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\Query\QueryException;
use Ang3\Component\Odoo\DBAL\Query\QueryInterface;
use Ang3\Component\Odoo\DBAL\RecordManagerInterface;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;
use Ang3\Component\Odoo\DBAL\Schema\SchemaInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Query\Factory\QueryFactory
 *
 * @internal
 */
final class QueryFactoryTest extends TestCase
{
    private QueryFactory $queryFactory;
    private MockObject $recordManager;
    private MockObject $queryNormalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recordManager = $this->createMock(RecordManagerInterface::class);
        $this->queryNormalizer = $this->createMock(QueryNormalizerInterface::class);
        $this->queryFactory = new QueryFactory($this->recordManager, $this->queryNormalizer);
    }

    /**
     * @covers \Ang3\Component\Odoo\DBAL\Query\Factory\QueryFactory
     */
    public function testInterface(): void
    {
        static::assertInstanceOf(QueryFactoryInterface::class, $this->queryFactory);
    }

    /**
     * @covers ::createQueryBuilder
     */
    public function testCreateQueryBuilder(): void
    {
        $modelName = 'model_name';
        $queryBuilder = $this->queryFactory->createQueryBuilder($modelName);

        static::assertInstanceOf(QueryBuilder::class, $queryBuilder);
        static::assertSame($this->recordManager, $queryBuilder->getRecordManager());
        static::assertSame($modelName, $queryBuilder->getFrom());
    }

    /**
     * @covers ::createQuery
     *
     * @testWith ["search"]
     *           ["search_read"]
     */
    public function testCreateQueryOnRead(string $method): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        [$modelName] = ['model_name'];

        $schema = $this->createMock(SchemaInterface::class);
        $this->recordManager->expects(static::once())->method('getSchema')->willReturn($schema);
        $model = $this->createMock(ModelMetadata::class);
        $schema->expects(static::once())->method('getModel')->with($modelName)->willReturn($model);
        $model->expects(static::once())->method('getName')->willReturn($modelName);
        $queryBuilder->expects(static::once())->method('getFrom')->willReturn($modelName);
        $queryBuilder->expects(static::once())->method('getMethod')->willReturn(QueryMethod::from($method));
        $this->queryNormalizer->expects(static::once())->method('normalizeDomains')->with($queryBuilder, $model)->willReturn($parameters = [
            'foo' => 'bar',
        ]);
        $this->queryNormalizer->expects(static::once())->method('normalizeOptions')->with($queryBuilder, $model)->willReturn($options = [
            'baz' => 'qux',
        ]);

        $query = $this->queryFactory->createQuery($queryBuilder);
        static::assertInstanceOf(QueryInterface::class, $query);
        static::assertSame($modelName, $query->getName());
        static::assertSame($method, $query->getMethod());
        static::assertSame($parameters, $query->getParameters());
        static::assertSame($options, $query->getOptions());
    }

    /**
     * @covers ::createQuery
     *
     * @testWith ["unlink"]
     */
    public function testCreateQueryOnDeletion(string $method): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        [$modelName] = ['model_name'];

        $schema = $this->createMock(SchemaInterface::class);
        $this->recordManager->expects(static::once())->method('getSchema')->willReturn($schema);
        $model = $this->createMock(ModelMetadata::class);
        $schema->expects(static::once())->method('getModel')->with($modelName)->willReturn($model);
        $model->expects(static::once())->method('getName')->willReturn($modelName);
        $queryBuilder->expects(static::once())->method('getFrom')->willReturn($modelName);
        $queryBuilder->expects(static::once())->method('getMethod')->willReturn(QueryMethod::from($method));
        $queryBuilder->expects(static::once())->method('getIds')->willReturn($ids = [1, 2, 3]);

        $query = $this->queryFactory->createQuery($queryBuilder);
        static::assertInstanceOf(QueryInterface::class, $query);
        static::assertSame($modelName, $query->getName());
        static::assertSame($method, $query->getMethod());
        static::assertSame([$ids], $query->getParameters());
        static::assertSame([], $query->getOptions());
    }

    /**
     * @covers ::createQuery
     *
     * @testWith ["unlink"]
     */
    public function testCreateQueryOnDeletionWithoutIds(string $method): void
    {
        $this->expectException(QueryException::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        [$modelName] = ['model_name'];

        $schema = $this->createMock(SchemaInterface::class);
        $this->recordManager->expects(static::once())->method('getSchema')->willReturn($schema);
        $model = $this->createMock(ModelMetadata::class);
        $schema->expects(static::once())->method('getModel')->with($modelName)->willReturn($model);
        $model->expects(static::once())->method('getName')->willReturn($modelName);
        $queryBuilder->expects(static::once())->method('getFrom')->willReturn($modelName);
        $queryBuilder->expects(static::once())->method('getMethod')->willReturn(QueryMethod::from($method));
        $queryBuilder->expects(static::once())->method('getIds')->willReturn([]);

        $this->queryFactory->createQuery($queryBuilder);
    }

    /**
     * @covers ::createQuery
     *
     * @testWith ["create"]
     */
    public function testCreateQueryOnCreate(string $method): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        [$modelName] = ['model_name'];

        $schema = $this->createMock(SchemaInterface::class);
        $this->recordManager->expects(static::once())->method('getSchema')->willReturn($schema);
        $model = $this->createMock(ModelMetadata::class);
        $schema->expects(static::once())->method('getModel')->with($modelName)->willReturn($model);
        $model->expects(static::once())->method('getName')->willReturn($modelName);
        $queryBuilder->expects(static::once())->method('getFrom')->willReturn($modelName);
        $queryBuilder->expects(static::once())->method('getMethod')->willReturn(QueryMethod::from($method));
        $this->queryNormalizer->expects(static::once())->method('normalizeValues')->with($queryBuilder, $model)->willReturn($parameters = [
            'foo' => 'bar',
        ]);

        $query = $this->queryFactory->createQuery($queryBuilder);
        static::assertInstanceOf(QueryInterface::class, $query);
        static::assertSame($modelName, $query->getName());
        static::assertSame($method, $query->getMethod());
        static::assertSame([$parameters], $query->getParameters());
        static::assertSame([], $query->getOptions());
    }

    /**
     * @covers ::createQuery
     *
     * @testWith ["write"]
     */
    public function testCreateQueryOnUpdate(string $method): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        [$modelName] = ['model_name'];

        $schema = $this->createMock(SchemaInterface::class);
        $this->recordManager->expects(static::once())->method('getSchema')->willReturn($schema);
        $model = $this->createMock(ModelMetadata::class);
        $schema->expects(static::once())->method('getModel')->with($modelName)->willReturn($model);
        $model->expects(static::once())->method('getName')->willReturn($modelName);
        $queryBuilder->expects(static::once())->method('getFrom')->willReturn($modelName);
        $queryBuilder->expects(static::once())->method('getMethod')->willReturn(QueryMethod::from($method));
        $this->queryNormalizer->expects(static::once())->method('normalizeValues')->with($queryBuilder, $model)->willReturn($parameters = [
            'foo' => 'bar',
        ]);
        $queryBuilder->expects(static::once())->method('getIds')->willReturn($ids = [1, 2, 3]);

        $query = $this->queryFactory->createQuery($queryBuilder);
        static::assertInstanceOf(QueryInterface::class, $query);
        static::assertSame($modelName, $query->getName());
        static::assertSame($method, $query->getMethod());
        static::assertSame([$ids, $parameters], $query->getParameters());
        static::assertSame([], $query->getOptions());
    }

    /**
     * @covers ::createQuery
     *
     * @testWith ["write"]
     */
    public function testCreateQueryOnUpdateWithoutIds(string $method): void
    {
        $this->expectException(QueryException::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        [$modelName] = ['model_name'];

        $schema = $this->createMock(SchemaInterface::class);
        $this->recordManager->expects(static::once())->method('getSchema')->willReturn($schema);
        $model = $this->createMock(ModelMetadata::class);
        $schema->expects(static::once())->method('getModel')->with($modelName)->willReturn($model);
        $model->expects(static::once())->method('getName')->willReturn($modelName);
        $queryBuilder->expects(static::once())->method('getFrom')->willReturn($modelName);
        $queryBuilder->expects(static::once())->method('getMethod')->willReturn(QueryMethod::from($method));
        $this->queryNormalizer->expects(static::once())->method('normalizeValues')->with($queryBuilder, $model)->willReturn([
            'foo' => 'bar',
        ]);
        $queryBuilder->expects(static::once())->method('getIds')->willReturn([]);

        $this->queryFactory->createQuery($queryBuilder);
    }

    /**
     * @covers ::createQuery
     *
     * @testWith ["create"]
     *           ["write"]
     */
    public function testCreateQueryOnWriteWithoutValues(string $method): void
    {
        $this->expectException(QueryException::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        [$modelName] = ['model_name'];

        $schema = $this->createMock(SchemaInterface::class);
        $this->recordManager->expects(static::once())->method('getSchema')->willReturn($schema);
        $model = $this->createMock(ModelMetadata::class);
        $schema->expects(static::once())->method('getModel')->with($modelName)->willReturn($model);
        $model->expects(static::once())->method('getName')->willReturn($modelName);
        $queryBuilder->expects(static::once())->method('getFrom')->willReturn($modelName);
        $queryBuilder->expects(static::once())->method('getMethod')->willReturn(QueryMethod::from($method));
        $this->queryNormalizer->expects(static::once())->method('normalizeValues')->with($queryBuilder, $model)->willReturn([]);

        $this->queryFactory->createQuery($queryBuilder);
    }
}
