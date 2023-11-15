<?php

namespace Ang3\Component\Odoo\DBAL\Tests\Types;

use Ang3\Component\Odoo\DBAL\Types\Types;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Types\Types
 */
class TypesTest extends TestCase
{
    /**
     * @covers ::getKeys
     * @covers ::getConstants
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
            'TEXT'
        ], Types::getKeys());
    }

    /**
     * @covers ::getValues
     * @covers ::getConstants
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
            Types::TEXT
        ], Types::getValues());
    }
}