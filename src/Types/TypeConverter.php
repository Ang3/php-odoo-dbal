<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

use Ang3\Component\Odoo\DBAL\Config\DatabaseSettings;

class TypeConverter implements TypeConverterInterface
{
    private TypeRegistryInterface $typeRegistry;

    private array $defaultContext = [
        DateType::TIMEZONE_KEY => DatabaseSettings::DEFAULT_TIMEZONE,
    ];

    public function __construct(TypeRegistryInterface $typeRegistry = null, array $defaultContext = [])
    {
        $this->typeRegistry = $typeRegistry ?: new TypeRegistry();
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    public function convertToDatabaseValue(mixed $value, string $type, array $context = []): mixed
    {
        return $this->typeRegistry->get($type)->convertToDatabaseValue($value, $this->getContext($context));
    }

    public function convertToPhpValue(mixed $value, string $type, array $context = []): mixed
    {
        return $this->typeRegistry->get($type)->convertToPhpValue($value, $this->getContext($context));
    }

    public function getContext(array $context): array
    {
        return array_merge($this->defaultContext, $context);
    }

    public function getTypeRegistry(): TypeRegistryInterface
    {
        return $this->typeRegistry;
    }

    public function getDefaultContext(): array
    {
        return $this->defaultContext;
    }
}
