<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query;

use Ang3\Component\Odoo\DBAL\Query\Normalizer\CriteriaNormalizer;
use Ang3\Component\Odoo\DBAL\Query\Normalizer\OrderNormalizer;
use Ang3\Component\Odoo\DBAL\Query\Normalizer\ValueNormalizer;
use Ang3\Component\Odoo\DBAL\RecordManager;
use Ang3\Component\Odoo\DBAL\Schema\SchemaException;

class QueryFactory implements QueryFactoryInterface
{
    private readonly CriteriaNormalizer $criteriaNormalizer;
    private readonly ValueNormalizer $valueNormalizer;
    private readonly OrderNormalizer $orderNormalizer;

    public function __construct(private readonly RecordManager $recordManager)
    {
        $this->criteriaNormalizer = new CriteriaNormalizer($this->recordManager);
        $this->valueNormalizer = new ValueNormalizer($this->recordManager);
        $this->orderNormalizer = new OrderNormalizer();
    }

    public function create(QueryBuilder $queryBuilder): OrmQuery
    {
        $ormQueryMethod = $queryBuilder->getMethod()->getOrmQueryMethod();
        $query = new OrmQuery($queryBuilder->getRecordManager(), $queryBuilder->getFrom(), $ormQueryMethod->value);
        $model = $this->recordManager->getSchema()->getModel($queryBuilder->getFrom());

        if ($queryBuilder->getMethod()->isReadingContext()) {
            $parameters = $this->criteriaNormalizer->normalize($model, $queryBuilder->getWhere());
        } elseif ($queryBuilder->getMethod()->isDeletion()) {
            if (!$queryBuilder->getIds()) {
                throw new QueryException('You must set indexes for queries of type "DELETE".');
            }

            $parameters = [$queryBuilder->getIds()];
        } else {
            if (!$queryBuilder->getValues()) {
                throw new QueryException('You must set values for queries of type "INSERT" and "UPDATE".');
            }

            $parameters = $this->valueNormalizer->normalize($model, $queryBuilder->getValues());

            if ($queryBuilder->getMethod()->isUpdate()) {
                if (!$queryBuilder->getIds()) {
                    throw new QueryException('You must set indexes for queries of type "UPDATE".');
                }

                $parameters = [$queryBuilder->getIds(), $parameters];
            } else {
                $parameters = [$parameters];
            }
        }

        $query->setParameters($parameters);

        if ($queryBuilder->getMethod()->isReadingContext()) {
            $options = [];

            if ($queryBuilder->getMethod()->isSelection() && $queryBuilder->getSelect()) {
                $options['fields'] = $queryBuilder->getSelect();

                foreach ($options['fields'] as $fieldName) {
                    if (!\is_string($fieldName)) {
                        throw new QueryException(sprintf('Expected selected fields of type "string", got "%s".', get_debug_type($fieldName)));
                    }

                    if (!$model->hasField($fieldName)) {
                        throw SchemaException::fieldNotFound($model->getName(), $fieldName);
                    }
                }
            }

            if ($orders = $this->orderNormalizer->normalize($model, $queryBuilder->getOrders())) {
                $options['order'] = $orders;
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
