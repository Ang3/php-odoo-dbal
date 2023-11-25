<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Types;

use Ang3\Component\Odoo\DBAL\Types\DateTimeType;
use Ang3\Component\Odoo\DBAL\Types\DateType;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Types\DateType
 *
 * @internal
 */
final class DateTimeTypeTest extends AbstractDateTypeTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->type = new DateTimeType();
    }

    public static function getDatabaseFormat(): string
    {
        return 'Y-m-d H:i:s';
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
