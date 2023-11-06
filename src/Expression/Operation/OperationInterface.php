<?php

declare(strict_types=1);

namespace Ang3\Component\Odoo\DBAL\Expression\Operation;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
interface OperationInterface
{
    public function toArray(): array;
}
