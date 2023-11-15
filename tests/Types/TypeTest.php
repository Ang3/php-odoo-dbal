<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Types;

use Ang3\Component\Odoo\DBAL\Types\Type;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Types\Type
 *
 * @internal
 */
final class TypeTest extends AbstractTypeTest
{
    private Type $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->type = new Type();
    }

    /**
     * @covers ::convertToDatabaseValue
     *
     * @testWith [ true, true ]
     *           [ false, false ]
     *           [ 0, 0 ]
     *           [ 1, 1 ]
     *           [ 1.1, 1.1 ]
     *           [ "1", "1" ]
     *           [ "Hello world!", "Hello world!" ]
     *           [ "", "" ]
     *           [ " ", " " ]
     *           [ [" "], [" "] ]
     */
    public function testConvertToDatabaseValue(mixed $value, mixed $expectedResult): void
    {
        self::assertSame($expectedResult, $this->type->convertToDatabaseValue($value));
    }

    /**
     * @covers ::convertToPhpValue
     *
     * @testWith [ true, true ]
     *           [ false, false ]
     *           [ 0, 0 ]
     *           [ 1, 1 ]
     *           [ 1.1, 1.1 ]
     *           [ "1", "1" ]
     *           [ "Hello world!", "Hello world!" ]
     *           [ "", "" ]
     *           [ " ", " " ]
     *           [ [" "], [" "] ]
     */
    public function testConvertToPhpValue(mixed $value, mixed $expectedResult): void
    {
        self::assertSame($expectedResult, $this->type->convertToPhpValue($value));
    }
}
