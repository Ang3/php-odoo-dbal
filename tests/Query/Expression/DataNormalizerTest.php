<?php

namespace Ang3\Component\Odoo\DBAL\Tests\Query\Expression;

use Ang3\Component\Odoo\DBAL\Query\Expression\DataNormalizer;
use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\Comparison;
use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\CompositeDomain;
use Ang3\Component\Odoo\DBAL\Query\Expression\Domain\DomainInterface;
use Ang3\Component\Odoo\DBAL\Query\Expression\Operation\CollectionOperation;
use Ang3\Component\Odoo\DBAL\Schema\Enum\DateTimeFormat;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Query\Expression\DataNormalizer
 *
 * @internal
 */
class DataNormalizerTest extends TestCase
{
    private DataNormalizer $dataNormalizer;
    private \DateTime $dateNewYork;
    private \DateTime $dateUtc;
    private MockObject $domain;
    private MockObject $compositeDomain;
    private MockObject $collectionOperation;
    private mixed $arrayResult = ['foo' => 'bar'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->dataNormalizer = new DataNormalizer();
        $this->dateNewYork = new \DateTime('now', new \DateTimeZone('America/New_York'));
        $this->dateUtc = (clone $this->dateNewYork)->setTimezone(new \DateTimeZone('UTC'));
        $this->domain = $this->createMock(DomainInterface::class);
        $this->compositeDomain = $this->createMock(CompositeDomain::class);
        $this->collectionOperation = $this->createMock(CollectionOperation::class);
    }

    /**
     * @covers ::normalizeValue
     */
    public function testNormalizeRecursiveValue(): void
    {
        $value = [
            'bool1' => true,
            'bool2' => false,
            'int' => 3,
            'float' => 3.1,
            'string' => '3.1',
            'date' => $this->dateNewYork,
            'domain' => $this->domain,
            'composite_domain' => $this->compositeDomain,
            'collection_operation' => $this->collectionOperation,
        ];

        $value['recursion'] = $value;
        $this->domain->expects($this->exactly(2))->method('toArray')->willReturn($this->arrayResult);
        $this->compositeDomain->expects($this->exactly(2))->method('toArray')->willReturn($this->arrayResult);
        $this->collectionOperation->expects($this->exactly(2))->method('toArray')->willReturn($this->arrayResult);

        $result = $this->dataNormalizer->normalizeValue($value);

        self::assertIsArray($result);

        // 1st level
        self::assertEquals($value['bool1'], $result['bool1'] ?? null);
        self::assertEquals($value['bool2'], $result['bool2'] ?? null);
        self::assertEquals($value['int'], $result['int'] ?? null);
        self::assertEquals($value['float'], $result['float'] ?? null);
        self::assertEquals($value['string'], $result['string'] ?? null);
        self::assertEquals($this->dateUtc->format(DateTimeFormat::Long->value), $result['date'] ?? null);
        self::assertEquals($this->arrayResult, $result['domain'] ?? null);
        self::assertEquals($this->arrayResult, $result['composite_domain'] ?? null);
        self::assertEquals($this->arrayResult, $result['collection_operation'] ?? null);

        // Recursion
        self::assertEquals($value['bool1'], $result['recursion']['bool1'] ?? null);
        self::assertEquals($value['bool2'], $result['recursion']['bool2'] ?? null);
        self::assertEquals($value['int'], $result['recursion']['int'] ?? null);
        self::assertEquals($value['float'], $result['recursion']['float'] ?? null);
        self::assertEquals($value['string'], $result['recursion']['string'] ?? null);
        self::assertEquals($this->dateUtc->format(DateTimeFormat::Long->value), $result['recursion']['date'] ?? null);
        self::assertEquals($this->arrayResult, $result['recursion']['domain'] ?? null);
        self::assertEquals($this->arrayResult, $result['recursion']['composite_domain'] ?? null);
        self::assertEquals($this->arrayResult, $result['recursion']['collection_operation'] ?? null);
    }

    /**
     * @covers ::normalizeValue
     * @testWith [true, true]
     *           [false, false]
     *           [3, 3]
     *           [3.1, 3.1]
     *           ["3.1", "3.1"]
     */
    public function testNormalizeScalarValue(bool|int|float|string $value, mixed $expectedResult): void
    {
        self::assertEquals($expectedResult, $this->dataNormalizer->normalizeValue($value));
    }

    /**
     * @covers ::normalizeValue
     */
    public function testNormalizeDateValue(): void
    {
        self::assertEquals($this->dateUtc->format(DateTimeFormat::Long->value), $this->dataNormalizer->normalizeValue($this->dateNewYork));
    }

    /**
     * @covers ::normalizeValue
     */
    public function testNormalizeDomain(): void
    {
        $this->domain->expects($this->once())->method('toArray')->willReturn($this->arrayResult);
        self::assertEquals($this->arrayResult, $this->dataNormalizer->normalizeValue($this->domain));
    }

    /**
     * @covers ::normalizeValue
     */
    public function testNormalizeCompositeDomain(): void
    {
        $this->compositeDomain->expects($this->once())->method('toArray')->willReturn($this->arrayResult);
        self::assertEquals($this->arrayResult, $this->dataNormalizer->normalizeValue($this->compositeDomain));
    }

    /**
     * @covers ::normalizeValue
     */
    public function testNormalizeCollectionOperation(): void
    {
        $this->collectionOperation->expects($this->once())->method('toArray')->willReturn($this->arrayResult);
        self::assertEquals($this->arrayResult, $this->dataNormalizer->normalizeValue($this->collectionOperation));
    }

    /**
     * @covers ::normalizeValue
     */
    public function testNormalizeStringableObject(): void
    {
        $object = new class { public function __toString(): string { return 'test'; } };

        self::assertEquals('test', $this->dataNormalizer->normalizeValue($object));
    }
}