<?php

namespace Query;

use Ang3\Component\Odoo\DBAL\Query\QueryBuilder;
use Ang3\Component\Odoo\DBAL\RecordManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Query\QueryBuilder
 */
class QueryBuilderTest extends TestCase
{
	private QueryBuilder $queryBuilder;
	private MockObject $recordManager;
	private string $modelName = 'model_name';

	protected function setUp(): void
	{
		parent::setUp();
		$this->recordManager = $this->createMock(RecordManager::class);
		$this->queryBuilder = new QueryBuilder($this->recordManager, $this->modelName);
	}

	/**
	 * @covers ::select
	 * @testWith [null, []]
	 *           ["", []]
	 *           [["", ""], []]
	 *           ["model_name", ["model_name"]]
	 *           [["model_name1", "model_name2"], ["model_name1", "model_name2"]]
	 *           [["model_name1", "model_name2", "model_name2"], ["model_name1", "model_name2"]]
	 */
	public function testSelect(array|string $fields = null, array $expectedResult = []): void
	{
		$this->queryBuilder->select($fields);

		self::assertEquals($expectedResult, $this->queryBuilder->getSelect());
	}
}