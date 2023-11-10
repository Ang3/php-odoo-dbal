<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Enum;

enum QueryBuilderMethod: string
{
    case Select = 'select';
    case Search = 'search';
    case Insert = 'insert';
    case Update = 'update';
    case Delete = 'delete';

    public function getOrmQueryMethod(): OrmQueryMethod
    {
        return match ($this) {
            QueryBuilderMethod::Select => OrmQueryMethod::SearchAndRead,
            QueryBuilderMethod::Search => OrmQueryMethod::Search,
            QueryBuilderMethod::Insert => OrmQueryMethod::Create,
            QueryBuilderMethod::Update => OrmQueryMethod::Write,
            QueryBuilderMethod::Delete => OrmQueryMethod::Unlink,
        };
    }
}
