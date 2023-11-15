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
use Ang3\Component\Odoo\DBAL\Types\ConversionException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Types\BinaryType
 *
 * @internal
 */
final class BinaryTypeTest extends TestCase
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
        self::assertSame($expectedResult, $this->type->convertToDatabaseValue($value));
    }

    /**
     * @covers ::convertToDatabaseValue
     *
     * @dataProvider invalidValueProvider
     */
    public function testConvertToDatabaseValueWithInvalidValue(mixed $value): void
    {
        $this->expectException(ConversionException::class);
        $this->type->convertToDatabaseValue($value);
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
        self::assertSame($expectedResult, $this->type->convertToPhpValue($value));
    }

    /**
     * @covers ::convertToPhpValue
     *
     * @dataProvider invalidValueProvider
     */
    public function testConvertToPhpValueWithInvalidValue(mixed $value): void
    {
        $this->expectException(ConversionException::class);
        $this->type->convertToPhpValue($value);
    }

    /**
     * @internal
     */
    public static function invalidValueProvider(): iterable
    {
        return [
            [['foo' => 'bar']],
            [new \stdClass()],
        ];
    }
}
