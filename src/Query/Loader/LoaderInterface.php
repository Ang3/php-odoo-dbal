<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Loader;

interface LoaderInterface
{
    /**
     * Loads the associated record(s).
     *
     * @param array $fields the fields to select - Leave empty to select all fields
     *
     * @return mixed The result
     */
    public function load(array $fields = [], array $context = []): mixed;

    /**
     * Gets the target model name of the association.
     */
    public function getModel(): string;
}
