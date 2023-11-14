<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

class BinaryType extends Type
{
    public function getName(): string
    {
        return Types::BINARY;
    }

    public function convertToDatabaseValue(mixed $value, array $context = []): ?string
    {
        if (!$value) {
            return null;
        }

        if (!\is_scalar($value)) {
            throw ConversionException::unexpectedType($value, $this->getName(), ['bool', 'int', 'float', 'string']);
        }

        return base64_encode((string) $value);
    }

    public function convertToPhpValue(mixed $value, array $context = []): ?string
    {
        if (!$value) {
            return null;
        }

        if (!\is_scalar($value)) {
            throw ConversionException::unexpectedDatabaseFormat($value, self::class, 'scalar');
        }

        return base64_decode((string) $value, true) ?: null;
    }
}
