<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Schema\Metadata;

use Ang3\Component\Odoo\DBAL\Schema\SchemaException;

class ModelMetadata
{
    private int $id;
    private string $name;
    private string $displayName;
    private bool $transient;

    /**
     * @var FieldMetadata[]
     */
    private array $fields = [];

    /**
     * @param FieldMetadata[] $fields
     */
    public function __construct(array $data, array $fields = [])
    {
        $this->id = (int) $data['id'];
        $this->name = (string) $data['model'];
        $this->displayName = (string) $data['name'];
        $this->transient = (bool) $data['transient'];

        foreach ($fields as $field) {
            $this->addField($field);
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDisplayName(): string
    {
        return $this->displayName ?: $this->name;
    }

    public function isTransient(): bool
    {
        return $this->transient;
    }

    public function getField(string $fieldName): FieldMetadata
    {
        $field = $this->fields[$fieldName] ?? null;

        if (!$field) {
            throw SchemaException::fieldNotFound($this->name, $fieldName);
        }

        return $field;
    }

    public function hasField(string $fieldName): bool
    {
        return \array_key_exists($fieldName, $this->fields);
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @internal
     */
    private function addField(FieldMetadata $field): void
    {
        $field->setModel($this);
        $this->fields[$field->getName()] = $field;
    }
}
