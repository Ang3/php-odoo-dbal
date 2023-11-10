<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query;

use Ang3\Component\Odoo\DBAL\Query\Enum\OrmQueryMethod;
use Ang3\Component\Odoo\DBAL\Query\Enum\QueryBuilderMethod;

class QueryFactory implements QueryFactoryInterface
{
    public function create(QueryBuilder $queryBuilder): OrmQuery
    {
        $method = match ($queryBuilder->getMethod()) {
            QueryBuilderMethod::Select => OrmQueryMethod::SearchAndRead,
            QueryBuilderMethod::Search => OrmQueryMethod::Search,
            QueryBuilderMethod::Insert => OrmQueryMethod::Create,
            QueryBuilderMethod::Update => OrmQueryMethod::Write,
            QueryBuilderMethod::Delete => OrmQueryMethod::Unlink,
        };

        $query = new OrmQuery($queryBuilder->getRecordManager(), $queryBuilder->getFrom(), $method->value);

        if (\in_array($queryBuilder->getMethod(), [QueryBuilderMethod::Select, QueryBuilderMethod::Search], true)) {
            $parameters = $queryBuilder->expr()->normalizeDomains($queryBuilder->getWhere());
        } elseif (QueryBuilderMethod::Delete === $queryBuilder->getMethod()) {
            if (!$queryBuilder->getIds()) {
                throw new QueryException('You must set indexes for queries of type "DELETE".');
            }

            $parameters = [$queryBuilder->getIds()];
        } else {
            if (!$queryBuilder->getValues()) {
                throw new QueryException('You must set values for queries of type "INSERT" and "UPDATE".');
            }

            $parameters = $queryBuilder->expr()->normalizeData($queryBuilder->getValues());

            if (QueryBuilderMethod::Update === $queryBuilder->getMethod()) {
                if (!$queryBuilder->getIds()) {
                    throw new QueryException('You must set indexes for queries of type "UPDATE".');
                }

                $parameters = [$queryBuilder->getIds(), $parameters];
            } else {
                $parameters = [$parameters];
            }
        }

        $query->setParameters($parameters);

        if (\in_array($queryBuilder->getMethod(), [QueryBuilderMethod::Select, QueryBuilderMethod::Search], true)) {
            $options = [];

            if (QueryBuilderMethod::Select === $queryBuilder->getMethod() && $queryBuilder->getSelect()) {
                $options['fields'] = $queryBuilder->getSelect();
            }

            $orders = $queryBuilder->getOrders();

            if ($orders) {
                $normalizedOrders = [];

                foreach ($orders as $fieldName => $order) {
                    $normalizedOrders[$fieldName] = sprintf('%s %s', $fieldName, $order->value);
                }

                $options['order'] = implode(', ', $normalizedOrders);
            }

            if (null !== $queryBuilder->getFirstResult()) {
                $options['offset'] = $queryBuilder->getFirstResult();
            }

            if ($queryBuilder->getMaxResults()) {
                $options['limit'] = $queryBuilder->getMaxResults();
            }

            $query->setOptions($options);
        }

        return $query;
    }
}
