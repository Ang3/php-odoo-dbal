<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

class ScalarType extends Type
{
    public function getName(): string
    {
        return 'scalar';
    }

    public function convertToDatabaseValue(mixed $value, array $context = []): null|bool|float|int|string
    {
        if (null === $value) {
            return null;
        }

        if (!\is_scalar($value)) {
            throw ConversionException::unexpectedType($value, $this->getName(), ['bool', 'int', 'float', 'string']);
        }

        if (\is_string($value)) {
            $value = $this->normalizeString($value);
        }

        return $value;
    }

    public function convertToPhpValue(mixed $value, array $context = []): null|bool|float|int|string
    {
        if (null === $value) {
            return null;
        }

        if (!\is_scalar($value)) {
            throw ConversionException::unexpectedDatabaseFormat($value, self::class, 'scalar');
        }

        if (\is_string($value)) {
            $value = $this->normalizeString($value);
        }

        return $value;
    }

    public function normalizeString(string $value): ?string
    {
        $value = trim($value);

        if ('' === $value) {
            return null;
        }

        return $value;
    }
}
