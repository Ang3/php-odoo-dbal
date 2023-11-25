<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Loader;

abstract class AbstractLoader implements LoaderInterface
{
    /**
     * The loader to use to load the target record.
     * The expected signature of the callable: fn(array $fields = [], array $context = []).
     *
     * @var callable
     */
    protected $callable;

    public function __construct(protected readonly string $model, callable $callable)
    {
        $this->callable = $callable;
    }

    public function load(array $fields = [], array $context = []): mixed
    {
        $callable = $this->callable;

        return $callable($fields, $context);
    }

    public function getModel(): string
    {
        return $this->model;
    }
}
