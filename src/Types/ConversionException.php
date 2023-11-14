<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
class ConversionException extends TypeException
{
    /**
     * Thrown when a PHP to Database Type Conversion fails.
     */
    public static function conversionToPhpFailed(mixed $value, string $toType, ?\Throwable $previous = null): self
    {
        $value = self::getValueAsString($value);

        return new self('Could not convert PHP value "'.$value.'" to Database Type '.$toType, 0, $previous);
    }

    /**
     * Thrown when a Database to PHP Type Conversion fails and we can make a statement about the expected format.
     */
    public static function unexpectedDatabaseFormat(mixed $value, string $toType, string $expectedFormat, ?\Throwable $previous = null): self
    {
        $value = self::getValueAsString($value);

        return new self(
            'Could not convert database value "'.$value.'" to PHP Type '.
            $toType.'. Expected format: '.$expectedFormat,
            0,
            $previous,
        );
    }

    /**
     * Thrown when the PHP value passed to the converter was not of the expected type.
     *
     * @param string[] $possibleTypes
     */
    public static function unexpectedType(mixed $value, string $toType, array $possibleTypes, ?\Throwable $previous = null): self
    {
        if (\is_scalar($value) || null === $value) {
            return new self(sprintf(
                'Could not convert PHP value %s to type %s. Expected one of the following types: %s',
                var_export($value, true),
                $toType,
                implode(', ', $possibleTypes),
            ), 0, $previous);
        }

        return new self(sprintf(
            'Could not convert PHP value of type %s to type %s. Expected one of the following types: %s',
            \is_object($value) ? $value::class : \gettype($value),
            $toType,
            implode(', ', $possibleTypes),
        ), 0, $previous);
    }

    /**
     * @internal
     */
    private static function getValueAsString(mixed $value): string
    {
        if (\is_scalar($value)) {
            return \strlen((string) $value) > 32 ? substr((string) $value, 0, 20).'...' : (string) $value;
        }

        return get_debug_type($value);
    }
}
