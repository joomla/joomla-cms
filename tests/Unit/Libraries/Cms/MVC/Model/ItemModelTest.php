<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\MVC\Model;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\MVC\Model\ItemModel
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 * @since       __DEPLOY_VERSION__
 */
class ItemModelTest extends UnitTestCase
{
	/**
	 * @testdox  The ItemModel store id is not empty
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetStoreId()
	{
		$model = new class(['dbo' => $this->createStub(DatabaseInterface::class)], $this->createStub(MVCFactoryInterface::class)) extends ItemModel
		{
			public function getStoreId($id = '')
			{
				return parent::getStoreId($id);
			}

			public function getItem($pk = null)
			{}
		};

		$this->assertNotEmpty($model->getStoreId(1));
	}

	/**
	 * @testdox  The ItemModel store id is not empty on an empty id
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetEmptyStoreId()
	{
		$model = new class(['dbo' => $this->createStub(DatabaseInterface::class)], $this->createStub(MVCFactoryInterface::class)) extends ItemModel
		{
			public function getStoreId($id = '')
			{
				return parent::getStoreId($id);
			}

			public function getItem($pk = null)
			{}
		};

		$this->assertNotEmpty($model->getStoreId());
	}

	/**
	 * @testdox  The ItemModel store id is different with different ids
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testStoreIdWithDifferentIds()
	{
		$model = new class(['dbo' => $this->createStub(DatabaseInterface::class)], $this->createStub(MVCFactoryInterface::class)) extends ItemModel
		{
			public function getStoreId($id = '')
			{
				return parent::getStoreId($id);
			}

			public function getItem($pk = null)
			{}
		};

		$this->assertNotEquals($model->getStoreId(1), $model->getStoreId(2));
	}

	/**
	 * @testdox  The ItemModel store id is the same with same ids
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testStoreIdWithSameIds()
	{
		$model = new class(['dbo' => $this->createStub(DatabaseInterface::class)], $this->createStub(MVCFactoryInterface::class)) extends ItemModel
		{
			public function getStoreId($id = '')
			{
				return parent::getStoreId($id);
			}

			public function getItem($pk = null)
			{}
		};

		$this->assertSame($model->getStoreId(1), $model->getStoreId(1));
	}
}
