<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Normalizer;

use Ang3\Component\Odoo\DBAL\Schema\Metadata\ModelMetadata;
use Ang3\Component\Odoo\DBAL\Types\TypeConverterInterface;

class ResultNormalizer implements ResultNormalizerInterface
{
    public function __construct(private readonly TypeConverterInterface $typeConverter)
    {
    }

    public function normalize(ModelMetadata $model, array $payload = [], array $context = []): array
    {
        foreach ($payload as $fieldName => $value) {
            $field = $model->getField($fieldName);

            if ($field->isAssociation() || null === $value) {
                continue;
            }

            $payload[$fieldName] = $this->typeConverter->convertToPhpValue($value, $field->getType()->value, $context);
        }

        return $payload;
    }
}
