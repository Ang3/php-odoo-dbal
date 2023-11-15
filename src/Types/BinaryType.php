<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

class BinaryType extends ScalarType
{
    public function getName(): string
    {
        return Types::BINARY;
    }

    public function convertToDatabaseValue(mixed $value, array $context = []): ?string
    {
        $value = parent::convertToDatabaseValue($value, $context);

        if (null === $value) {
            return null;
        }

        return base64_encode((string) $value);
    }

    public function convertToPhpValue(mixed $value, array $context = []): ?string
    {
        $value = parent::convertToPhpValue($value, $context);

        if (null === $value) {
            return null;
        }

        $result = base64_decode((string) $value, true);

        return false !== $result ? $result : null;
    }
}
