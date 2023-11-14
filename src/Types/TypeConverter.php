<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

class TypeConverter implements TypeConverterInterface
{
    private TypeRegistryInterface $typeRegistry;

    public function __construct(TypeRegistryInterface $typeRegistry = null)
    {
        $this->typeRegistry = $typeRegistry ?: new TypeRegistry();
    }

    public function convertToDatabaseValue(mixed $value, string $type, array $context = []): mixed
    {
        return $this->typeRegistry->get($type)->convertToDatabaseValue($value, $context);
    }

    public function convertToPhpValue(mixed $value, string $type, array $context = []): mixed
    {
        return $this->typeRegistry->get($type)->convertToPhpValue($value, $context);
    }
}
