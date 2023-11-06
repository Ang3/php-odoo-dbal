<?php

declare(strict_types=1);

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
