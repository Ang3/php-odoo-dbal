<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Types;

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
abstract class AbstractTypeTest extends TestCase
{
    /**
     * @internal
     */
    public static function nonScalarValueProvider(): iterable
    {
        return [
            [['foo' => 'bar']],
            [new \stdClass()],
        ];
    }
}
