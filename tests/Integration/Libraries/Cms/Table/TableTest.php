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

	public function getSchemasToLoad(): array
	{
		return ['framework.sql', 'testtable.sql'];
	}

	public function testObjectHasAttributesFromTable()
	{
		$fields = ['id', 'title', 'asset_id', 'hits', 'checked_out', 'checked_out_time', 'published', 'publish_up', 'publish_down', 'ordering', 'params'];
		$this->assertEquals(
			$fields,
			array_keys($this->object->getFields())
		);
	}
}
