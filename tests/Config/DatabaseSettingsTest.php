<?php

namespace Ang3\Component\Odoo\DBAL\Tests\Config;

use Ang3\Component\Odoo\DBAL\Config\DatabaseSettings;
use Ang3\Component\Odoo\DBAL\Configuration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Config\DatabaseSettings
 *
 * @internal
 */
class DatabaseSettingsTest extends TestCase
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
		self::assertSame(DatabaseSettings::DEFAULT_TIMEZONE, $databaseSettings->getTimezone());
	}

	/**
	 * @covers ::getTimezone
	 */
	public function testTimezone(): void
	{
		self::assertSame($this->timezone, $this->databaseSettings->getTimezone());
	}
}