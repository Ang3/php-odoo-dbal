<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests;

use Ang3\Component\Odoo\DBAL\Config\DatabaseSettings;
use Ang3\Component\Odoo\DBAL\Configuration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Configuration
 *
 * @internal
 */
final class ConfigurationTest extends TestCase
{
    private Configuration $configuration;
    private MockObject $databaseSettings;
    private MockObject $metadataCache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->databaseSettings = $this->createMock(DatabaseSettings::class);
        $this->metadataCache = $this->createMock(CacheItemPoolInterface::class);
        $this->configuration = new Configuration($this->databaseSettings, $this->metadataCache);
    }

    /**
     * @covers ::__construct
     */
    public function testEmptyConstructor(): void
    {
        $configuration = new Configuration();
        static::assertInstanceOf(DatabaseSettings::class, $configuration->getDatabaseSettings());
        static::assertInstanceOf(CacheItemPoolInterface::class, $configuration->getMetadataCache());
    }

    /**
     * @covers ::getDatabaseSettings
     */
    public function testGetDatabaseSettings(): void
    {
        static::assertSame($this->databaseSettings, $this->configuration->getDatabaseSettings());
    }

    /**
     * @covers ::getMetadataCache
     */
    public function testGetMetadataCache(): void
    {
        static::assertSame($this->metadataCache, $this->configuration->getMetadataCache());
    }
}
