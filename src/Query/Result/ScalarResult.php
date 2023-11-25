<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-dbal
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\DBAL\Query\Result;

/**
 * This class represents a scalar result: an array of scalar values.
 *
 * @method \Generator|bool[]|int[]|float[]|string[] getIterator()
 * @method bool|int|float|string|null               offsetGet(mixed $offset)
 * @method void                                     offsetSet(mixed $offset, bool|int|float|string|null $value)
 * @method bool|int|float|string|null               first()
 * @method bool|int|float|string|null               current()
 * @method bool|int|float|string|null               fetch()
 * @method \Generator|bool[]|int[]|float[]|string[] fetchAll()
 * @method bool|int|float|string|null               last()
 * @method bool[]|int[]|float[]|string[]            toArray()
 */
class ScalarResult extends ArrayResult
{
    /**
     * @internal
     */
    protected function assertValue(mixed $value): null|bool|float|int|string
    {
        if (null !== $value && !\is_scalar($value)) {
            throw ResultException::invalidValue($value, ['null', 'scalar']);
        }

        return $value;
    }
}
