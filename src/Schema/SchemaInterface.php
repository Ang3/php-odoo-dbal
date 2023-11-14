<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Schema;

use Ang3\Component\Odoo\DBAL\Schema\Metadata\FieldMetadata;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;

interface SchemaInterface
{
    /**
     * Gets the model metadata.
     *
     * @throws SchemaException when the model was not found
     */
    public function getModel(string $modelName): ModelMetadata;

    /**
     * Gets a field from a model recursively.
     *
     * @throws \InvalidArgumentException on invalid field name
     * @throws SchemaException           when the field was not found
     */
    public function getField(ModelMetadata|string $model, string $fieldName): FieldMetadata;

    /**
     * Checks if a model exists.
     */
    public function hasModel(string $modelName): bool;

    /**
     * Gets all model names.
     *
     * @return string[]
     */
    public function getModelNames(): array;
}
