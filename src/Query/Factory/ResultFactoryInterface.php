<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Factory;

use Ang3\Component\Odoo\DBAL\Query\QueryInterface;
use Ang3\Component\Odoo\DBAL\Query\Result\ResultInterface;
use Ang3\Component\Odoo\DBAL\Query\Result\RowResult;
use Ang3\Component\Odoo\DBAL\Query\Result\ScalarResult;

interface ResultFactoryInterface
{
    /**
     * Creates the result of the query.
     */
    public function create(QueryInterface $query, array $data, array $context = []): ResultInterface;

    /**
     * Creates the scalar result of the query.
     * This method is used to create the result of a search query.
     */
    public function createScalarResult(QueryInterface $query, array $data, array $context): ScalarResult;

    /**
     * Creates the array result of the query.
     * This method is used to create the result of a select query.
     */
    public function createRowResult(QueryInterface $query, array $data, array $context): RowResult;
}
