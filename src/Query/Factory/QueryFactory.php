<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Factory;

use Ang3\Component\Odoo\DBAL\Query\Normalizer\QueryNormalizerInterface;
use Ang3\Component\Odoo\DBAL\Query\Query;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\Query\QueryException;
use Ang3\Component\Odoo\DBAL\Query\QueryInterface;
use Ang3\Component\Odoo\DBAL\RecordManagerInterface;

class QueryFactory implements QueryFactoryInterface
{
    public function __construct(
        private readonly RecordManagerInterface $recordManager,
        private readonly QueryNormalizerInterface $queryNormalizer
    ) {
    }

    public function createQueryBuilder(string $modelName): QueryBuilder
    {
        return new QueryBuilder($this->recordManager, $modelName);
    }

    public function createQuery(QueryBuilder $queryBuilder): QueryInterface
    {
        $model = $this->recordManager->getSchema()->getModel($queryBuilder->getFrom());
        $queryMethod = $queryBuilder->getMethod();
        $query = new Query($this->recordManager, $model->getName(), $queryMethod->value);

        if ($queryMethod->isRead()) {
            $parameters = $this->queryNormalizer->normalizeDomains($queryBuilder, $model);
            $options = $this->queryNormalizer->normalizeOptions($queryBuilder, $model);
            $query->setOptions($options);
        } elseif ($queryMethod->isDeletion()) {
            $ids = $queryBuilder->getIds();

            if (!$ids) {
                throw new QueryException('You must set indexes for queries of type "DELETE".');
            }

            $parameters = [$ids];
        } else {
            $parameters = $this->queryNormalizer->normalizeValues($queryBuilder, $model);

            if (!$parameters) {
                throw new QueryException('You must set at least one value for queries of type "CREATE" or "UPDATE".');
            }

            if ($queryMethod->isUpdate()) {
                $ids = $queryBuilder->getIds();

                if (!$ids) {
                    throw new QueryException('You must set indexes for queries of type "UPDATE".');
                }

                $parameters = [$ids, $parameters];
            } else {
                $parameters = [$parameters];
            }
        }

        $query->setParameters($parameters);

        return $query;
    }

    public function getRecordManager(): RecordManagerInterface
    {
        return $this->recordManager;
    }

    public function getNormalizer(): QueryNormalizerInterface
    {
        return $this->queryNormalizer;
    }
}
