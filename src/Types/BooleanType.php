<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

class BooleanType extends Type
{
    public function getName(): string
    {
        return Types::BOOLEAN;
    }

    public function convertToDatabaseValue(mixed $value, array $context = []): bool
    {
        return (bool) $value;
    }

    public function convertToPhpValue(mixed $value, array $context = []): bool
    {
        if (\is_string($value)) {
            return match (strtolower($value)) {
                'true', '1' => true,
                default => false
            };
        }

        return (bool) $value;
    }
}
