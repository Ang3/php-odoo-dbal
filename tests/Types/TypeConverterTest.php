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
use Ang3\Component\Odoo\DBAL\Types\TypeConverter;
use Ang3\Component\Odoo\DBAL\Types\TypeConverterInterface;
use Ang3\Component\Odoo\DBAL\Types\TypeException;
use Ang3\Component\Odoo\DBAL\Types\TypeInterface;
use Ang3\Component\Odoo\DBAL\Types\TypeRegistryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Types\TypeConverter
 *
 * @internal
 */
final class TypeConverterTest extends TestCase
{
    private TypeConverter $typeConverter;
    private MockObject $typeRegistry;
    private array $defaultContext = [
        DateType::TIMEZONE_KEY => DatabaseSettings::DEFAULT_TIMEZONE,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->typeRegistry = $this->createMock(TypeRegistryInterface::class);
        $this->typeConverter = new TypeConverter($this->typeRegistry, $this->defaultContext);
    }

    /**
     * @covers \Ang3\Component\Odoo\DBAL\Types\TypeConverter
     */
    public function testInterface(): void
    {
        self::assertInstanceOf(TypeConverterInterface::class, $this->typeConverter);
    }

    /**
     * @covers ::getTypeRegistry
     */
    public function testGetTypeRegistry(): void
    {
        self::assertSame($this->typeRegistry, $this->typeConverter->getTypeRegistry());
    }

    /**
     * @covers ::__construct
     *
     * @depends testGetTypeRegistry
     */
    public function testEmptyConstructor(): void
    {
        $typeConverter = new TypeConverter();
        self::assertInstanceOf(TypeRegistryInterface::class, $typeConverter->getTypeRegistry());
    }

    /**
     * @covers ::getContext
     *
     * @testWith [ {"bar": "baz"} ]
     */
    public function testGetContext(array $context): void
    {
        self::assertSame(array_merge($this->defaultContext, $context), $this->typeConverter->getContext($context));
    }

    /**
     * @covers ::convertToDatabaseValue
     *
     * @depends testGetContext
     *
     * @testWith [ "foo", "type_name", [] ]
     */
    public function testConvertToDatabaseValue(mixed $value, string $type, array $context = []): void
    {
        $registeredType = $this->createMock(TypeInterface::class);
        $this->typeRegistry->expects(self::once())->method('get')->with($type)->willReturn($registeredType);
        $registeredType->expects(self::once())->method('convertToDatabaseValue')->with($value, $this->typeConverter->getContext($context))->willReturn($result = 'bar');

        self::assertSame($result, $this->typeConverter->convertToDatabaseValue($value, $type, $context));
    }

    /**
     * @covers ::convertToDatabaseValue
     *
     * @testWith [ "foo", "unknown_type" ]
     */
    public function testConvertToDatabaseValueWithUnknownType(mixed $value, string $unknownType): void
    {
        $this->expectException(TypeException::class);
        $this->typeRegistry->expects(self::once())->method('get')->with($unknownType)->willThrowException(new TypeException());
        $this->typeConverter->convertToDatabaseValue($value, $unknownType);
    }

    /**
     * @covers ::convertToPhpValue
     *
     * @depends testGetContext
     *
     * @testWith [ "foo", "type_name", [] ]
     */
    public function testConvertToPhpValue(mixed $value, string $type, array $context = []): void
    {
        $registeredType = $this->createMock(TypeInterface::class);
        $this->typeRegistry->expects(self::once())->method('get')->with($type)->willReturn($registeredType);
        $registeredType->expects(self::once())->method('convertToPhpValue')->with($value, $this->typeConverter->getContext($context))->willReturn($result = 'bar');

        self::assertSame($result, $this->typeConverter->convertToPhpValue($value, $type, $context));
    }

    /**
     * @covers ::convertToPhpValue
     *
     * @testWith [ "foo", "unknown_type" ]
     */
    public function testConvertToPhpValueWithUnknownType(mixed $value, string $unknownType): void
    {
        $this->expectException(TypeException::class);
        $this->typeRegistry->expects(self::once())->method('get')->with($unknownType)->willThrowException(new TypeException());
        $this->typeConverter->convertToPhpValue($value, $unknownType);
    }
}
