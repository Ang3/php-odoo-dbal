<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Types;

use Ang3\Component\Odoo\DBAL\Config\DatabaseSettings;
use Ang3\Component\Odoo\DBAL\Types\DateType;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Types\DateType
 *
 * @internal
 */
final class DateTypeTest extends AbstractTypeTest
{
    private DateType $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->type = new DateType();
    }

    /**
     * @covers ::convertToDatabaseValue
     *
     * @testWith [ null, null, null, null ]
     *           [ "2000-01-01 01:02:03", "Africa/Algiers", "2000-01-01 00:02:03", "UTC" ]
     */
    public function testConvertToDatabaseValue(?string $value, ?string $timezoneSource, ?string $dateTarget, ?string $timezoneTarget): void
    {
        $timezoneTarget = $timezoneTarget ?: DatabaseSettings::DEFAULT_TIMEZONE;

        if (\is_string($value)) {
            $value = new \DateTime($value, new \DateTimeZone($timezoneSource ?: DatabaseSettings::DEFAULT_TIMEZONE));
            $expected = new \DateTime($dateTarget, new \DateTimeZone($timezoneTarget));
        }

        $result = $this->type->convertToDatabaseValue($value, [
            DateType::TIMEZONE_KEY => $timezoneTarget,
        ]);

        if (isset($expected)) {
            static::assertSame($expected->format('Y-m-d'), $result);
        } else {
            static::assertNull($result);
        }
    }

    /**
     * @covers ::convertToPhpValue
     *
     * @testWith [ "2000-01-01", "UTC", "2000-01-01 00:00:00" ]
     */
    public function testConvertToPhpValue(string $value, string $timezoneSource, string $dateTarget): void
    {
        $result = $this->type->convertToPhpValue($value, [
            DateType::TIMEZONE_KEY => $timezoneSource,
        ]);

        static::assertInstanceOf(\DateTime::class, $result);
        static::assertSame($dateTarget, $result->format('Y-m-d H:i:s'));
    }
}
