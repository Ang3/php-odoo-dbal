<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Schema\Resolver;

use Ang3\Component\Odoo\DBAL\Schema\Enum\ColumnType;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\FieldMetadata;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;
use Ang3\Component\Odoo\DBAL\Schema\SchemaException;
use Ang3\Component\Odoo\DBAL\Schema\SchemaInterface;

/**
 * This resolver is used to get recursive field from a model.
 */
class FieldResolver
{
    public function __construct(private readonly SchemaInterface $schema)
    {
    }

    /**
     * Gets the metadata of a field recursively with dot notation.
     *
     * @throws \InvalidArgumentException on invalid field name
     * @throws SchemaException           when the field was not found
     */
    public function getField(ModelMetadata $model, string $fieldName, array $context = []): FieldMetadata
    {
        $fields = explode('.', $fieldName);

        if (!$fields) {
            throw new \InvalidArgumentException('The field name cannot be empty.');
        }

        $firstFieldName = array_shift($fields);
        $field = $model->getField($firstFieldName);

        if (!$fields) {
            return $field;
        }

        $context['traversed_fields'] = (array) ($context['traversed_fields'] ?? []);
        $context['traversed_fields'][] = $firstFieldName;
        $flattenedCurrentFieldName = implode('.', $context['traversed_fields']);

        if (!$field->isAssociation()) {
            throw SchemaException::invalidFieldType($model->getName(), $flattenedCurrentFieldName, $field->getType(), ColumnType::associations());
        }

        $targetModelName = $field->getTargetModelName();

        if (!$targetModelName) {
            throw SchemaException::targetModelNotFound($model->getName(), $flattenedCurrentFieldName);
        }

        if ($targetModelName !== $model->getName()) {
            $targetModel = $this->schema->getModel($targetModelName);

            return $this->getField($targetModel, implode('.', $fields), $context);
        }

        return $field;
    }
}
