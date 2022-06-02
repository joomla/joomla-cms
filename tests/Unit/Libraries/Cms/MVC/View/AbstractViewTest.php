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
 * @since       __DEPLOY_VERSION__
 */
class AbstractViewTest extends UnitTestCase
{
	/**
	 * @testdox  The AbstractView gets the injected name
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetInjectedName()
	{
		$view = new class(['name' => 'unit test']) extends AbstractView
		{
			public function display($tpl = null)
			{}
		};

		$this->assertEquals('unit test', $view->getName());
	}

	/**
	 * @testdox  The AbstractView compiles its own name
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetCompiledName()
	{
		$view = new class extends AbstractView
		{
			public function display($tpl = null)
			{}
		};

		$this->assertStringContainsStringIgnoringCase('view', $view->getName());
	}

	/**
	 * @testdox  The AbstractView has the injected option
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testInjectedOption()
	{
		$view = new class(['option' => 'unit test']) extends AbstractView
		{
			public function getOption()
			{
				return $this->option;
			}

			public function display($tpl = null)
			{}
		};

		$this->assertEquals('unit test', $view->getOption());
	}

	/**
	 * @testdox  The AbstractView can set a model and get it by name
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testSetGetModel()
	{
		$model = new class(['name' => 'unit test']) extends BaseModel
		{};

		$view = new class extends AbstractView
		{
			public function display($tpl = null)
			{}
		};
		$view->setModel($model, false);

		$this->assertEquals($model, $view->getModel('unit test'));
	}

	/**
	 * @testdox  The AbstractView can set a default model and get it with no name
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testSetGetDefaultModel()
	{
		$model = new class(['name' => 'unit']) extends BaseModel
		{};

		$view = new class extends AbstractView
		{
			public function display($tpl = null)
			{}
		};
		$view->setModel($model, true);

		$this->assertEquals($model, $view->getModel());
	}

	/**
	 * @testdox  The AbstractView can get data
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetData()
	{
		$view = new class extends AbstractView
		{
			public function display($tpl = null)
			{}
		};
		$view->set('unit', 'test');

		$this->assertEquals('test', $view->get('unit'));
	}

	/**
	 * @testdox  The AbstractView can get data
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetDefaultData()
	{
		$view = new class extends AbstractView
		{
			public function display($tpl = null)
			{}
		};

		$this->assertEquals('test', $view->get('unit', 'test'));
	}

	/**
	 * @testdox  The AbstractView can get data from model
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetDataFromModel()
	{
		$model = new class(['name' => 'test']) extends BaseModel
		{
			public function getUnit()
			{
				return 'test';
			}
		};

		$view = new class extends AbstractView
		{
			public function display($tpl = null)
			{}
		};
		$view->setModel($model, false);

		$this->assertEquals('test', $view->get('unit', 'test'));
	}

	/**
	 * @testdox  The AbstractView can get data from default model
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetDataFromDefaultModel()
	{
		$model = new class(['name' => 'test']) extends BaseModel
		{
			public function getUnit()
			{
				return 'test';
			}
		};

		$view = new class extends AbstractView
		{
			public function display($tpl = null)
			{}
		};
		$view->setModel($model, true);

		$this->assertEquals('test', $view->get('unit'));
	}

	/**
	 * @testdox  The AbstractView can dispatch an event
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testDispatchEvent()
	{
		$event      = new Event('test');
		$dispatcher = $this->createMock(DispatcherInterface::class);
		$dispatcher->expects($this->once())->method('dispatch')->with($this->equalTo('test'), $this->equalTo($event));

		$view = new class extends AbstractView
		{
			public function dispatchEvent(EventInterface $event)
			{
				parent::dispatchEvent($event);
			}

			public function display($tpl = null)
			{}
		};
		$view->setDispatcher($dispatcher);
		$view->dispatchEvent($event);
	}
}
