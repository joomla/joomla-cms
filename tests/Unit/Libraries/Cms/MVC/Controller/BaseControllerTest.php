<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\MVC\Controller;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Document\Document;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\LegacyFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\View\AbstractView;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Input\Input;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\MVC\Controller\BaseDatabaseModel
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 * @since       __DEPLOY_VERSION__
 */
class BaseControllerTest extends UnitTestCase
{
	/**
	 * @testdox  Test that the BaseController contains the right dependencies
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testInjectedDependencies()
	{
		$mvcFactory = $this->createStub(MVCFactoryInterface::class);
		$app        = $this->createStub(CMSApplication::class);
		$input      = $this->createStub(Input::class);

		$controller = new class(['base_path' => __DIR__], $mvcFactory, $app, $input) extends BaseController
		{
			public function getMVCFactory(): MVCFactoryInterface
			{
				return $this->factory;
			}

			public function getApplication(): CMSApplication
			{
				return $this->app;
			}

			public function getInput(): Input
			{
				return $this->input;
			}
		};

		$this->assertEquals($mvcFactory, $controller->getMVCFactory());
		$this->assertEquals($input, $controller->getInput());
	}

	/**
	 * @testdox  Test that the BaseController gets the injected name
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetInjectedName()
	{
		$controller = new class(
			['name' => 'unit test', 'base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$this->createStub(CMSApplication::class)
		) extends BaseController
		{};

		$this->assertEquals('unit test', $controller->getName());
	}

	/**
	 * @testdox  Test that the BaseController compiles it's own name
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetCompiledName()
	{
		$controller = new class(
			['base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$this->createStub(CMSApplication::class)
		) extends BaseController
		{};

		$this->assertStringContainsStringIgnoringCase('base', $controller->getName());
	}

	/**
	 * @testdox  Test that the BaseModel executes a task
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testExecuteTask()
	{
		$controller = new class(
			['base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$this->createStub(CMSApplication::class)
		) extends BaseController
		{
			public function unit()
			{
				return 'unit test';
			}
		};

		$this->assertEquals('unit test', $controller->execute('unit'));
	}

	/**
	 * @testdox  Test that the BaseController can execute the injected default task
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testExecuteInjectedDefaultTask()
	{
		$controller = new class(
			['default_task' => 'unit', 'base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$this->createStub(CMSApplication::class)
		) extends BaseController
		{
			public function unit()
			{
				return 'unit test';
			}
		};

		$this->assertEquals('unit test', $controller->execute('unit'));
	}

	/**
	 * @testdox  Test that the BaseModel executes the display default task
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testExecuteDisplayDefaultTask()
	{
		$controller = new class(
			['base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$this->createStub(CMSApplication::class)
		) extends BaseController
		{
			public function display($cachable = false, $urlparams = array())
			{
				return 'unit test';
			}
		};

		$this->assertEquals('unit test', $controller->execute(''));
	}

	/**
	 * @testdox  Test that the BaseModel executes a task
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testExecuteTaskWhichDoesntExist()
	{
		$controller = new class(
			['default_task' => 'invalid', 'base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$this->createStub(CMSApplication::class)
		) extends BaseController
		{};

		$this->expectException(Exception::class);

		$controller->execute('unit');
	}

	/**
	 * @testdox  Test that the BaseController returns the correct model
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetModel()
	{
		$mvcFactory = $this->createStub(MVCFactoryInterface::class);
		$model      = new class(['dbo' => $this->createStub(DatabaseInterface::class), 'name' => 'test'], $mvcFactory) extends BaseDatabaseModel
		{
			protected $option = 'test';
		};
		$mvcFactory->method('createModel')->willReturn($model);

		$controller = new class(
			['base_path' => __DIR__],
			$mvcFactory,
			$this->createStub(CMSApplication::class),
			$this->createStub(Input::class)
		) extends BaseController
		{};

		$this->assertEquals($model, $controller->getModel());
	}

	/**
	 * @testdox  Test that the BaseController returns the correct model with an injected prefix
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetModelWithInjectedPrefix()
	{
		$mvcFactory = $this->createStub(LegacyFactory::class);
		$model      = new class(['dbo' => $this->createStub(DatabaseInterface::class), 'name' => 'test'], $mvcFactory) extends BaseDatabaseModel
		{
			protected $option = 'test';
		};
		$mvcFactory->method('createModel')->willReturnCallback(function($name, $prefix) use($model) {
			return $prefix === 'Test' ? $model : null;
		});

		$controller = new class(
			['model_prefix' => 'Test', 'base_path' => __DIR__],
			$mvcFactory,
			$this->createStub(CMSApplication::class),
			$this->createStub(Input::class)
		) extends BaseController
		{};

		$this->assertEquals($model, $controller->getModel('Unit'));
	}

	/**
	 * @testdox  Test that the BaseController returns the correct model with the app name prefix
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetModelWithAppNamePrefix()
	{
		$mvcFactory = $this->createStub(MVCFactoryInterface::class);
		$model      = new class(['dbo' => $this->createStub(DatabaseInterface::class), 'name' => 'test'], $mvcFactory) extends BaseDatabaseModel
		{
			protected $option = 'test';
		};
		$mvcFactory->method('createModel')->willReturnCallback(function($name, $prefix) use($model) {
			return $prefix === 'Test' ? $model : null;
		});

		$app = $this->createStub(CMSApplication::class);
		$app->method('getName')->willReturn('Test');

		$controller = new class(
			['base_path' => __DIR__],
			$mvcFactory,
			$app,
			$this->createStub(Input::class)
		) extends BaseController
		{};

		$this->assertEquals($model, $controller->getModel('Unit'));
	}

	/**
	 * @testdox  Test that the BaseController returns false when no model is available
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetNullModel()
	{
		$mvcFactory = $this->createStub(MVCFactoryInterface::class);

		$controller = new class(
			['base_path' => __DIR__],
			$mvcFactory,
			$this->createStub(CMSApplication::class),
			$this->createStub(Input::class)
		) extends BaseController
		{};

		$this->assertFalse($controller->getModel());
	}

	/**
	 * @testdox  Test that the BaseController returns the correct model with the identity from the app
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetModelWithIdentity()
	{
		$mvcFactory = $this->createStub(MVCFactoryInterface::class);
		$model      = new class(['dbo' => $this->createStub(DatabaseInterface::class), 'name' => 'test'], $mvcFactory) extends BaseDatabaseModel
		{
			protected $option = 'test';

			public function getUser(): User
			{
				return $this->getCurrentUser();
			}
		};
		$mvcFactory->method('createModel')->willReturn($model);

		$user = new User();
		$app = $this->createStub(CMSApplication::class);
		$app->method('getIdentity')->willReturn($user);

		$controller = new class(
			['base_path' => __DIR__],
			$mvcFactory,
			$app,
			$this->createStub(Input::class)
		) extends BaseController
		{};

		$this->assertEquals($user, $controller->getModel()->getUser());
	}

	/**
	 * @testdox  Test that the BaseController returns the correct view
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetView()
	{
		$view = new class(['name' => 'test']) extends AbstractView
		{
			public function display($tpl = null)
			{}
		};
		$mvcFactory = $this->createStub(MVCFactoryInterface::class);
		$mvcFactory->method('createView')->willReturn($view);

		$controller = new class(
			['base_path' => __DIR__],
			$mvcFactory,
			$this->createStub(CMSApplication::class),
			$this->createStub(Input::class)
		) extends BaseController
		{};

		$this->assertEquals($view, $controller->getView('testGetView'));
	}

	/**
	 * @testdox  Test that the BaseController throws an exception when no view is available
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetNullView()
	{
		$mvcFactory = $this->createStub(MVCFactoryInterface::class);

		$controller = new class(
			['base_path' => __DIR__],
			$mvcFactory,
			$this->createStub(CMSApplication::class),
			$this->createStub(Input::class)
		) extends BaseController
		{};

		$this->expectException(Exception::class);

		$controller->getView('testGetNullView');
	}

	/**
	 * @testdox  Test that the BaseController returns the correct view with the identity from the app
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetViewWithIdentity()
	{
		$view = new class(['name' => 'test']) extends AbstractView implements CurrentUserInterface
		{
			use CurrentUserTrait;

			public function display($tpl = null)
			{}

			public function getUser(): User
			{
				return $this->getCurrentUser();
			}
		};
		$mvcFactory = $this->createStub(MVCFactoryInterface::class);
		$mvcFactory->method('createView')->willReturn($view);

		$user = new User();
		$app = $this->createStub(CMSApplication::class);
		$app->method('getIdentity')->willReturn($user);

		$controller = new class(
			['base_path' => __DIR__],
			$mvcFactory,
			$app,
			$this->createStub(Input::class)
		) extends BaseController
		{};

		$this->assertEquals($user, $controller->getView('testGetViewWithIdentity')->getUser());
	}

	/**
	 * @testdox  Test that the BaseController can display
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testDisplay()
	{
		$document = new Document;
		$app      = $this->createStub(CMSApplication::class);
		$app->method('getDocument')->willReturn($document);

		$view = new class(['name' => 'test']) extends AbstractView
		{
			public function display($tpl = null)
			{
				$this->document->setBuffer('unit test');
			}
		};
		$mvcFactory = $this->createStub(MVCFactoryInterface::class);
		$mvcFactory->method('createView')->willReturn($view);

		$controller = new class(
			['default_view' => 'unit', 'base_path' => __DIR__],
			$mvcFactory,
			$app,
			$this->createStub(Input::class)
		) extends BaseController
		{};

		$controller->display(false);

		$this->assertEquals('unit test', $document->getBuffer());
	}
}
