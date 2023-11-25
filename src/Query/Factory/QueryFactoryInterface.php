<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Factory;

use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\Query\QueryInterface;

interface QueryFactoryInterface
{
    /**
     * Creates a query builder for new ORM query.
     */
    public function createQueryBuilder(string $modelName): QueryBuilder;

    /**
     * Creates an ORM query from a query builder.
     */
    public function createQuery(QueryBuilder $queryBuilder): QueryInterface;
}
