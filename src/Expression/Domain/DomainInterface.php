<?php

declare(strict_types=1);

namespace Ang3\Component\Odoo\DBAL\Expression\Domain;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
interface DomainInterface extends \IteratorAggregate
{
    public function toArray(): array;
}
