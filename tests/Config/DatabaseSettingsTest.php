<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Config;

use Ang3\Component\Odoo\DBAL\Config\DatabaseSettings;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Config\DatabaseSettings
 *
 * @internal
 */
final class DatabaseSettingsTest extends TestCase
{
    private DatabaseSettings $databaseSettings;
    private string $timezone = 'Europe/Paris';

    protected function setUp(): void
    {
        parent::setUp();
        $this->databaseSettings = new DatabaseSettings($this->timezone);
    }

    /**
     * @covers ::__construct
     * @covers ::getTimezone
     */
    public function testEmptyConstructor(): void
    {
        $databaseSettings = new DatabaseSettings();
        static::assertSame(DatabaseSettings::DEFAULT_TIMEZONE, $databaseSettings->getTimezone());
    }

    /**
     * @covers ::getTimezone
     */
    public function testTimezone(): void
    {
        static::assertSame($this->timezone, $this->databaseSettings->getTimezone());
    }
}
