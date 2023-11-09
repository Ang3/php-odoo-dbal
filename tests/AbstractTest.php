<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests;

use Ang3\Component\Odoo\DBAL\Tests\Utils\ObjectTester;
use Ang3\Component\Odoo\DBAL\Tests\Utils\Reflector;
use PHPUnit\Framework\TestCase;

abstract class AbstractTest extends TestCase
{
    protected Reflector $reflector;

    protected function setUp(): void
    {
        $this->reflector = new Reflector();
    }

    /**
     * @throws \ReflectionException
     */
    protected function createObjectTester(object $object): ObjectTester
    {
        return new ObjectTester($this, $object);
    }
}
