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
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\MVC\Model\FormModel
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 *
 * @testdox     The FormModel
 *
 * @since       4.2.0
 */
class FormModelTest extends UnitTestCase
{
    /**
     * @testdox  can checkin a record
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSuccessfulCheckin()
    {
        $table              = $this->createStub(Table::class);
        $table->checked_out = 0;
        $table->method('load')->willReturn(true);
        $table->method('hasField')->willReturn(true);
        $table->method('checkIn')->willReturn(true);
        $table->method('getColumnAlias')->willReturn('checked_out');

        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn($table);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends FormModel {
            public function getForm($data = [], $loadData = true)
            {
                return null;
            }
        };
        $model->setCurrentUser(new User());

        $this->assertTrue($model->checkin(1));
    }

    /**
     * @testdox  can checkin a record when the id is 0
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSuccessfulCheckinWithEmptyRecord()
    {
        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $this->createStub(MVCFactoryInterface::class)) extends FormModel {
            public function getForm($data = [], $loadData = true)
            {
                return null;
            }
        };

        $this->assertTrue($model->checkin(0));
    }

    /**
     * @testdox  can't checkin a record
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testFailedCheckin()
    {
        $table              = $this->createStub(Table::class);
        $table->checked_out = 0;
        $table->method('load')->willReturn(true);
        $table->method('hasField')->willReturn(true);
        $table->method('checkIn')->willReturn(false);
        $table->method('getColumnAlias')->willReturn('checked_out');

        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn($table);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends FormModel {
            public function getForm($data = [], $loadData = true)
            {
                return null;
            }
        };
        $model->setCurrentUser(new User());

        $this->assertFalse($model->checkin(1));
    }

    /**
     * @testdox  can't checkin a record when load of the table fails
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testFailedCheckinLoad()
    {
        $table              = $this->createStub(Table::class);
        $table->checked_out = 0;
        $table->method('load')->willReturn(false);

        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn($table);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends FormModel {
            public function getForm($data = [], $loadData = true)
            {
                return null;
            }
        };
        $model->setCurrentUser(new User());

        $this->assertFalse($model->checkin(1));
    }

    /**
     * @testdox  can checkin a record when the table has not the required fields
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSuccessfulCheckinFieldNotAvailableCheck()
    {
        $table              = $this->createStub(Table::class);
        $table->checked_out = 0;
        $table->method('load')->willReturn(true);
        $table->method('hasField')->willReturn(false);

        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn($table);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends FormModel {
            public function getForm($data = [], $loadData = true)
            {
                return null;
            }
        };
        $model->setCurrentUser(new User());

        $this->assertTrue($model->checkin(1));
    }

    /**
     * @testdox  can't checkin a record when is checked out as different user and current user is not admin
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSuccessfulCheckinWhenCurrentUserIsNotAdmin()
    {
        $table              = $this->createStub(Table::class);
        $table->checked_out = 1;
        $table->method('load')->willReturn(true);
        $table->method('hasField')->willReturn(true);
        $table->method('getColumnAlias')->willReturn('checked_out');

        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn($table);

        $user     = $this->createStub(User::class);
        $user->id = 2;
        $user->method('authorise')->willReturn(false);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends FormModel {
            public function getForm($data = [], $loadData = true)
            {
                return null;
            }
        };
        $model->setCurrentUser($user);

        $this->assertFalse($model->checkin(1));
    }

    /**
     * @testdox  can checkin a record when is checked out as different user and current user is admin
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSuccessfulCheckinWhenCurrentUserAdmin()
    {
        $table              = $this->createStub(Table::class);
        $table->checked_out = 1;
        $table->method('load')->willReturn(true);
        $table->method('hasField')->willReturn(true);
        $table->method('checkIn')->willReturn(true);
        $table->method('getColumnAlias')->willReturn('checked_out');

        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn($table);

        $user     = $this->createStub(User::class);
        $user->id = 2;
        $user->method('authorise')->willReturn(true);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends FormModel {
            public function getForm($data = [], $loadData = true)
            {
                return null;
            }
        };
        $model->setCurrentUser($user);

        $this->assertTrue($model->checkin(1));
    }

    /**
     * @testdox  can checkout a record
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSuccessfulCheckout()
    {
        $table              = $this->createStub(Table::class);
        $table->checked_out = 0;
        $table->method('load')->willReturn(true);
        $table->method('hasField')->willReturn(true);
        $table->method('checkOut')->willReturn(true);
        $table->method('getColumnAlias')->willReturn('checked_out');

        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn($table);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends FormModel {
            public function getForm($data = [], $loadData = true)
            {
                return null;
            }
        };

        // Must be a valid user
        $user     = new User();
        $user->id = 1;
        $model->setCurrentUser($user);

        $this->assertTrue($model->checkout(1));
    }

    /**
     * @testdox  can checkout a record when the id is 0
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSuccessfulCheckoutWithEmptyRecord()
    {
        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $this->createStub(MVCFactoryInterface::class)) extends FormModel {
            public function getForm($data = [], $loadData = true)
            {
                return null;
            }
        };

        $this->assertTrue($model->checkout(0));
    }

    /**
     * @testdox  can't checkout a record
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testFailedCheckout()
    {
        $table              = $this->createStub(Table::class);
        $table->checked_out = 0;
        $table->method('load')->willReturn(true);
        $table->method('hasField')->willReturn(true);
        $table->method('checkIn')->willReturn(false);
        $table->method('getColumnAlias')->willReturn('checked_out');

        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn($table);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends FormModel {
            public function getForm($data = [], $loadData = true)
            {
                return null;
            }
        };

        // Must be a valid user
        $user     = new User();
        $user->id = 1;
        $model->setCurrentUser($user);

        $this->assertFalse($model->checkout(1));
    }

    /**
     * @testdox  can't checkout a record when the current user is a guest
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testFailedCheckoutAsGuest()
    {
        $table              = $this->createStub(Table::class);
        $table->checked_out = 0;
        $table->method('load')->willReturn(true);
        $table->method('hasField')->willReturn(true);
        $table->method('checkIn')->willReturn(false);
        $table->method('getColumnAlias')->willReturn('checked_out');

        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn($table);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends FormModel {
            public function getForm($data = [], $loadData = true)
            {
                return null;
            }
        };
        $model->setCurrentUser(new User());

        $this->assertFalse($model->checkout(1));
    }

    /**
     * @testdox  can't checkout a record when load of the table fails
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testFailedCheckoutLoad()
    {
        $table              = $this->createStub(Table::class);
        $table->checked_out = 0;
        $table->method('load')->willReturn(false);

        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn($table);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends FormModel {
            public function getForm($data = [], $loadData = true)
            {
                return null;
            }
        };

        $this->assertFalse($model->checkout(1));
    }

    /**
     * @testdox  can checkout a record when the table has not the required fields
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSuccessfulCheckoutFieldNotAvailableCheck()
    {
        $table              = $this->createStub(Table::class);
        $table->checked_out = 0;
        $table->method('load')->willReturn(true);
        $table->method('hasField')->willReturn(false);

        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn($table);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends FormModel {
            public function getForm($data = [], $loadData = true)
            {
                return null;
            }
        };

        $this->assertTrue($model->checkout(1));
    }

    /**
     * @testdox  can't checkout a record when is checked out as different user
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSuccessfulCheckoutWhenCurrentUserIsDifferent()
    {
        $table              = $this->createStub(Table::class);
        $table->checked_out = 1;
        $table->method('load')->willReturn(true);
        $table->method('hasField')->willReturn(true);
        $table->method('getColumnAlias')->willReturn('checked_out');

        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createTable')->willReturn($table);

        $model = new class (['dbo' => $this->createStub(DatabaseInterface::class)], $mvcFactory) extends FormModel {
            public function getForm($data = [], $loadData = true)
            {
                return null;
            }
        };

        $user     = new User();
        $user->id = 2;
        $model->setCurrentUser($user);

        $this->assertFalse($model->checkout(1));
    }
}
