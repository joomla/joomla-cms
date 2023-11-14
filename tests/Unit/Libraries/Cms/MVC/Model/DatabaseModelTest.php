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
use Joomla\CMS\MVC\Model\DatabaseAwareTrait;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\MVC\Model\BaseDatabaseModel
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 *
 * @testdox     The BaseDatabaseModel
 *
 * @since       4.2.0
 */
class DatabaseModelTest extends UnitTestCase
{
    /**
     * @testdox  contains the right db and MVC factory
     *
     * @return  void
     *
     * @since   4.2.0
     */
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
     * @testdox  returns the right table
     *
     * @return  void
     *
     * @since   4.2.0
     */
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
     * @testdox  throws an exception when no table can be created
     *
     * @return  void
     *
     * @since   4.2.0
     */
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
     * @testdox  returns the right list when the query is an object
     *
     * @return  void
     *
     * @since   4.2.0
     */
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
     * @testdox  returns the right list when the query is a string
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testGetListFromString()
    {
        $db = $this->createStub(DatabaseInterface::class);
        $db->method('loadObjectList')->willReturn([1]);
        $db->method('getQuery')->willReturn($this->getQueryStub($db));

        $model = new class (['dbo' => $db], $this->createStub(MVCFactoryInterface::class)) extends BaseDatabaseModel {
            public function _getList($query, $limitstart = 0, $limit = 0)
            {
                return parent::_getList($query, $limitstart, $limit);
            }
        };

        $this->assertEquals([1], $model->_getList('query', 0, 1));
    }

    /**
     * @testdox  returns the right list count from a query object
     *
     * @return  void
     *
     * @since   4.2.0
     */
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
     * @testdox  returns the right list count from a query object
     *
     * @return  void
     *
     * @since   4.2.0
     */
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
     * @testdox  returns the right list count from a query string
     *
     * @return  void
     *
     * @since   4.2.0
     */
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
     * @testdox  can't determine the checked out state of an item that has not the required field
     *
     * @return  void
     *
     * @since   4.2.0
     */
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
     * @testdox  can determine the checked out state of an item with the same user
     *
     * @return  void
     *
     * @since   4.2.0
     */
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
     * @testdox  can determine the checked out state of an item with a different user
     *
     * @return  void
     *
     * @since   4.2.0
     */
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
     * @testdox  can determine the checked out state of an item when the user is not set
     *
     * @return  void
     *
     * @since   4.2.0
     */
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

    /**
     * @testdox  that all function calls go over deprecated getDbo function
     *
     * @return  void
     *
     * @since   4.2.1
     *
     * @deprecated  5.0 Must be removed when database calls are changed to getDatabase in libraries models
     */
    public function testOverrideOldDboFunction()
    {
        $db = $this->createMock(DatabaseInterface::class);
        $db->expects($this->never())->method('setQuery');

        $query = $this->getQueryStub($db);
        $newDb = $this->createMock(DatabaseInterface::class);
        $newDb->expects($this->once())->method('setQuery')->with($this->equalTo($query));

        $model = new class (['dbo' => $db], $this->createStub(MVCFactoryInterface::class), $newDb) extends BaseDatabaseModel {
            private $newDb;

            public function __construct(array $config, MVCFactoryInterface $factory, DatabaseInterface $newDb)
            {
                parent::__construct($config, $factory);

                $this->newDb = $newDb;
            }

            public function _getList($query, $limitstart = 0, $limit = 0)
            {
                return parent::_getList($query, $limitstart, $limit);
            }

            public function getDbo()
            {
                return $this->newDb;
            }
        };
        $model->_getList($query, 0, 1);
    }

    /**
     * @testdox  still can use the old trait
     *
     * @return  void
     *
     * @since   4.2.0
     *
     * @deprecated  5.0 Must be removed when trait gets deleted
     */
    public function testUseOldMVCTrait()
    {
        $db = $this->createStub(DatabaseInterface::class);

        $model = new class (['dbo' => $db], $this->createStub(MVCFactoryInterface::class)) extends BaseDatabaseModel {
            use DatabaseAwareTrait;
        };

        $this->assertEquals($db, $model->getDbo());
    }

    /**
     * @testdox  operates normally even when no variable is declared
     *
     * @return  void
     *
     * @since   4.2.0
     *
     * @deprecated  5.0 This has to be removed when we do not support the MVC Trait anymore
     */
    public function testNotDeclaredVariable()
    {
        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $this->createStub(MVCFactoryInterface::class)) extends BaseDatabaseModel {
            public function cache($key, $value)
            {
                if (!isset($this->test[$key])) {
                    $this->test[$key] = $value;
                }

                return $this->test[$key];
            }
        };

        $this->assertEquals('test', $model->cache(1, 'test'));
    }
}
