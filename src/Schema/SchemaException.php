<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Schema;

use Ang3\Component\Odoo\DBAL\Exception\BaseException;

class SchemaException extends BaseException
{
    public static function targetModelNotFound(string $modelName, string $fieldName): self
    {
        return new self(sprintf('Unable to retrieve the target model of field "%s" for the model "%s".', $fieldName, $modelName));
    }

    public static function fieldNotFound(string $modelName, string $fieldName): self
    {
        return new self(sprintf('The field "%s" was not found into the model "%s".', $fieldName, $modelName));
    }

    public static function modelNotFound(string $modelName): self
    {
        return new self(sprintf('The model "%s" was not found on the database.', $modelName));
    }
}
