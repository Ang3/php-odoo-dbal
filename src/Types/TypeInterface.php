<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

interface TypeInterface
{
    /**
     * Gets the name of the type.
     * Used to register the type into the registry.
     */
    public function getName(): string;

    /**
     * @throws ConversionException on conversion error
     */
    public function convertToDatabaseValue(mixed $value, array $context = []): mixed;

    /**
     * @throws ConversionException on conversion error
     */
    public function convertToPhpValue(mixed $value, array $context = []): mixed;
}
