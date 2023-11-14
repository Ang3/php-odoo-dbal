<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Enum;

enum OrmQueryMethod: string
{
    case Create = 'create';
    case Write = 'write';
    case Read = 'read';
    case Search = 'search';
    case SearchAndRead = 'search_read';
    case SearchAndCount = 'search_count';
    case Unlink = 'unlink';

    public function isReadingContext(): bool
    {
        return \in_array($this, [
            OrmQueryMethod::Search,
            OrmQueryMethod::SearchAndRead,
        ], true);
    }

    public function isWritingContext(): bool
    {
        return \in_array($this, [
            OrmQueryMethod::Create,
            OrmQueryMethod::Write,
        ], true);
    }

    public function isCount(): bool
    {
        return self::SearchAndCount === $this;
    }
}
