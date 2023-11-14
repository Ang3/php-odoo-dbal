<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Schema\Enum;

enum CacheKey: string
{
    /** @internal Cache prefix key */
    private const PREFIX = 'ang3_odoo_dbal';

    case Models = self::PREFIX.'.schema.model';
    case ModelNames = self::PREFIX.'.schema.models';

    public static function model(string $modelName): string
    {
        return sprintf('%s.%s', self::Models->value, $modelName);
    }
}
