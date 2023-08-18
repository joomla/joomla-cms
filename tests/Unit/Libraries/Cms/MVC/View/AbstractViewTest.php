<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\MVC\View;

use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\MVC\View\AbstractView;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\EventInterface;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\MVC\View\AbstractView
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 *
 * @testdox     The AbstractView
 *
 * @since       4.2.0
 */
class AbstractViewTest extends UnitTestCase
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
        $view = new class (['name' => 'unit test']) extends AbstractView {
            public function display($tpl = null)
            {
            }
        };

        $this->assertEquals('unit test', $view->getName());
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
        $view = new class () extends AbstractView {
            public function display($tpl = null)
            {
            }
        };

        $this->assertStringContainsStringIgnoringCase('view', $view->getName());
    }

    /**
     * @testdox  has the injected option
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testInjectedOption()
    {
        $view = new class (['option' => 'unit test']) extends AbstractView {
            public function getOption()
            {
                return $this->option;
            }

            public function display($tpl = null)
            {
            }
        };

        $this->assertEquals('unit test', $view->getOption());
    }

    /**
     * @testdox  can set a model and get it by name
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSetGetModel()
    {
        $model = new class (['name' => 'unit test']) extends BaseModel {
        };

        $view = new class () extends AbstractView {
            public function display($tpl = null)
            {
            }
        };
        $view->setModel($model, false);

        $this->assertEquals($model, $view->getModel('unit test'));
    }

    /**
     * @testdox  can set a default model and get it with no name
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testSetGetDefaultModel()
    {
        $model = new class (['name' => 'unit']) extends BaseModel {
        };

        $view = new class () extends AbstractView {
            public function display($tpl = null)
            {
            }
        };
        $view->setModel($model, true);

        $this->assertEquals($model, $view->getModel());
    }

    /**
     * @testdox  can get data
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testGetData()
    {
        $view = new class () extends AbstractView {
            public function display($tpl = null)
            {
            }
        };
        $view->set('unit', 'test');

        $this->assertEquals('test', $view->get('unit'));
    }

    /**
     * @testdox  can get data
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testGetDefaultData()
    {
        $view = new class () extends AbstractView {
            public function display($tpl = null)
            {
            }
        };

        $this->assertEquals('test', $view->get('unit', 'test'));
    }

    /**
     * @testdox  can get data from model
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testGetDataFromModel()
    {
        $model = new class (['name' => 'test']) extends BaseModel {
            public function getUnit()
            {
                return 'test';
            }
        };

        $view = new class () extends AbstractView {
            public function display($tpl = null)
            {
            }
        };
        $view->setModel($model, false);

        $this->assertEquals('test', $view->get('unit', 'test'));
    }

    /**
     * @testdox  can get data from default model
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testGetDataFromDefaultModel()
    {
        $model = new class (['name' => 'test']) extends BaseModel {
            public function getUnit()
            {
                return 'test';
            }
        };

        $view = new class () extends AbstractView {
            public function display($tpl = null)
            {
            }
        };
        $view->setModel($model, true);

        $this->assertEquals('test', $view->get('unit'));
    }

    /**
     * @testdox  can dispatch an event
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function testDispatchEvent()
    {
        $event      = new Event('test');
        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())->method('dispatch')->with($this->equalTo('test'), $this->equalTo($event));

        $view = new class () extends AbstractView {
            public function dispatchEvent(EventInterface $event)
            {
                parent::dispatchEvent($event);
            }

            public function display($tpl = null)
            {
            }
        };
        $view->setDispatcher($dispatcher);
        $view->dispatchEvent($event);
    }
}
