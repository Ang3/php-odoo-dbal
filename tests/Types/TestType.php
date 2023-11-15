<?php

namespace Ang3\Component\Odoo\DBAL\Tests\Types;

use Ang3\Component\Odoo\DBAL\Types\TypeInterface;

class TestType implements TypeInterface
{
    public const NAME = 'test';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToDatabaseValue(mixed $value, array $context = []): mixed
    {
        return $value;
    }

    public function convertToPhpValue(mixed $value, array $context = []): mixed
    {
        return $value;
    }
}