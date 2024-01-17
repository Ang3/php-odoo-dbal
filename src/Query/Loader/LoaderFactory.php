<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Loader;

use Ang3\Component\Odoo\DBAL\RecordManagerInterface;

class LoaderFactory implements LoaderFactoryInterface
{
    public function __construct(private readonly RecordManagerInterface $recordManager)
    {
    }

    public function single(string $modelName, int $id, string $name = null): SingleLoader
    {
        return new SingleLoader($modelName, fn (array $fields = []) => $this->recordManager->read($modelName, $id, $fields), $id, $name);
    }

    public function multiple(string $modelName, array $ids): MultipleLoader
    {
        $queryBuilder = $this->recordManager->getRepository($modelName)->createQueryBuilder();

        return new MultipleLoader($modelName, static function (array $fields = [], array $context = []) use ($queryBuilder, $ids) {
            return $queryBuilder
                ->select($fields)
                ->where($queryBuilder->expr()->in('id', $ids))
                ->getQuery()
                ->getResult($context)
            ;
        }, $ids);
    }
}
