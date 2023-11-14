<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

class DateTimeType extends DateType
{
    public function getName(): string
    {
        return Types::DATETIME;
    }

    protected function getFormat(): string
    {
        return 'Y-m-d H:i:s';
    }
}
