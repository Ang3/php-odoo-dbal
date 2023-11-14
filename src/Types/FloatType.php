<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

class FloatType extends Type
{
    public function getName(): string
    {
        return Types::FLOAT;
    }

    public function convertToDatabaseValue(mixed $value, array $context = []): null|float|int
    {
        if (null === $value) {
            return null;
        }

        if (!\is_scalar($value)) {
            throw ConversionException::unexpectedType($value, $this->getName(), ['bool', 'int', 'float', 'string']);
        }

        return (float) $value;
    }

    public function convertToPhpValue(mixed $value, array $context = []): ?float
    {
        if (null === $value) {
            return null;
        }

        if (!\is_scalar($value)) {
            throw ConversionException::unexpectedDatabaseFormat($value, self::class, 'scalar');
        }

        return \is_float($value) ? $value : (float) $value;
    }
}
