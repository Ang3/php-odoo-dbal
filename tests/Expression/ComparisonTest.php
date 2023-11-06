<?php

declare(strict_types=1);

namespace Ang3\Component\Odoo\DBAL\Tests\Expression;

use Ang3\Component\Odoo\DBAL\Expression\Domain\Comparison;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Expression\Domain\Comparison
 *
 * @internal
 */
final class ComparisonTest extends AbstractDomainTest
{
    /**
     * @covers ::getFieldName
     * @covers ::getOperator
     * @covers ::getValue
     * @covers ::setFieldName
     * @covers ::setOperator
     * @covers ::setValue
     *
     * @throws \ReflectionException
     */
    public function testAccessorsAndMutators(): void
    {
        $comparison = new Comparison('foo', Comparison::EQUAL_TO, 'bar');

        $this
            ->createObjectTester($comparison)
            ->assertPropertyAccessorsAndMutators('fieldName', 'bar')
            ->assertPropertyAccessorsAndMutators('operator', Comparison::NOT_EQUAL_TO)
            ->assertPropertyAccessorsAndMutators('value', 'mixed')
        ;
    }

    /**
     * @covers ::toArray
     */
    public function testToArray(): void
    {
        $comparison = new Comparison('foo', Comparison::EQUAL_TO, 'bar');

        static::assertSame(['foo', '=', 'bar'], $comparison->toArray());
    }
}
