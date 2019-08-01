<?php
/**
 * @package     Joomla.IntegrationTest
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Tests\Unit\Libraries\Cms\Table;

use Joomla\Event\Dispatcher;
use Joomla\CMS\Table\Table;
use Joomla\Tests\Integration\DBTestInterface;
use Joomla\Tests\Integration\DBTestTrait;
use Joomla\Tests\Integration\IntegrationTestCase;
use Joomla\Tests\Integration\Libraries\Cms\Table\Stubs\TestTable;

/**
 * Test class for \Joomla\CMS\Table\Table.
 *
 * @package  Joomla.Platform
 *
 * @since    __DEPLOY_VERSION__
 */
class TableTest extends IntegrationTestCase implements DBTestInterface
{
	use DBTestTrait;

	/**
	 * @var    Table
	 * @since  __DEPLOY_VERSION__
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function setUp():void
	{
		parent::setUp();

		$dispatcher = new Dispatcher;
		$this->object = new TestTable($this->getDBDriver(), $dispatcher);
	}

	/**
	 *
	 * @return array
	 *
	 * @since   4.0.0
	 */
	public function getSchemasToLoad(): array
	{
		return ['framework.sql', 'testtable.sql'];
	}

	/**
	 *
	 * @return  void
	 * @since   4.0.0
	 */
	public function testObjectHasAttributesFromTable()
	{
		$fields = [
			'id',
			'title',
			'asset_id',
			'hits',
			'checked_out',
			'checked_out_time',
			'published',
			'publish_up',
			'publish_down',
			'ordering',
			'params'
		];

		$this->assertEquals(
			$fields,
			array_keys($this->object->getFields())
		);
	}

	public function testBindWorksWithArraysAndObjects()
	{
		$data = [
			'title' => 'Test Title',
			'hits' => 42,
			'published' => 1,
			'ordering' => 23
		];

		$this->object->bind($data);

		$this->assertEquals('Test Title', $this->object->title);
		$this->assertEquals(42, $this->object->hits);
		$this->assertEquals(1, $this->object->published);
		$this->assertEquals(23, $this->object->ordering);

		$this->object->reset();

		$this->object->bind((object) $data);

		$this->assertEquals('Test Title', $this->object->title);
		$this->assertEquals(42, $this->object->hits);
		$this->assertEquals(1, $this->object->published);
		$this->assertEquals(23, $this->object->ordering);
	}

	public function testBindOnlyBindsTableFields()
	{
		$data = [
			'title' => 'Test Title',
			'hits' => 42,
			'fakefield' => 'Not present!',
			'published' => 1,
			'ordering' => 23,
			'fakefield2' => 'Not present either!'
		];

		$this->object->bind($data);

		$this->assertEquals('Test Title', $this->object->title);
		$this->assertEquals(42, $this->object->hits);
		$this->assertEquals(1, $this->object->published);
		$this->assertEquals(23, $this->object->ordering);
		$this->assertNotTrue(isset($this->object->fakefield));
		$this->assertNotTrue(isset($this->object->fakefield2));
	}

	public function testBindIgnoresFields()
	{
		$data = [
			'title' => 'Test Title',
			'hits' => 42,
			'published' => 1,
			'ordering' => 23
		];
		$ignore = [
			'hits',
			'ordering'
		];

		// Check for ingore fields as array
		$this->object->bind($data, $ignore);

		$this->assertEquals('Test Title', $this->object->title);
		$this->assertEquals(null, $this->object->hits);
		$this->assertEquals(1, $this->object->published);
		$this->assertEquals(null, $this->object->ordering);

		// Check for ignore fields as string
		$this->object->bind($data, 'hits ordering');

		$this->assertEquals('Test Title', $this->object->title);
		$this->assertEquals(null, $this->object->hits);
		$this->assertEquals(1, $this->object->published);
		$this->assertEquals(null, $this->object->ordering);
	}

	public function testBindJSONEncodesFields()
	{
		$data = [
			'title' => 'Test Title',
			'hits' => 42,
			'published' => 1,
			'ordering' => 23,
			'params' => [
				'key' => 'value',
				'nested' => [
					'more' => 'values',
					'even' => 'more'
				],
				'object' => (object) [
					'attribute1' => 'value1',
					'attribute2' => 'value2'
				]
			]
		];

		$this->object->bind($data);

		$this->assertEquals(json_encode($data['params']), $this->object->params);
	}

	public function testBindRequiresArrayOrObject()
	{
		$this->expectException(\InvalidArgumentException::class);

		$this->object->bind(2);
	}
}
