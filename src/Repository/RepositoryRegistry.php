<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Repository;

use Ang3\Component\Odoo\DBAL\RecordManager;

class RepositoryRegistry
{
    public function __construct(private readonly RecordManager $recordManager, private array $repositories = []) {}

    public function add(RecordRepositoryInterface $repository): self
    {
        $this->repositories[$repository->getModelName()] = $repository;

        return $this;
    }

    public function get(string $modelName): RecordRepositoryInterface
    {
        return $this->repositories[$modelName] ?? new RecordRepository($this->recordManager, $modelName);
    }
}
