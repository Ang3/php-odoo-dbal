<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Normalizer;

use Ang3\Component\Odoo\DBAL\Query\Expression\Operation\OperationInterface;
use Ang3\Component\Odoo\DBAL\RecordManager;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\FieldMetadata;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;

class ValueNormalizer
{
    public function __construct(private readonly RecordManager $recordManager) {}

    public function normalize(ModelMetadata $model, array $values = []): array
    {
        foreach ($values as $fieldName => $value) {
            if ($value instanceof OperationInterface) {
                $values[$fieldName] = $this->normalizeOperation($model, $fieldName, $value);

                continue;
            }

            $values[$fieldName] = $this->normalizeFieldValue($model->getField($fieldName), $value);
        }

        return $values;
    }

    public function normalizeOperation(ModelMetadata $model, string $fieldName, OperationInterface $operation): array
    {
        $operationArray = $operation->toArray();
        $data = $operationArray[2] ?? null;

        if (0 !== $data && null !== $data) {
            $operationArray[2] = $this->normalizeFieldValue($model->getField($fieldName), $data);
        }

        return $operationArray;
    }

    public function normalizeFieldValue(FieldMetadata $field, mixed $value): mixed
    {
        if (!$field->isAssociation() && \is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->normalizeFieldValue($field, $v);
            }

            return $value;
        }

        return $this->recordManager->getTypeConverter()->convertToDatabaseValue($value, $field->getType()->value);
    }
}
