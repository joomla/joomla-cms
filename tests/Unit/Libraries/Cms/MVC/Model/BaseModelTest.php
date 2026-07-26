<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\MVC\Model;

use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for \Joomla\CMS\MVC\Model\BaseModel
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 *
 * @since       4.2.0
 */
#[TestDox('The BaseModel')]
class BaseModelTest extends UnitTestCase
{
    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('gets the injected name')]
    public function testGetInjectedName()
    {
        $model = new class (['name' => 'unit test']) extends BaseModel {
        };

        $this->assertEquals('unit test', $model->getName());
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('compiles its own name')]
    public function testGetCompiledName()
    {
        $model = new class () extends BaseModel {
        };

        $this->assertStringContainsStringIgnoringCase('basetest', $model->getName());
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('gets the injected state')]
    public function testGetInjectedState()
    {
        $state = ['test' => 'unit'];
        $model = new class (['state' => $state]) extends BaseModel {
        };

        $this->assertEquals($state, $model->getState());
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('does populate the state before accessing it')]
    public function testAcceptRequest()
    {
        $model = new class (['ignore_request' => false]) extends BaseModel {
            protected function populateState()
            {
                $this->setState('state.set', true);
            }
        };

        $this->assertTrue($model->getState('state.set', false));
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('does ignore to populate the state before accessing it')]
    public function testIgnoreRequest()
    {
        $model = new class (['ignore_request' => true]) extends BaseModel {
            protected function populateState()
            {
                $this->setState('state.set', true);
            }
        };

        $this->assertFalse($model->getState('state.set', false));
    }
}
