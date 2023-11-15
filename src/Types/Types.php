<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Types;

/**
 * Default built-in types provided by Odoo database.
 */
final class Types
{
    public const BINARY = 'binary';
    public const BOOLEAN = 'boolean';
    public const CHAR = 'char';
    public const DATE = 'date';
    public const DATETIME = 'datetime';
    public const FLOAT = 'float';
    public const HTML = 'html';
    public const INTEGER = 'integer';
    public const MONETARY = 'monetary';
    public const SELECTION = 'selection';
    public const TEXT = 'text';

    /**
     * @var \ReflectionClassConstant[]
     */
    private static array $constants = [];

    /**
     * @return string[]
     */
    public static function getKeys(): array
    {
        return array_keys(self::getConstants());
    }

    /**
     * @return string[]
     */
    public static function getValues(): array
    {
        return array_values(self::getConstants());
    }

    /**
     * @return \ReflectionClassConstant[]
     */
    private static function getConstants(): array
    {
        if (!self::$constants) {
            $class = new \ReflectionClass(self::class);
            self::$constants = $class->getConstants();
        }

        return self::$constants;
    }
}
