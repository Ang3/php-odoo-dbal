<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Expression;

use Ang3\Component\Odoo\DBAL\Types\ConversionException;

interface DataNormalizerInterface
{
    /**
     * @throws \InvalidArgumentException when $criteria value is not valid
     * @throws ConversionException       on data conversion failure
     */
    public function normalizeDomains(iterable $criteria = null): array;

    /**
     * @throws ConversionException on data conversion failure
     */
    public function normalizeData(array $data = []): array;

    /**
     * Normalizes values from PHP to Odoo.
     */
    public function normalizeValue(mixed $value): mixed;
}
