<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Normalizer;

use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\Comparison;
use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\CompositeDomain;
use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\DomainInterface;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;
use Ang3\Component\Odoo\DBAL\Schema\SchemaInterface;

class CriteriaNormalizer
{
    public function __construct(
        private readonly SchemaInterface $schema,
        private readonly ValueNormalizer $valueNormalizer
    ) {}

    public function normalize(ModelMetadata $model, array|DomainInterface $criteria = null): array
    {
        if (!$criteria) {
            return [[]];
        }

        if (\is_array($criteria)) {
            $criteria = CompositeDomain::criteria($criteria);
        }

        $domain = $this->normalizeDomain($model, $criteria);
        $domainArray = $domain->toArray();

        return $criteria instanceof CompositeDomain ? [$domainArray] : [[$domainArray]];
    }

    public function normalizeDomain(ModelMetadata $model, DomainInterface $domain): DomainInterface
    {
        $domain = clone $domain;

        if ($domain instanceof CompositeDomain) {
            $newDomain = (clone $domain)->resetDomains();

            foreach ($domain as $subDomain) {
                $newDomain->add($this->normalizeDomain($model, $subDomain));
            }

            return $newDomain;
        }

        if ($domain instanceof Comparison) {
            $field = $this->schema->getField($model, $domain->getFieldName());
            $domain->setValue($this->valueNormalizer->normalizeFieldValue($field, $domain->getValue()));
        }

        return $domain;
    }
}
