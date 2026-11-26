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
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for \Joomla\CMS\MVC\Model\FormModel
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 *
 * @since       4.2.0
 */
#[TestDox('The FormModel')]
class FormModelTest extends UnitTestCase
{
    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can checkin a record')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can checkin a record when the id is 0')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox("can't checkin a record")]
    public function testFailedCheckin()
    {
        $table              = $this->createStub(Table::class);
        $table->checked_out = 0;
        $table->method('load')->willReturn(true);
        $table->method('hasField')->willReturn(true);
        $table->method('checkIn')->willReturn(false);
        $table->method('getColumnAlias')->willReturn('checked_out');
        $table->method('getError')->willReturn('ERROR MESSAGE');

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

        $this->expectException(\Exception::class);
        $model->setUseExceptions(true);
        $model->checkin(1);
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox("can't checkin a record when load of the table fails")]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can checkin a record when the table has not the required fields')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox("can't checkin a record when is checked out as different user and current user is not admin")]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can checkin a record when is checked out as different user and current user is admin')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can checkout a record')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can checkout a record when the id is 0')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox("can't checkout a record")]
    public function testFailedCheckout()
    {
        $table              = $this->createStub(Table::class);
        $table->checked_out = 0;
        $table->method('load')->willReturn(true);
        $table->method('hasField')->willReturn(true);
        $table->method('checkIn')->willReturn(false);
        $table->method('getColumnAlias')->willReturn('checked_out');
        $table->method('getError')->willReturn('ERROR MESSAGE');

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

        $this->expectException(\Exception::class);
        $model->setUseExceptions(true);
        $model->checkout(1);
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox("can't checkout a record when the current user is a guest")]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox("can't checkout a record when load of the table fails")]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can checkout a record when the table has not the required fields')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox("can't checkout a record when is checked out as different user")]
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
