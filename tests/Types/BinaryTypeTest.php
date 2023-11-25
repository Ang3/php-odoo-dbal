<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Types;

use Ang3\Component\Odoo\DBAL\Types\BinaryType;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Types\BinaryType
 *
 * @internal
 */
final class BinaryTypeTest extends AbstractScalarTypeTest
{
    private BinaryType $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->type = new BinaryType();
    }

    /**
     * @covers ::convertToDatabaseValue
     *
     * @testWith [ "Hello world!", "SGVsbG8gd29ybGQh" ]
     *           [ 0, "MA==" ]
     *           [ 1, "MQ==" ]
     *           [ "0", "MA==" ]
     *           [ "1", "MQ==" ]
     *           [ 1.1, "MS4x" ]
     *           [ "1.1", "MS4x" ]
     *           [ "", null ]
     *           [ " ", null ]
     */
    public function testConvertToDatabaseValue(mixed $value, ?string $expectedResult): void
    {
        static::assertSame($expectedResult, $this->type->convertToDatabaseValue($value));
    }

    /**
     * @covers ::convertToPhpValue
     *
     * @testWith [ "SGVsbG8gd29ybGQh", "Hello world!"  ]
     *           [ "MA==", "0" ]
     *           [ "MQ==", "1" ]
     *           [ "MS4x", "1.1" ]
     *           [ "", null ]
     *           [ " ", null ]
     *           [ null, null ]
     */
    public function testConvertToPhpValue(mixed $value, mixed $expectedResult): void
    {
        static::assertSame($expectedResult, $this->type->convertToPhpValue($value));
    }
}
