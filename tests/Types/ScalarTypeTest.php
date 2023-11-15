<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Types;

use Ang3\Component\Odoo\DBAL\Types\ScalarType;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Types\ScalarType
 *
 * @internal
 */
final class ScalarTypeTest extends AbstractScalarTypeTest
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
     * @testWith [ true, true ]
     *           [ false, false ]
     *           [ 0, 0 ]
     *           [ 1, 1 ]
     *           [ 1.1, 1.1 ]
     *           [ "1", "1" ]
     *           [ "Hello world!", "Hello world!" ]
     *           [ "", null ]
     *           [ " ", null ]
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
     *           [ "", null ]
     *           [ " ", null ]
     */
    public function testConvertToPhpValue(mixed $value, mixed $expectedResult): void
    {
        self::assertSame($expectedResult, $this->type->convertToPhpValue($value));
    }
}
