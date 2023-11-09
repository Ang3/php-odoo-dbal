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

interface RepositoryRegistryInterface
{
    public function setRecordManager(RecordManager $recordManager): self;

    public function add(RecordRepositoryInterface $repository): self;

    public function get(string $modelName): RecordRepositoryInterface;
}
