<?php

declare(strict_types=1);

namespace Ang3\Component\Odoo\DBAL\Schema;

class Model
{
    private int $id;
    private string $name;
    private string $displayName;
    private bool $transient;

    /**
     * @var Field[]
     */
    private array $fields = [];

    /**
     * @param Field[] $fields
     */
    public function __construct(private readonly Schema $schema, array $data, array $fields = [])
    {
        $this->id = (int) $data['id'];
        $this->name = (string) $data['model'];
        $this->displayName = (string) $data['name'];
        $this->transient = (bool) $data['transient'];

        foreach ($fields as $field) {
            $this->addField($field);
        }
    }

    public function getSchema(): Schema
    {
        return $this->schema;
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

    public function hasField(string $fieldName): bool
    {
        try {
            $this->getField($fieldName);
        } catch (SchemaException) {
            return false;
        }

        return true;
    }

    /**
     * @throws \InvalidArgumentException when the field name is empty
     * @throws SchemaException           when the field was not found
     */
    public function getField(string $fieldName): Field
    {
        $model = $this;
        $fields = explode('.', $fieldName);

        if (!$fields) {
            throw new \InvalidArgumentException('Empty field name.');
        }

        $lastKey = \count($fields) - 1;

        foreach ($fields as $key => $subFieldName) {
            $field = $model->getField($subFieldName);

            if ($lastKey === $key) {
                break;
            }

            $targetModel = $field->getTargetModelName();

            if (!$targetModel) {
                throw SchemaException::fieldNotFound($fieldName, $this);
            }

            $model = $this->schema->getModel($targetModel);
        }

        return $field;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array<string>
     */
    public function getFieldNames(): array
    {
        $fieldNames = [];

        foreach ($this->fields as $field) {
            $fieldNames[] = $field->getName();
        }

        return $fieldNames;
    }

    /**
     * @internal
     */
    private function addField(Field $field): void
    {
        $field->setModel($this);
        $this->fields[] = $field;
    }
}
