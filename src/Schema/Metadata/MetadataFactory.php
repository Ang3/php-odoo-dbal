<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Schema\Metadata;

use Ang3\Component\Odoo\Client;
use Ang3\Component\Odoo\DBAL\Query\Enum\OrmQueryMethod;
use Ang3\Component\Odoo\DBAL\Schema\Enum\IrModel;

class MetadataFactory implements MetadataFactoryInterface
{
    public function __construct(private readonly Client $client) {}

    public function createModel(array $payload): ModelMetadata
    {
        $fields = (array) $this->client->executeKw(
            IrModel::Fields->value,
            OrmQueryMethod::SearchAndRead->value,
            [['model_id', '=', $payload['id']]]
        );

        foreach ($fields as $key => $fieldData) {
            $choices = [];
            $fieldData = (array) $fieldData;
            $selectionsIds = (array) ($fieldData['selection_ids'] ?? []);
            $selectionsIds = array_filter($selectionsIds);

            if (!empty($selectionsIds)) {
                $choices = (array) $this->client->executeKw(
                    IrModel::FieldsSelection->value,
                    OrmQueryMethod::SearchAndRead->value,
                    [['field_id', '=', $fieldData['id']]]
                );

                foreach ($choices as $index => $choice) {
                    if (\is_array($choice)) {
                        $choices[$index] = new ChoiceMetadata((string) $choice['name'], $choice['value'], (int) $choice['id']);
                    }
                }
            } elseif (!empty($fieldData['selection'])) {
                if (preg_match_all('#^\[\s*(\(\'(\w+)\'\,\s*\'(\w+)\'\)\s*\,?\s*)*\s*\]$#', trim($fieldData['selection']), $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        if (isset($match[2], $match[3])) {
                            $choices[] = new ChoiceMetadata($match[3], $match[2]);
                        }
                    }
                }
            }

            if ($choices) {
                $fieldData['selection'] = $choices;
            }

            $fields[$key] = new FieldMetadata($fieldData);
        }

        return new ModelMetadata($payload, $fields);
    }
}
