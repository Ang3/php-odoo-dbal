<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query;

use Ang3\Component\Odoo\DBAL\Query\Result\NoResultException;

class RecordNotFoundException extends NoResultException
{
    public function __construct(private readonly string $modelName, private readonly int $id)
    {
        parent::__construct(sprintf('No record found for model "%s" with ID #%d.', $this->modelName, $this->id));
    }

    public function getModelName(): string
    {
        return $this->modelName;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
