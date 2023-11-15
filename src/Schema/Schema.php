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
use Ang3\Component\Odoo\DBAL\Schema\Enum\CacheKey;
use Ang3\Component\Odoo\DBAL\Schema\Enum\IrModel;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\FieldMetadata;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\MetadataFactory;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\MetadataFactoryInterface;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;
use Ang3\Component\Odoo\DBAL\Schema\Resolver\FieldResolver;

class Schema implements SchemaInterface
{
    private MetadataFactoryInterface $metadataFactory;
    private FieldResolver $fieldResolver;

    /** @var string[] */
    private array $modelNames = [];

    public function __construct(
        private readonly RecordManager $recordManager,
        MetadataFactoryInterface $metadataFactory = null
    ) {
        $this->metadataFactory = $metadataFactory ?: new MetadataFactory($this->recordManager->getClient());
        $this->fieldResolver = new FieldResolver($this);
    }

    public function getModel(string $modelName): ModelMetadata
    {
        if (!$this->hasModel($modelName)) {
            throw SchemaException::modelNotFound($modelName);
        }

        $modelItem = $this->recordManager
            ->getConfiguration()
            ->getMetadataCache()
            ->getItem(CacheKey::model($modelName))
        ;

        $model = $modelItem->get();

        if ($model instanceof ModelMetadata) {
            return $model;
        }

        $modelData = (array) $this->recordManager
            ->getClient()
            ->executeKw(
                IrModel::Model->value,
                OrmQueryMethod::SearchAndRead->value,
                [[['model', '=', $modelName]]]
            )
        ;

        $modelData = $modelData[0] ?? null;

        if (!\is_array($modelData)) {
            throw SchemaException::modelNotFound($modelName);
        }

        $model = $this->metadataFactory->createModel($modelData);

        // Caching
        $this->recordManager
            ->getConfiguration()
            ->getMetadataCache()
            ->save($modelItem->set($model))
        ;

        return $model;
    }

    public function getField(ModelMetadata|string $model, string $fieldName): FieldMetadata
    {
        $model = $model instanceof ModelMetadata ? $model : $this->getModel($model);

        return $this->fieldResolver->getField($model, $fieldName);
    }

    public function hasModel(string $modelName): bool
    {
        return \in_array($modelName, $this->getModelNames(), true);
    }

    public function getModelNames(): array
    {
        if (!$this->modelNames) {
            $modelNamesItem = $this->recordManager
                ->getConfiguration()
                ->getMetadataCache()
                ->getItem(CacheKey::ModelNames->value)
            ;

            $modelNames = $modelNamesItem->get();

            if (\is_array($modelNames)) {
                $this->modelNames = $modelNames;

                return $this->modelNames;
            }

            $modelNames = array_column((array) $this->recordManager
                ->getClient()
                ->executeKw(IrModel::Model->value, OrmQueryMethod::SearchAndRead->value, [[]], [
                    'fields' => ['model'],
                ]), 'model');

            $this->modelNames = $modelNames;

            // Caching
            $this->recordManager
                ->getConfiguration()
                ->getMetadataCache()
                ->save($modelNamesItem->set($modelNames))
            ;
        }

        return $this->modelNames;
    }

    public function getMetadataFactory(): MetadataFactoryInterface
    {
        return $this->metadataFactory;
    }

    public function getFieldResolver(): FieldResolver
    {
        return $this->fieldResolver;
    }
}
