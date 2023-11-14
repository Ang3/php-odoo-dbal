<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

use Ang3\Component\Odoo\DBAL\Exception\BaseException;

class TypeException extends BaseException
{
    public static function notRegistered(string $type): self
    {
        return new self(sprintf('The type "'.$type.'" is not registered. Any type that you use has '.
            'to be registered into the type registry. You can add custom types or get a list of all the '.
            'known types with \Ang3\Component\Odoo\DBAL\Types\TypeRegistry::getMap().', $type));
    }
}
