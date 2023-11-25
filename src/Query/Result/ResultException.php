<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Result;

use Ang3\Component\Odoo\DBAL\Exception\ExceptionInterface;

class ResultException extends \UnexpectedValueException implements ExceptionInterface
{
    public static function columnNotFound(int|string $column, int|string $offset, \Throwable $previous = null): self
    {
        return new self(sprintf('The column "%s" (%s) was not found in the row at offset "%s".', $column, \gettype($column), $offset), 0, $previous);
    }

    public static function invalidOffsetType(mixed $value, \Throwable $previous = null): self
    {
        return self::unexpectedValue($value, ['int', 'string'], 'Invalid offset type', $previous);
    }

    public static function invalidValue(mixed $value, array|string $expectedTypes, \Throwable $previous = null): self
    {
        return self::unexpectedValue($value, $expectedTypes, 'Invalid value type', $previous);
    }

    public static function unexpectedValue(
        mixed $value,
        array|string $expectedTypes,
        string $message = null,
        \Throwable $previous = null
    ): self {
        $expectedTypes = \is_array($expectedTypes) ? $expectedTypes : [$expectedTypes];
        $errorMessage = sprintf('Expected value of type "%s", got "%s"', implode('|', $expectedTypes), get_debug_type($value));
        $message = $message ? sprintf('%s - %s', $message, $errorMessage) : $errorMessage;

        return new self($message, 0, $previous);
    }
}
