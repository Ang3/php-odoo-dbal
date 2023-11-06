<?php

declare(strict_types=1);

namespace Ang3\Component\Odoo\DBAL\Schema\Enum;

enum DateTimeFormat: string
{
    case Short = 'Y-m-d';
    case Long = 'Y-m-d H:i:s';
}
