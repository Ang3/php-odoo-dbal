<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Enum;

enum QueryMethod: string
{
    /*
     * Insert a new record(s).
     */
    case Insert = 'create';

    /*
     * Update a record(s).
     */
    case Update = 'write';

    /*
     * Gets a record.
     */
    case Read = 'read';

    /*
     * Search record(s).
     */
    case Search = 'search';

    /*
     * Find record(s).
     */
    case SearchAndRead = 'search_read';

    /*
     * Count record(s).
     */
    case SearchAndCount = 'search_count';

    /*
     * Delete record(s).
     */
    case Delete = 'unlink';

    public function isRead(): bool
    {
        return $this->isSelection() || $this->isSearch();
    }

    public function isSelection(): bool
    {
        return self::SearchAndRead === $this;
    }

    public function isSearch(): bool
    {
        return self::Search === $this;
    }

    public function isWrite(): bool
    {
        return $this->isInsertion() || $this->isUpdate();
    }

    public function isInsertion(): bool
    {
        return self::Insert === $this;
    }

    public function isUpdate(): bool
    {
        return self::Update === $this;
    }

    public function isDeletion(): bool
    {
        return self::Delete === $this;
    }

    public function isCount(): bool
    {
        return self::SearchAndCount === $this;
    }
}
