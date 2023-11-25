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
 * @coversNothing
 *
 * @internal
 */
abstract class AbstractDateTypeTest extends AbstractTypeTest
{
    protected DateType $type;

    /**
     * @covers ::convertToDatabaseValue
     *
     * @testWith [ null, null, null, null ]
     *           [ "2000-01-01", "Africa/Algiers", "1999-12-31 23:00:00", "UTC" ]
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
            static::assertSame($expected->format($this->getDatabaseFormat()), $result);
        } else {
            static::assertNull($result);
        }
    }

    abstract public static function getDatabaseFormat(): string;
}
