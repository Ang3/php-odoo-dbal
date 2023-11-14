<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Schema\Metadata;

use Ang3\Component\Odoo\DBAL\Schema\Enum\ColumnType;
use Ang3\Component\Odoo\DBAL\Schema\Enum\DateTimeFormat;

class FieldMetadata
{
    private ModelMetadata $model;
    private int $id;
    private string $name;
    private ColumnType $type;
    private bool $required;
    private bool $readOnly;
    private ?string $displayName;
    private ?int $size;
    private ?SelectionMetadata $selection;
    private ?string $targetModelName;
    private ?string $targetFieldName;

    public function __construct(array $data = [])
    {
        $this->setData($data);
    }

    public static function create(array $data): self
    {
        return new self($data);
    }

    public function setData(array $data): self
    {
        $this->id = (int) $data['id'];
        $this->name = (string) $data['name'];
        $this->type = ColumnType::from((string) $data['ttype']);
        $this->required = (bool) $data['required'];
        $this->readOnly = (bool) $data['readonly'];
        $this->displayName = $data['display_name'] ?? null;
        $this->size = $data['size'] ?? null;
        $this->selection = $data['selection'] ?? null ? new SelectionMetadata($data['selection']) : null;
        $this->targetModelName = $data['relation'] ?? null ? $data['relation'] : null;
        $this->targetFieldName = $data['relation_field'] ?? null ? $data['relation_field'] : null;

        return $this;
    }

    public function getModel(): ModelMetadata
    {
        return $this->model;
    }

    public function setModel(ModelMetadata $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): ColumnType
    {
        return $this->type;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    public function getDisplayName(): string
    {
        return $this->displayName ?: $this->name;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getSelection(): ?SelectionMetadata
    {
        return $this->selection;
    }

    public function getTargetModelName(): ?string
    {
        return $this->targetModelName;
    }

    public function getTargetFieldName(): ?string
    {
        return $this->targetFieldName;
    }

    public function isIdentifier(): bool
    {
        return 'id' === $this->name;
    }

    public function isBinary(): bool
    {
        return ColumnType::Binary === $this->type;
    }

    public function isBoolean(): bool
    {
        return ColumnType::Boolean === $this->type;
    }

    public function isInteger(): bool
    {
        return ColumnType::Integer === $this->type;
    }

    public function isFloat(): bool
    {
        return \in_array($this->type, [ColumnType::Float, ColumnType::Monetary], true);
    }

    public function isNumber(): bool
    {
        return $this->isInteger() || $this->isFloat();
    }

    public function isString(): bool
    {
        return \in_array($this->type, [ColumnType::Char, ColumnType::Text, ColumnType::Html], true);
    }

    public function isDate(): bool
    {
        return \in_array($this->type, [ColumnType::Date, ColumnType::DateTime], true);
    }

    public function getDateFormat(): DateTimeFormat
    {
        return ColumnType::DateTime === $this->type ? DateTimeFormat::Long : DateTimeFormat::Short;
    }

    public function isSelection(): bool
    {
        return ColumnType::Selection === $this->type;
    }

    public function isSelectable(): bool
    {
        return null !== $this->selection;
    }

    public function isAssociation(): bool
    {
        return $this->isSingleAssociation() || $this->isMultipleAssociation();
    }

    public function isSingleAssociation(): bool
    {
        return ColumnType::ManyToOne === $this->type;
    }

    public function isMultipleAssociation(): bool
    {
        return \in_array($this->type, [
            ColumnType::ManyToMany,
            ColumnType::OneToMany,
        ], true);
    }
}
