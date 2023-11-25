<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Schema;

use Ang3\Component\Odoo\Client;
use Ang3\Component\Odoo\DBAL\RecordManager;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\MetadataFactory;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\MetadataFactoryInterface;
use Ang3\Component\Odoo\DBAL\Schema\Schema;
use Ang3\Component\Odoo\DBAL\Schema\SchemaInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Schema\Schema
 *
 * @internal
 */
final class SchemaTest extends TestCase
{
    private Schema $schema;
    private MockObject $recordManager;
    private MockObject $metadataFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recordManager = $this->createMock(RecordManager::class);
        $this->metadataFactory = $this->createMock(MetadataFactoryInterface::class);
        $this->schema = new Schema($this->recordManager, $this->metadataFactory);
    }

    /**
     * @covers \Ang3\Component\Odoo\DBAL\Schema\Schema
     */
    public function testInterface(): void
    {
        static::assertInstanceOf(SchemaInterface::class, $this->schema);
    }

    /**
     * @covers ::getRecordManager
     */
    public function testGetRecordManager(): void
    {
        static::assertSame($this->recordManager, $this->schema->getRecordManager());
    }

    /**
     * @covers ::getMetadataFactory
     */
    public function testGetMetadataFactory(): void
    {
        static::assertSame($this->metadataFactory, $this->schema->getMetadataFactory());
    }

    /**
     * @covers ::__construct
     *
     * @depends testGetMetadataFactory
     */
    public function testConstructorWithoutMetadataFactory(): void
    {
        $client = $this->createMock(Client::class);
        $this->recordManager->expects(static::once())->method('getClient')->willReturn($client);
        $this->schema = new Schema($this->recordManager);

        $metadataFactory = $this->schema->getMetadataFactory();
        static::assertNotSame($this->metadataFactory, $metadataFactory);
        static::assertInstanceOf(MetadataFactory::class, $metadataFactory);
        static::assertSame($client, $metadataFactory->getClient());
    }
}
