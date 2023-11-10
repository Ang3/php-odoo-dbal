<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Expression;

use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\CompositeDomain;
use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\DomainInterface;
use Ang3\Component\Odoo\DBAL\Query\Expression\Exception\ConversionException;
use Ang3\Component\Odoo\DBAL\Query\Expression\Operation\OperationInterface;

class DataNormalizer implements DataNormalizerInterface
{
    public function normalizeDomains(iterable $criteria = null): array
    {
        if (!$criteria) {
            return [[]];
        }

        if (\is_array($criteria)) {
            $criteria = CompositeDomain::criteria($criteria);
        }

        if (!$criteria instanceof DomainInterface) {
            throw new \InvalidArgumentException(sprintf('Expected parameter #1 of type %s|array<%s|array>, %s given', DomainInterface::class, DomainInterface::class, \gettype($criteria)));
        }

        $criteriaArray = $this->normalizeValue($criteria->toArray());

        return $criteria instanceof CompositeDomain ? [$criteriaArray] : [[$criteriaArray]];
    }

    public function normalizeData(array $data = []): array
    {
        return (array) $this->normalizeValue($data);
    }

    public function normalizeValue(mixed $value): mixed
    {
        if (\is_scalar($value)) {
            return $value;
        }

        if (\is_array($value) || is_iterable($value)) {
            $values = [];

            foreach ($value as $key => $aValue) {
                $values[$key] = $this->normalizeValue($aValue);
            }

            return $values;
        }

        if (\is_object($value)) {
            if ($value instanceof DomainInterface) {
                return $this->normalizeValue($value->toArray());
            }

            if ($value instanceof OperationInterface) {
                return $this->normalizeValue($value->toArray());
            }

            if ($value instanceof \DateTimeInterface) {
                try {
                    $date = new \DateTime(sprintf('@%s', $value->getTimestamp()));
                } catch (\Exception $e) {
                    throw new ConversionException(sprintf('Failed to convert date from timestamp "%d"', $value->getTimestamp()), 0, $e);
                }

                return $date
                    ->setTimezone(new \DateTimeZone('UTC'))
                    ->format('Y-m-d H:i:s')
                ;
            }
        }

        return (string) $value;
    }
}
