<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Schema;

use Ang3\Component\Odoo\DBAL\Query\Enum\OrmQueryMethod;
use Ang3\Component\Odoo\DBAL\RecordManager;

class Schema
{
    public const IR_MODEL = 'ir.model';
    public const IR_MODEL_FIELDS = 'ir.model.fields';
    public const IR_MODEL_FIELD_SELECTION = 'ir.model.fields.selection';

    /**
     * @var string[]
     */
    private array $modelNames = [];

    /**
     * @var Model[]
     */
    private array $loadedModels = [];

    public function __construct(private readonly RecordManager $recordManager) {}

    /**
     * @throws SchemaException when the model was not found
     */
    public function getModel(string $modelName): Model
    {
        if (!$this->hasModel($modelName)) {
            throw SchemaException::modelNotFound($modelName);
        }

        if (!isset($this->loadedModels[$modelName])) {
            $expr = $this->recordManager->getExpressionBuilder();
            $modelData = (array) $this->recordManager
                ->getClient()
                ->executeKw(self::IR_MODEL, OrmQueryMethod::SearchAndRead->value, $this->recordManager->getDataNormalizer()->normalizeDomains($expr->eq('model', $modelName)))
            ;

            $modelData = $modelData[0] ?? null;

            if (!\is_array($modelData)) {
                throw SchemaException::modelNotFound($modelName);
            }

            $this->loadedModels[$modelName] = $this->createModel($modelData);
        }

        return $this->loadedModels[$modelName];
    }

    public function hasModel(string $modelName): bool
    {
        return \in_array($modelName, $this->getModelNames(), true);
    }

    /**
     * Gets all model names.
     *
     * @return string[]
     */
    public function getModelNames(): array
    {
        if (!$this->modelNames) {
            $this->modelNames = array_column((array) $this->recordManager
                ->getClient()
                ->executeKw(self::IR_MODEL, OrmQueryMethod::SearchAndRead->value, [[]], [
                    'fields' => ['model'],
                ]), 'model');
        }

        return $this->modelNames;
    }

    /**
     * @internal
     */
    private function createModel(array $modelData): Model
    {
        $expr = $this->recordManager->getExpressionBuilder();
        $fields = (array) $this->recordManager
            ->getClient()
            ->executeKw(
                self::IR_MODEL_FIELDS,
                OrmQueryMethod::SearchAndRead->value,
                $this->recordManager->getDataNormalizer()->normalizeDomains($expr->eq('model_id', $modelData['id']))
            )
        ;

        foreach ($fields as $key => $fieldData) {
            $choices = [];
            $fieldData = (array) $fieldData;
            $selectionsIds = (array) ($fieldData['selection_ids'] ?? []);
            $selectionsIds = array_filter($selectionsIds);

            if (!empty($selectionsIds)) {
                $choices = (array) $this->recordManager
                    ->getClient()
                    ->executeKw(
                        self::IR_MODEL_FIELD_SELECTION,
                        OrmQueryMethod::SearchAndRead->value,
                        $this->recordManager->getDataNormalizer()->normalizeDomains($expr->eq('field_id', $fieldData['id']))
                    )
                ;

                foreach ($choices as $index => $choice) {
                    if (\is_array($choice)) {
                        $choices[$index] = new Choice((string) $choice['name'], $choice['value'], (int) $choice['id']);
                    }
                }
            } elseif (!empty($fieldData['selection'])) {
                if (preg_match_all('#^\[\s*(\(\'(\w+)\'\,\s*\'(\w+)\'\)\s*\,?\s*)*\s*\]$#', trim($fieldData['selection']), $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        if (isset($match[2], $match[3])) {
                            $choices[] = new Choice($match[3], $match[2]);
                        }
                    }
                }
            }

            if ($choices) {
                $fieldData['selection'] = $choices;
            }

            $fields[$key] = new Field($fieldData);
        }

        return new Model($this, $modelData, $fields);
    }
}
