<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Tests\Types;

use Ang3\Component\Odoo\DBAL\Types\Types;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Types\Types
 *
 * @internal
 */
final class TypesTest extends TestCase
{
    /**
     * @covers ::getConstants
     * @covers ::getKeys
     */
    public function testGetKeys(): void
    {
        self::assertSame([
            'BINARY',
            'BOOLEAN',
            'CHAR',
            'DATE',
            'DATETIME',
            'FLOAT',
            'HTML',
            'INTEGER',
            'MONETARY',
            'SELECTION',
            'TEXT',
        ], Types::getKeys());
    }

    /**
     * @covers ::getConstants
     * @covers ::getValues
     */
    public function testGetValues(): void
    {
        self::assertSame([
            Types::BINARY,
            Types::BOOLEAN,
            Types::CHAR,
            Types::DATE,
            Types::DATETIME,
            Types::FLOAT,
            Types::HTML,
            Types::INTEGER,
            Types::MONETARY,
            Types::SELECTION,
            Types::TEXT,
        ], Types::getValues());
    }
}
