<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Loader;

use Ang3\Component\Odoo\DBAL\Query\RecordNotFoundException;

class SingleLoader extends AbstractLoader
{
    public function __construct(
        string $model,
        callable $callable,
        private readonly int $id,
        private readonly ?string $name = null
    ) {
        parent::__construct($model, $callable);
    }

    public function __toString(): string
    {
        return $this->name ?: '#'.$this->id;
    }

    /**
     * @throws RecordNotFoundException when the record was not found
     */
    public function load(array $fields = [], array $context = []): array
    {
        $record = parent::load($fields, $context);

        if (null === $record) {
            throw new RecordNotFoundException($this->model, $this->id);
        }

        if (!\is_array($record)) {
            throw new \UnexpectedValueException(sprintf('Expected result of type "array", got "%s".', get_debug_type($record)));
        }

        return $record;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
