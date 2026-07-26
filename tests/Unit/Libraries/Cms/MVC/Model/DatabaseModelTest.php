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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for \Joomla\CMS\MVC\Model\BaseDatabaseModel
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 *
 * @since       4.2.0
 */
#[TestDox('The BaseDatabaseModel')]
class DatabaseModelTest extends UnitTestCase
{
    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('contains the right db and MVC factory')]
    public function testInjectedDatabaseAndMVCFactory()
    {
        $db         = $this->createStub(DatabaseInterface::class);
        $mvcFactory = $this->createStub(MVCFactoryInterface::class);

        $model = new class (['dbo' => $db], $mvcFactory) extends BaseDatabaseModel {
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('returns the right table')]
    public function testGetTable()
    {
        $table      = $this->createStub(Table::class);
        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn($table);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends BaseDatabaseModel {
        };

        $this->assertEquals($table, $model->getTable());
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('throws an exception when no table can be created')]
    public function testGetTableWhenNull()
    {
        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn(null);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends BaseDatabaseModel {
        };

        $this->expectException(\Exception::class);
        $model->getTable();
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('returns the right list when the query is an object')]
    public function testGetListFromObject()
    {
        $db = $this->createStub(DatabaseInterface::class);
        $db->method('loadObjectList')->willReturn([1]);

        $model = new class (['dbo' => $db], $this->createStub(MVCFactoryInterface::class)) extends BaseDatabaseModel {
            public function _getList($query, $limitstart = 0, $limit = 0)
            {
                return parent::_getList($query, $limitstart, $limit);
            }
        };

        $this->assertEquals([1], $model->_getList($this->getQueryStub($db), 0, 1));
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('returns the right list when the query is a string')]
    public function testGetListFromString()
    {
        $db = $this->createStub(DatabaseInterface::class);
        $db->method('loadObjectList')->willReturn([1]);
        $db->method('createQuery')->willReturn($this->getQueryStub($db));

        $model = new class (['dbo' => $db], $this->createStub(MVCFactoryInterface::class)) extends BaseDatabaseModel {
            public function _getList($query, $limitstart = 0, $limit = 0)
            {
                return parent::_getList($query, $limitstart, $limit);
            }
        };

        $this->assertEquals([1], $model->_getList('query', 0, 1));
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('returns the right list count from a query object')]
    public function testGetListCountFromObject()
    {
        $db = $this->createStub(DatabaseInterface::class);
        $db->method('getNumRows')->willReturn(5);

        $model = new class (['dbo' => $db], $this->createStub(MVCFactoryInterface::class)) extends BaseDatabaseModel {
            public function _getListCount($query)
            {
                return parent::_getListCount($query);
            }
        };

        $this->assertEquals(5, $model->_getListCount($this->getQueryStub($db)));
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('returns the right list count from a query object')]
    public function testGetListCountFromObjectTypeSelect()
    {
        $db = $this->createStub(DatabaseInterface::class);
        $db->method('loadResult')->willReturn(5);

        $model = new class (['dbo' => $db], $this->createStub(MVCFactoryInterface::class)) extends BaseDatabaseModel {
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('returns the right list count from a query string')]
    public function testGetListCountFromString()
    {
        $db = $this->createStub(DatabaseInterface::class);
        $db->method('getNumRows')->willReturn(5);

        $model = new class (['dbo' => $db], $this->createStub(MVCFactoryInterface::class)) extends BaseDatabaseModel {
            public function _getListCount($query)
            {
                return parent::_getListCount($query);
            }
        };

        $this->assertEquals(5, $model->_getListCount('query'));
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox("can't determine the checked out state of an item that has not the required field")]
    public function testCheckedOutWithoutField()
    {
        $table = $this->createStub(Table::class);
        $table->method('getColumnAlias')->willReturn('checked_out');

        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn($table);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends BaseDatabaseModel {
        };

        $this->assertFalse($model->isCheckedOut(new \stdClass()));
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can determine the checked out state of an item with the same user')]
    public function testCheckedOutWithCheckedOutUser()
    {
        $table = $this->createStub(Table::class);
        $table->method('getColumnAlias')->willReturn('checked_out');

        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn($table);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends BaseDatabaseModel {
        };

        $user     = new User();
        $user->id = 1;
        $model->setCurrentUser($user);

        $this->assertFalse($model->isCheckedOut((object)['checked_out' => 1]));
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can determine the checked out state of an item with a different user')]
    public function testCheckedOutWithNotCheckedOutUser()
    {
        $table = $this->createStub(Table::class);
        $table->method('getColumnAlias')->willReturn('checked_out');

        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn($table);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends BaseDatabaseModel {
        };

        $user     = new User();
        $user->id = 2;
        $model->setCurrentUser($user);

        $this->assertTrue($model->isCheckedOut((object)['checked_out' => 1]));
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can determine the checked out state of an item when the user is not set')]
    public function testCheckedOutWitFieldEmptyUserSet()
    {
        $table = $this->createStub(Table::class);
        $table->method('getColumnAlias')->willReturn('checked_out');

        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn($table);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends BaseDatabaseModel {
        };
        $model->setCurrentUser(new User());

        $this->assertTrue($model->isCheckedOut((object)['checked_out' => 1]));
    }
}
