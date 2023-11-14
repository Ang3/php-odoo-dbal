<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

class Type implements TypeInterface
{
    private const DEFAULT_TYPE = 'default';

    public function getName(): string
    {
        return self::DEFAULT_TYPE;
    }

    public function convertToDatabaseValue(mixed $value, array $context = []): mixed
    {
        return $value;
    }

    public function convertToPhpValue(mixed $value, array $context = []): mixed
    {
        return $value;
    }
}
