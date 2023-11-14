<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Normalizer;

use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;
use Ang3\Component\Odoo\DBAL\Schema\SchemaException;

class OrderNormalizer
{
    public function normalize(ModelMetadata $model, array $orders = []): ?string
    {
        $normalizedOrders = [];

        foreach ($orders as $fieldName => $order) {
            if (!$model->hasField($fieldName)) {
                throw SchemaException::fieldNotFound($model->getName(), $fieldName);
            }

            $normalizedOrders[$fieldName] = sprintf('%s %s', $fieldName, $order->value);
        }

        return $normalizedOrders ? implode(', ', $normalizedOrders) : null;
    }
}
