<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Types;

use Ang3\Component\Odoo\DBAL\Types\TextType;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Types\TextType
 *
 * @internal
 */
final class TextTypeTest extends AbstractScalarTypeTest
{
    private TextType $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->type = new TextType();
    }

    /**
     * @covers ::convertToDatabaseValue
     *
     * @testWith [ true, "1" ]
     *           [ false, null ]
     *           [ 0, "0" ]
     *           [ 1, "1" ]
     *           [ 1.1, "1.1" ]
     *           [ "1", "1" ]
     *           [ "Hello world!", "Hello world!" ]
     *           [ "", null ]
     *           [ " ", null ]
     */
    public function testConvertToDatabaseValue(mixed $value, mixed $expectedResult): void
    {
        static::assertSame($expectedResult, $this->type->convertToDatabaseValue($value));
    }

    /**
     * @covers ::convertToPhpValue
     *
     * @testWith [ true, "1" ]
     *           [ false, null ]
     *           [ 0, "0" ]
     *           [ 1, "1" ]
     *           [ 1.1, "1.1" ]
     *           [ "1", "1" ]
     *           [ "Hello world!", "Hello world!" ]
     *           [ "", null ]
     *           [ " ", null ]
     */
    public function testConvertToPhpValue(mixed $value, mixed $expectedResult): void
    {
        static::assertSame($expectedResult, $this->type->convertToPhpValue($value));
    }
}
