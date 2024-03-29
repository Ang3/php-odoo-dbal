<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Utils;

use PHPUnit\Framework\TestCase;

abstract class TestDecorator
{
    protected TestCase $testCase;

    public function __construct(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    public function getTestCase(): TestCase
    {
        return $this->testCase;
    }

    public function setTestCase(TestCase $testCase): self
    {
        $this->testCase = $testCase;

        return $this;
    }
}
