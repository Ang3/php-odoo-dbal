<?php

declare(strict_types=1);

namespace Ang3\Component\Odoo\DBAL\Schema\Enum;

enum FieldType: string
{
    // Basics
    case Binary = 'binary';
    case Boolean = 'boolean';
    case Char = 'char';
    case Date = 'date';
    case DateTime = 'datetime';
    case Float = 'float';
    case Html = 'html';
    case Integer = 'integer';
    case Monetary = 'monetary';
    case Selection = 'selection';
    case Text = 'text';

    // Relationships
    case ManyToOne = 'many2one';
    case ManyToMany = 'many2many';
    case OneToMany = 'one2many';
}
