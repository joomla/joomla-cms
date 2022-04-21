<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\MVC\Model;

use Exception;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\MVC\Model\BaseDatabaseModel
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 * @since       __DEPLOY_VERSION__
 */
class DatabaseModelTest extends UnitTestCase
{
	/**
	 * @testdox  Test that the BaseDatabaseModel contains the right db and MVC factory
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testInjectedDatabaseAndMVCFactory()
	{
		$db         = $this->createStub(DatabaseInterface::class);
		$mvcFactory = $this->createStub(MVCFactoryInterface::class);

		$model = new class(['dbo' => $db], $mvcFactory) extends BaseDatabaseModel
		{
			public function getDatabase(): DatabaseInterface
			{
				return parent::getDatabase();
			}

			public function getMVCFactory(): MVCFactoryInterface
			{
				return parent::getMVCFactory();
			}
		};

		$this->assertEquals($db, $model->getDatabase());
		$this->assertEquals($mvcFactory, $model->getMVCFactory());
	}

	/**
	 * @testdox  Test that the BaseDatabaseModel returns the right table
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetTable()
	{
		$table      = $this->createStub(Table::class);
		$mvcFactory = $this->createStub(MVCFactoryInterface::class);
		$mvcFactory->method('createTable')->willReturn($table);

		$model = new class(['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends BaseDatabaseModel
		{};

		$this->assertEquals($table, $model->getTable());
	}

	/**
	 * @testdox  Test that the BaseDatabaseModel throws an exception when no table can be created
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetTableWhenNull()
	{
		$mvcFactory = $this->createStub(MVCFactoryInterface::class);
		$mvcFactory->method('createTable')->willReturn(null);

		$model = new class(['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends BaseDatabaseModel
		{};

		$this->expectException(Exception::class);
		$model->getTable();
	}

	/**
	 * @testdox  Test that the BaseDatabaseModel returns the right list when the query is an object
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetListFromObject()
	{
		$db = $this->createStub(DatabaseInterface::class);
		$db->method('loadObjectList')->willReturn([1]);

		$model = new class(['dbo' => $db], $this->createStub(MVCFactoryInterface::class)) extends BaseDatabaseModel
		{
			public function _getList($query, $limitstart = 0, $limit = 0)
			{
				return parent::_getList($query, $limitstart, $limit);
			}
		};

		$this->assertEquals([1], $model->_getList($this->getQueryStub($db), 0, 1));
	}

	/**
	 * @testdox  Test that the BaseDatabaseModel returns the right list when the query is a string
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetListFromString()
	{
		$db = $this->createStub(DatabaseInterface::class);
		$db->method('loadObjectList')->willReturn([1]);
		$db->method('getQuery')->willReturn($this->getQueryStub($db));

		$model = new class(['dbo' => $db], $this->createStub(MVCFactoryInterface::class)) extends BaseDatabaseModel
		{
			public function _getList($query, $limitstart = 0, $limit = 0)
			{
				return parent::_getList($query, $limitstart, $limit);
			}
		};

		$this->assertEquals([1], $model->_getList('query', 0, 1));
	}

	/**
	 * @testdox  Test that the BaseDatabaseModel returns the right list count from a query object
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetListCountFromObject()
	{
		$db = $this->createStub(DatabaseInterface::class);
		$db->method('getNumRows')->willReturn(5);

		$model = new class(['dbo' => $db], $this->createStub(MVCFactoryInterface::class)) extends BaseDatabaseModel
		{
			public function _getListCount($query)
			{
				return parent::_getListCount($query);
			}
		};

		$this->assertEquals(5, $model->_getListCount($this->getQueryStub($db)));
	}

	/**
	 * @testdox  Test that the BaseDatabaseModel returns the right list count from a query object
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetListCountFromObjectTypeSelect()
	{
		$db = $this->createStub(DatabaseInterface::class);
		$db->method('loadResult')->willReturn(5);

		$model = new class(['dbo' => $db], $this->createStub(MVCFactoryInterface::class)) extends BaseDatabaseModel
		{
			public function _getListCount($query)
			{
				return parent::_getListCount($query);
			}
		};

		$query = $this->getQueryStub($db);
		$query->select('*');

		$this->assertEquals(5, $model->_getListCount($query));
	}

	/**
	 * @testdox  Test that the BaseDatabaseModel returns the right list count from a query string
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetListCountFromString()
	{
		$db = $this->createStub(DatabaseInterface::class);
		$db->method('getNumRows')->willReturn(5);

		$model = new class(['dbo' => $db], $this->createStub(MVCFactoryInterface::class)) extends BaseDatabaseModel
		{
			public function _getListCount($query)
			{
				return parent::_getListCount($query);
			}
		};

		$this->assertEquals(5, $model->_getListCount('query'));
	}
}
