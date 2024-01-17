<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Normalizer;

use Ang3\Component\Odoo\DBAL\Schema\Metadata\FieldMetadata;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;
use Ang3\Component\Odoo\DBAL\Types\TypeConverterInterface;

class ValueNormalizer
{
    public function __construct(
        private readonly TypeConverterInterface $typeConverter
    ) {
    }

    public function normalize(ModelMetadata $model, array $values = [], array $context = []): array
    {
        foreach ($values as $fieldName => $value) {
            $values[$fieldName] = $this->normalizeFieldValue($model->getField($fieldName), $value, $context);
        }

        return $values;
    }

    public function normalizeFieldValue(FieldMetadata $field, mixed $value, array $context = []): mixed
    {
        if ($field->isAssociation()) {
            return $value;
        }

        if (\is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->normalizeFieldValue($field, $v);
            }

            return $value;
        }

        return $this->typeConverter->convertToDatabaseValue($value, $field->getType()->value, $context);
    }
}
