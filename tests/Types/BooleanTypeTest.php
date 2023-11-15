<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Types;

use Ang3\Component\Odoo\DBAL\Types\BooleanType;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Types\BooleanType
 *
 * @internal
 */
final class BooleanTypeTest extends AbstractScalarTypeTest
{
    private BooleanType $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->type = new BooleanType();
    }

    /**
     * @covers ::convertToDatabaseValue
     *
     * @testWith [ true, true ]
     *           [ false, false ]
     *           [ 0, false ]
     *           [ 1, true ]
     *           [ 1.1, true ]
     *           [ "1", true ]
     *           [ "Hello world!", true ]
     *           [ "", false ]
     *           [ " ", false ]
     */
    public function testConvertToDatabaseValue(mixed $value, ?bool $expectedResult): void
    {
        self::assertSame($expectedResult, $this->type->convertToDatabaseValue($value));
    }

    /**
     * @covers ::convertToPhpValue
     *
     * @testWith [ true, true ]
     *           [ false, false ]
     *           [ 0, false ]
     *           [ 1, true ]
     *           [ 1.1, true ]
     *           [ "1", true ]
     *           [ "Hello world!", true ]
     *           [ "", false ]
     *           [ " ", false ]
     */
    public function testConvertToPhpValue(mixed $value, ?bool $expectedResult): void
    {
        self::assertSame($expectedResult, $this->type->convertToPhpValue($value));
    }
}
