<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Schema\Enum;

enum IrModel: string
{
    case Model = 'ir.model';
    case Fields = 'ir.model.fields';
    case FieldsSelection = 'ir.model.fields.selection';
}
