<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Normalizer;

use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\CompositeDomain;
use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\Query\QueryException;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;
use Ang3\Component\Odoo\DBAL\Schema\SchemaException;
use Ang3\Component\Odoo\DBAL\Schema\SchemaInterface;
use Ang3\Component\Odoo\DBAL\Types\TypeConverter;
use Ang3\Component\Odoo\DBAL\Types\TypeConverterInterface;

class QueryNormalizer implements QueryNormalizerInterface
{
    private readonly TypeConverterInterface $typeConverter;
    private readonly ValueNormalizer $valueNormalizer;
    private readonly DomainNormalizer $domainNormalizer;
    private readonly OrderNormalizer $orderNormalizer;

    public function __construct(
        private readonly SchemaInterface $schema,
        TypeConverterInterface $typeConverter = null
    ) {
        $this->typeConverter = $typeConverter ?: new TypeConverter();
        $this->valueNormalizer = new ValueNormalizer($schema, $this->typeConverter);
        $this->domainNormalizer = new DomainNormalizer($schema, $this->valueNormalizer);
        $this->orderNormalizer = new OrderNormalizer();
    }

    public function normalizeDomains(QueryBuilder $queryBuilder, ModelMetadata $model): array
    {
        $criteria = $queryBuilder->getWhere();

        if (!$criteria) {
            return [[]];
        }

        $domainArray = $this->domainNormalizer->normalize($model, $criteria);

        return $criteria instanceof CompositeDomain ? [$domainArray] : [[$domainArray]];
    }

    public function normalizeValues(QueryBuilder $queryBuilder, ModelMetadata $model, array $context = []): array
    {
        if (!$queryBuilder->getValues()) {
            throw new QueryException('You must set values for queries of type "INSERT" and "UPDATE".');
        }

        return $this->valueNormalizer->normalize($model, $queryBuilder->getValues(), $context);
    }

    public function normalizeOptions(QueryBuilder $queryBuilder, ModelMetadata $model): array
    {
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

        return $options;
    }

    public function getSchema(): SchemaInterface
    {
        return $this->schema;
    }

    public function getTypeConverter(): TypeConverterInterface
    {
        return $this->typeConverter;
    }

    public function getValueNormalizer(): ValueNormalizer
    {
        return $this->valueNormalizer;
    }

    public function getDomainNormalizer(): DomainNormalizer
    {
        return $this->domainNormalizer;
    }

    public function getOrderNormalizer(): OrderNormalizer
    {
        return $this->orderNormalizer;
    }
}
