<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Loader;

use Ang3\Component\Odoo\DBAL\Query\Result\ResultInterface;

class MultipleLoader extends AbstractLoader
{
    public function __construct(
        string $model,
        callable $callable,
        private readonly array $ids
    ) {
        parent::__construct($model, $callable);
    }

    public function load(array $fields = [], array $context = []): ResultInterface
    {
        $result = parent::load($fields, $context);

        if (!$result instanceof ResultInterface) {
            throw new \UnexpectedValueException(sprintf('Expected result of type "%s", got "%s".', ResultInterface::class, get_debug_type($result)));
        }

        return $result;
    }

    public function getIds(): array
    {
        return $this->ids;
    }
}
