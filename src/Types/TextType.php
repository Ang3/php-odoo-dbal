<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

class TextType extends ScalarType
{
    public function getName(): string
    {
        return Types::TEXT;
    }

    public function convertToDatabaseValue(mixed $value, array $context = []): ?string
    {
        return $this->toString($value, $context);
    }

    public function convertToPhpValue(mixed $value, array $context = []): ?string
    {
        return $this->toString($value, $context);
    }

    private function toString(mixed $value, array $context = []): ?string
    {
        $value = parent::convertToDatabaseValue($value, $context);

        if (null === $value) {
            return null;
        }

        return $this->normalizeString((string) $value);
    }
}
