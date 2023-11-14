<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

class TypeRegistry implements TypeRegistryInterface
{
    /**
     * @var TypeInterface[]
     */
    private array $types = [];

    public function __construct(array $types = [])
    {
        $this->registerBuiltInTypes();

        foreach ($types as $name => $type) {
            $this->register($name, $type);
        }
    }

    /**
     * Loads default built-in Odoo types.
     */
    public function registerBuiltInTypes(): void
    {
        $this->register(Types::BINARY, new BinaryType());
        $this->register(Types::BOOLEAN, new BooleanType());
        $this->register(Types::CHAR, new TextType());
        $this->register(Types::DATE, new DateType());
        $this->register(Types::DATETIME, new DateTimeType());
        $this->register(Types::FLOAT, new FloatType());
        $this->register(Types::INTEGER, new IntegerType());
        $this->register(Types::MONETARY, new FloatType());
        $this->register(Types::SELECTION, new ScalarType());
        $this->register(Types::TEXT, new TextType());
    }

    public function register(string $name, TypeInterface $type): self
    {
        $this->types[$name] = $type;

        return $this;
    }

    public function get(string $name): TypeInterface
    {
        $type = $this->types[$name] ?? null;

        if (!$type) {
            throw TypeException::notRegistered($name);
        }

        return $type;
    }

    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->types);
    }

    public function getMap(): array
    {
        return $this->types;
    }
}
