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
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for \Joomla\CMS\MVC\View\AbstractView
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 *
 * @since       4.2.0
 */
#[TestDox('The AbstractView')]
class AbstractViewTest extends UnitTestCase
{
    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('gets the injected name')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('compiles its own name')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('has the injected option')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can set a model and get it by name')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can set a default model and get it with no name')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can get data')]
    public function testGetData()
    {
        $view = new class () extends AbstractView {
            public function display($tpl = null)
            {
            }
        };
        $view->set('unit', 'test');

        $this->assertEquals('test', $view->get('unit', ''));
    }

    /**
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can get data')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can get data from model')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can get data from default model')]
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
     * @return  void
     *
     * @since   4.2.0
     */
    #[TestDox('can dispatch an event')]
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
