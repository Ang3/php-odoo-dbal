<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Types;

use Ang3\Component\Odoo\DBAL\Types\FloatType;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Types\FloatType
 *
 * @internal
 */
final class FloatTypeTest extends AbstractScalarTypeTest
{
    private FloatType $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->type = new FloatType();
    }

    /**
     * @covers ::convertToDatabaseValue
     *
     * @testWith [ true, 1.0 ]
     *           [ false, 0.0 ]
     *           [ 0, 0.0 ]
     *           [ 1, 1.0 ]
     *           [ 1.1, 1.1 ]
     *           [ "1", 1.0 ]
     *           [ "Hello world!", 0.0 ]
     *           [ "", 0.0 ]
     *           [ " ", 0.0 ]
     */
    public function testConvertToDatabaseValue(mixed $value, mixed $expectedResult): void
    {
        static::assertSame($expectedResult, $this->type->convertToDatabaseValue($value));
    }

    /**
     * @covers ::convertToPhpValue
     *
     * @testWith [ true, 1.0 ]
     *           [ false, 0.0 ]
     *           [ 0, 0.0 ]
     *           [ 1, 1.0 ]
     *           [ 1.1, 1.1 ]
     *           [ "1", 1.0 ]
     *           [ "Hello world!", 0.0 ]
     *           [ "", 0.0 ]
     *           [ " ", 0.0 ]
     */
    public function testConvertToPhpValue(mixed $value, mixed $expectedResult): void
    {
        static::assertSame($expectedResult, $this->type->convertToPhpValue($value));
    }
}
