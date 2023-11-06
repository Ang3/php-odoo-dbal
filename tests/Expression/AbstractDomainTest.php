<?php

declare(strict_types=1);

namespace Ang3\Component\Odoo\DBAL\Tests\Expression;

use Ang3\Component\Odoo\DBAL\Expression\Domain\Comparison;
use Ang3\Component\Odoo\DBAL\Expression\Domain\CompositeDomain;
use Ang3\Component\Odoo\Tests\AbstractTest;

abstract class AbstractDomainTest extends AbstractTest
{
    public function createComparison(string $operator, string $fieldName, mixed $value): Comparison
    {
        return new Comparison($operator, $fieldName, $value);
    }

    public function createCompositeDomain(string $operator, array $domains = []): CompositeDomain
    {
        return new CompositeDomain($operator, $domains);
    }
}
