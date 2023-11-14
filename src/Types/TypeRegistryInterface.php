<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

interface TypeRegistryInterface
{
    public function register(string $name, TypeInterface $type): self;

    /**
     * @throws TypeException when the type is not registered
     */
    public function get(string $name): TypeInterface;

    public function has(string $name): bool;
}
