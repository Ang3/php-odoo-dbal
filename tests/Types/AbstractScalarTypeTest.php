<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Types;

use Ang3\Component\Odoo\DBAL\Types\ConversionException;
use Ang3\Component\Odoo\DBAL\Types\ScalarType;

/**
 * @coversNothing
 *
 * @internal
 */
abstract class AbstractScalarTypeTest extends AbstractTypeTest
{
    private ScalarType $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->type = new ScalarType();
    }

    /**
     * @covers ::convertToDatabaseValue
     *
     * @dataProvider nonScalarValueProvider
     */
    public function testConvertToDatabaseValueWithInvalidValue(mixed $value): void
    {
        $this->expectException(ConversionException::class);
        $this->type->convertToDatabaseValue($value);
    }

    /**
     * @covers ::convertToPhpValue
     *
     * @dataProvider nonScalarValueProvider
     */
    public function testConvertToPhpValueWithInvalidValue(mixed $value): void
    {
        $this->expectException(ConversionException::class);
        $this->type->convertToPhpValue($value);
    }
}
