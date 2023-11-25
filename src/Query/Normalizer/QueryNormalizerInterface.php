<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Normalizer;

use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;

interface QueryNormalizerInterface
{
    public function normalizeDomains(QueryBuilder $queryBuilder, ModelMetadata $model): array;

    public function normalizeValues(QueryBuilder $queryBuilder, ModelMetadata $model): array;

    public function normalizeOptions(QueryBuilder $queryBuilder, ModelMetadata $model): array;
}
