<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Normalizer;

use Ang3\Component\Odoo\DBAL\Query\Loader\LoaderFactory;
use Ang3\Component\Odoo\DBAL\Query\Loader\LoaderFactoryInterface;
use Ang3\Component\Odoo\DBAL\RecordManagerInterface;
use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;
use Ang3\Component\Odoo\DBAL\Types\TypeConverterInterface;

class ResultNormalizer implements ResultNormalizerInterface
{
    private readonly LoaderFactoryInterface $loaderFactory;

    public function __construct(
        private readonly RecordManagerInterface $recordManager,
        private readonly TypeConverterInterface $typeConverter,
        ?LoaderFactoryInterface $loaderFactory = null
    ) {
        $this->loaderFactory = $loaderFactory ?: new LoaderFactory($this->recordManager);
    }

    public function normalize(ModelMetadata $model, array $payload = [], array $context = []): array
    {
        foreach ($payload as $fieldName => $value) {
            $field = $model->getField($fieldName);

            // We skip the ID
            if ('id' === $fieldName) {
                continue;
            }

            if ($field->isAssociation() && $field->getTargetModelName()) {
                if ($value && \is_array($value)) {
                    if ($field->isSingleAssociation()) {
                        [$id, $name] = [
                            $value[0] ?? null,
                            !empty($value[1]) ? (string) $value[1] : null,
                        ];

                        if (null === $id) {
                            continue;
                        }

                        $payload[$fieldName] = $this->loaderFactory->single($field->getTargetModelName(), $id, $name);

                        continue;
                    }

                    if ($field->isMultipleAssociation()) {
                        $payload[$fieldName] = $this->loaderFactory->multiple($field->getTargetModelName(), $value);
                    }
                }

                continue;
            }

            $payload[$fieldName] = $this->typeConverter->convertToPhpValue($value, $field->getType()->value, $context);
        }

        return $payload;
    }
}
