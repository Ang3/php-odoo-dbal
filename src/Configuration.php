<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL;

use Ang3\Component\Odoo\DBAL\Config\DatabaseSettings;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Configuration
{
    public const PACKAGE_PREFIX = 'ang3_odoo_dbal';

    private DatabaseSettings $databaseSettings;
    private CacheItemPoolInterface $metadataCache;

    public function __construct(
        DatabaseSettings $databaseSettings = null,
        CacheItemPoolInterface $metadataCache = null
    ) {
        $this->databaseSettings = $databaseSettings ?: new DatabaseSettings();
        $this->metadataCache = $metadataCache ?: new FilesystemAdapter(self::PACKAGE_PREFIX);
    }

    public function getDatabaseSettings(): DatabaseSettings
    {
        return $this->databaseSettings;
    }

    public function getMetadataCache(): CacheItemPoolInterface
    {
        return $this->metadataCache;
    }
}
