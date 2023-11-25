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
use Ang3\Component\Odoo\DBAL\Query\Loader\MultipleLoader;
use Ang3\Component\Odoo\DBAL\Query\Loader\SingleLoader;
use Ang3\Component\Odoo\DBAL\Query\QueryException;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\FieldMetadata;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;
use Ang3\Component\Odoo\DBAL\Schema\SchemaInterface;
use Ang3\Component\Odoo\DBAL\Types\TypeConverterInterface;

class ValueNormalizer
{
    public function __construct(
        private readonly SchemaInterface $schema,
        private readonly TypeConverterInterface $typeConverter
    ) {}

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
            if ($field->isSingleAssociation()) {
                $value = $value ? $this->getAssociationIdentifier($field, $value) : null;
            } else {
                if ($value instanceof OperationInterface) {
                    $operationArray = $value->toArray();
                    $data = $operationArray[2] ?? null;

                    if (0 !== $data && null !== $data) {
                        $operationArray[2] = $this->normalize($this->schema->getModel((string) $field->getTargetModelName()), $data, $context);
                    }

                    return $operationArray;
                }

                $values = $value instanceof MultipleLoader ? $value->getIds() : $value;

                if (!\is_array($values)) {
                    throw new QueryException(sprintf('Expected value of type "%s|array<int<1, max>>" for the field "%s" (model: %s), got "%s".', MultipleLoader::class, $field->getName(), $field->getModel()->getName(), get_debug_type($value)));
                }

                $value = array_map(fn ($value) => $this->getAssociationIdentifier($field, $value), $values);
            }

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

    /**
     * @internal
     *
     * @return int<1, max>
     */
    private function getAssociationIdentifier(FieldMetadata $field, mixed $value): int
    {
        $value = $value instanceof SingleLoader ? $value->getId() : $value;

        if (!\is_int($value) || $value < 1) {
            throw new QueryException(sprintf('Expected value of type "%s|int<1, max>" for the field "%s" (model: %s), got "%s".', SingleLoader::class, $field->getName(), $field->getModel()->getName(), get_debug_type($value)));
        }

        return $value;
    }
}
