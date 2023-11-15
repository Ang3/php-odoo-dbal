<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

class BooleanType extends ScalarType
{
    public function getName(): string
    {
        return Types::BOOLEAN;
    }

    public function convertToDatabaseValue(mixed $value, array $context = []): bool
    {
        $value = parent::convertToDatabaseValue($value, $context);

        return (bool) $value;
    }

    public function convertToPhpValue(mixed $value, array $context = []): ?bool
    {
        $value = parent::convertToPhpValue($value, $context);

        return (bool) $value;
    }
}
