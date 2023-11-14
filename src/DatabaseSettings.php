<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL;

class DatabaseSettings
{
    public const DEFAULT_TIMEZONE = 'UTC';

    public function __construct(private readonly string $timezone = self::DEFAULT_TIMEZONE) {}

    public function getTimezone(): string
    {
        return $this->timezone;
    }
}
