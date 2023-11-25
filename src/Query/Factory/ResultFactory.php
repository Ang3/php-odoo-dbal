<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Factory;

use Ang3\Component\Odoo\DBAL\Query\Enum\QueryMethod;
use Ang3\Component\Odoo\DBAL\Query\Normalizer\ResultNormalizerInterface;
use Ang3\Component\Odoo\DBAL\Query\QueryInterface;
use Ang3\Component\Odoo\DBAL\Query\Result\ResultInterface;
use Ang3\Component\Odoo\DBAL\Query\Result\RowResult;
use Ang3\Component\Odoo\DBAL\Query\Result\ScalarResult;
use Ang3\Component\Odoo\DBAL\Schema\SchemaInterface;

class ResultFactory implements ResultFactoryInterface
{
    /**
     * Context parameters keys.
     */
    public const COLUMN_NAME_KEY = 'column_name';
    public const BUFFER_SIZE_KEY = 'buffer_size';

    private array $defaultContext = [
        self::COLUMN_NAME_KEY => null,
        self::BUFFER_SIZE_KEY => null,
    ];

    public function __construct(
        private readonly SchemaInterface $schema,
        private readonly ResultNormalizerInterface $resultNormalizer,
        array $defaultContext = []
    ) {
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    public function create(QueryInterface $query, array $data, array $context = []): ResultInterface
    {
        if (QueryMethod::tryFrom($query->getMethod())?->isSearch()) {
            return $this->createScalarResult($query, $data, $context);
        }

        return $this->createRowResult($query, $data, $context);
    }

    public function createScalarResult(QueryInterface $query, array $data, array $context): ScalarResult
    {
        $context = $this->getContext($context);

        if (QueryMethod::tryFrom($query->getMethod())?->isSearch()) {
            return new ScalarResult($query, $data, $context);
        }

        $columnName = $context[self::COLUMN_NAME_KEY] ?? null;

        if (!\is_string($columnName)) {
            $selectedFields = (array) ($this->options['fields'] ?? []);
            $columnName = array_shift($selectedFields) ?: 'id';
        }

        return $this->createRowResult($query, $data, $context)->scalars($columnName);
    }

    public function createRowResult(QueryInterface $query, array $data, array $context): RowResult
    {
        $context = $this->getContext($context);
        $model = $this->schema->getModel($query->getName());

        foreach ($data as $key => $row) {
            $data[$key] = $this->resultNormalizer->normalize($model, $row, $context);
        }

        return new RowResult($query, $data, $context);
    }

    public function getDefaultContext(): array
    {
        return $this->defaultContext;
    }

    /**
     * @internal
     */
    private function getContext(array $context): array
    {
        return array_merge($this->defaultContext, $context);
    }
}
