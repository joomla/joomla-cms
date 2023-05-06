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

/**
 * Test class for \Joomla\CMS\MVC\Model\BaseModel
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 *
 * @testdox     The BaseModel
 *
 * @since       4.2.0
 */
class BaseModelTest extends UnitTestCase
{
    /**
     * @testdox  gets the injected name
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testGetInjectedName()
    {
        $model = new class (['name' => 'unit test']) extends BaseModel {
        };

        $this->assertEquals('unit test', $model->getName());
    }

    /**
     * @testdox  compiles its own name
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testGetCompiledName()
    {
        $model = new class () extends BaseModel {
        };

        $this->assertStringContainsStringIgnoringCase('basetest', $model->getName());
    }

    /**
     * @testdox  gets the injected state
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testGetInjectedState()
    {
        $state = ['test' => 'unit'];
        $model = new class (['state' => $state]) extends BaseModel {
        };

        $this->assertEquals($state, $model->getState());
    }

    /**
     * @testdox  does populate the state before accessing it
     *
     * @return  void
     *
     * @since   4.2.0
     */
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
     * @testdox  does ignore to populate the state before accessing it
     *
     * @return  void
     *
     * @since   4.2.0
     */
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
