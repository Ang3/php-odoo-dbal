<?php

namespace Ang3\Component\Odoo\DBAL\Tests\Types;

use Ang3\Component\Odoo\DBAL\Types\TypeException;
use Ang3\Component\Odoo\DBAL\Types\TypeRegistry;
use Ang3\Component\Odoo\DBAL\Types\TypeRegistryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Types\TypeRegistry
 */
class TypeRegistryTest extends TestCase
{
    private TypeRegistry $typeRegistry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->typeRegistry = new TypeRegistry();
    }

    /**
     * @covers \Ang3\Component\Odoo\DBAL\Types\TypeRegistry
     */
    public function testInterface(): void
    {
        self::assertInstanceOf(TypeRegistryInterface::class, $this->typeRegistry);
    }

    /**
     * @covers ::__construct
     * @covers ::registerBuiltInTypes
     * @covers ::get
     * @testWith [ "binary", "Ang3\\Component\\Odoo\\DBAL\\Types\\BinaryType" ]
     *           [ "boolean", "Ang3\\Component\\Odoo\\DBAL\\Types\\BooleanType" ]
     *           [ "char", "Ang3\\Component\\Odoo\\DBAL\\Types\\TextType" ]
     *           [ "date", "Ang3\\Component\\Odoo\\DBAL\\Types\\DateType" ]
     *           [ "datetime", "Ang3\\Component\\Odoo\\DBAL\\Types\\DateTimeType" ]
     *           [ "float", "Ang3\\Component\\Odoo\\DBAL\\Types\\FloatType" ]
     *           [ "html", "Ang3\\Component\\Odoo\\DBAL\\Types\\TextType" ]
     *           [ "integer", "Ang3\\Component\\Odoo\\DBAL\\Types\\IntegerType" ]
     *           [ "monetary", "Ang3\\Component\\Odoo\\DBAL\\Types\\FloatType" ]
     *           [ "selection", "Ang3\\Component\\Odoo\\DBAL\\Types\\ScalarType" ]
     *           [ "text", "Ang3\\Component\\Odoo\\DBAL\\Types\\TextType" ]
     */
    public function testGetBuiltInTypes(string $name, string $expectedClassType): void
    {
        self::assertTrue(class_exists($expectedClassType), sprintf('The class type "%s" was not found.', $expectedClassType));
        self::assertInstanceOf($expectedClassType, $this->typeRegistry->get($name));
    }

    /**
     * @covers ::get
     * @testWith [ "unknown_type" ]
     */
    public function testGetUnknownType(string $name): void
    {
        self::expectException(TypeException::class);
        $this->typeRegistry->get($name);
    }

    /**
     * @covers ::register
     * @testWith [ "new_type", "Ang3\\Component\\Odoo\\DBAL\\Tests\\Types\\TestType" ]
     */
    public function testRegister(string $name, string $classType): void
    {
        $this->typeRegistry->register($name, new $classType());
        self::assertInstanceOf($classType, $this->typeRegistry->get($name));
    }

    /**
     * @covers ::has
     * @testWith [ "binary", true ]
     *           [ "boolean", true ]
     *           [ "char", true ]
     *           [ "date", true ]
     *           [ "datetime", true ]
     *           [ "float", true ]
     *           [ "html", true ]
     *           [ "integer", true ]
     *           [ "monetary", true ]
     *           [ "selection", true ]
     *           [ "text", true ]
     *           [ "new_type", false ]
     *           [ "unknown_type", false ]
     */
    public function testHas(string $name, bool $expectedResult): void
    {
        self::assertSame($expectedResult, $this->typeRegistry->has($name));
    }
}