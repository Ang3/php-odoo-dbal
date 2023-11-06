<?php

declare(strict_types=1);

namespace Ang3\Component\Odoo\DBAL\Query;

interface QueryInterface
{
    public function getName(): string;

    public function getMethod(): string;

    public function getParameters(): array;

    public function getOptions(): array;
}
