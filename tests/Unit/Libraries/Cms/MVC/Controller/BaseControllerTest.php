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
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\MVC\View\AbstractView;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Input\Input;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\MVC\Controller\BaseController
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 * @since       __DEPLOY_VERSION__
 */
class BaseControllerTest extends UnitTestCase
{
	/**
	 * @testdox  The BaseController contains the right dependencies
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testInjectedDependencies()
	{
		$mvcFactory = $this->createStub(MVCFactoryInterface::class);
		$app        = $this->createStub(CMSApplication::class);
		$input      = new Input;

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
	 * @testdox  The BaseController gets the injected name
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
	 * @testdox  The BaseController compiles it's own name
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
	 * @testdox  The BaseController lists the correct tasks
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testAvailableTasks()
	{
		$controller = new class(
			['base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$this->createStub(CMSApplication::class)
		) extends BaseController
		{
			public function unit()
			{}
		};

		$this->assertEquals(['unit', 'display'], $controller->getTasks());
	}

	/**
	 * @testdox  The BaseController executes a task
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testUnregisterTask()
	{
		$controller = new class(
			['base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$this->createStub(CMSApplication::class)
		) extends BaseController
		{
			public function list()
			{
				return $this->taskMap;
			}
		};
		$controller->unregisterTask('list');

		$this->assertNotContains('list', $controller->list());
	}

	/**
	 * @testdox  The BaseController executes a task
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
		$this->assertEquals('unit', $controller->getTask());
	}

	/**
	 * @testdox  The BaseController can execute the injected default task
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
		$this->assertEquals('unit', $controller->getTask());
	}

	/**
	 * @testdox  The BaseController executes the display default task
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
		$this->assertEquals('', $controller->getTask());
	}

	/**
	 * @testdox  The BaseController executes a task
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
	 * @testdox  The BaseController returns the correct model
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
			new Input
		) extends BaseController
		{};

		$this->assertEquals($model, $controller->getModel());
	}

	/**
	 * @testdox  The BaseController returns the correct model with an injected prefix
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetModelWithInjectedPrefix()
	{
		$mvcFactory = $this->createMock(LegacyFactory::class);
		$mvcFactory->expects($this->once())->method('createModel')->with($this->equalTo('Unit'), $this->equalTo('Test'));

		$controller = new class(
			['model_prefix' => 'Test', 'base_path' => __DIR__],
			$mvcFactory,
			$this->createStub(CMSApplication::class),
			new Input
		) extends BaseController
		{};
		$controller->getModel('Unit');
	}

	/**
	 * @testdox  The BaseController returns the correct model with the app name prefix
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetModelWithAppNamePrefix()
	{
		$mvcFactory = $this->createMock(MVCFactoryInterface::class);
		$mvcFactory->expects($this->once())->method('createModel')->with($this->equalTo('Unit'), $this->equalTo('Test'));

		$app = $this->createStub(CMSApplication::class);
		$app->method('getName')->willReturn('Test');

		$controller = new class(
			['base_path' => __DIR__],
			$mvcFactory,
			$app,
			new Input
		) extends BaseController
		{};
		$controller->getModel('Unit');
	}

	/**
	 * @testdox  The BaseController returns false when no model is available
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
			new Input
		) extends BaseController
		{};

		$this->assertFalse($controller->getModel());
	}

	/**
	 * @testdox  The BaseController returns the correct model with the identity from the app
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

		$user = new User;
		$app = $this->createStub(CMSApplication::class);
		$app->method('getIdentity')->willReturn($user);

		$controller = new class(
			['base_path' => __DIR__],
			$mvcFactory,
			$app,
			new Input
		) extends BaseController
		{};

		$this->assertEquals($user, $controller->getModel()->getUser());
	}

	/**
	 * @testdox  The BaseController returns the correct view
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
			new Input
		) extends BaseController
		{};

		$this->assertEquals($view, $controller->getView('testGetView'));
	}

	/**
	 * @testdox  The BaseController returns the correct view with an injected prefix
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetViewWithInjectedPrefix()
	{
		$mvcFactory = $this->createMock(LegacyFactory::class);
		$mvcFactory->expects($this->once())->method('createView')->with($this->equalTo('Unit'), $this->equalTo('TestView'))
			->willReturn($this->createStub(AbstractView::class));

		$controller = new class(
			['name' => 'Test', 'base_path' => __DIR__],
			$mvcFactory,
			$this->createStub(CMSApplication::class),
			new Input
		) extends BaseController
		{};
		$controller->getView('Unit');
	}

	/**
	 * @testdox  The BaseController throws an exception when no view is available
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
			new Input
		) extends BaseController
		{};

		$this->expectException(Exception::class);

		$controller->getView('testGetNullView');
	}

	/**
	 * @testdox  The BaseController returns the correct view with the identity from the app
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

		$user = new User;
		$app = $this->createStub(CMSApplication::class);
		$app->method('getIdentity')->willReturn($user);

		$controller = new class(
			['base_path' => __DIR__],
			$mvcFactory,
			$app,
			new Input
		) extends BaseController
		{};

		$this->assertEquals($user, $controller->getView('testGetViewWithIdentity')->getUser());
	}

	/**
	 * @testdox  The BaseController gets the injected name
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testInjectViewPath()
	{
		$path       = dirname(__DIR__) . '/';
		$controller = new class(
			['view_path' => $path, 'base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$this->createStub(CMSApplication::class)
		) extends BaseController
		{
			public function getPaths()
			{
				return $this->paths;
			}
		};

		$this->arrayHasKey('view', $controller->getPaths());
		$this->assertEquals([$path], $controller->getPaths()['view']);
	}

	/**
	 * @testdox  The BaseController gets the injected name
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testAddViewPath()
	{
		$controller = new class(
			['name' => 'unit test', 'base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$this->createStub(CMSApplication::class)
		) extends BaseController
		{
			public function getPaths()
			{
				return $this->paths;
			}
		};

		$controller->addViewPath(__DIR__ . '/');

		$this->arrayHasKey('view', $controller->getPaths());
		$this->assertContains(__DIR__ . '/', $controller->getPaths()['view']);
	}

	/**
	 * @testdox  The BaseController can display
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testDisplay()
	{
		$app = $this->createStub(CMSApplication::class);
		$app->method('getDocument')->willReturn(new Document);

		$view = new class(['name' => 'test']) extends AbstractView
		{
			public $value = null;

			public function display($tpl = null)
			{
				$this->value = 'unit test';
			}
		};
		$mvcFactory = $this->createStub(MVCFactoryInterface::class);
		$mvcFactory->method('createView')->willReturn($view);

		$controller = new class(
			['default_view' => 'unit', 'base_path' => __DIR__],
			$mvcFactory,
			$app,
			new Input
		) extends BaseController
		{};

		$controller->display(false);

		$this->assertEquals('unit test', $view->value);
	}

	/**
	 * @testdox  The BaseController can display and sets a model on the view
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testDisplayWithModel()
	{
		$app = $this->createStub(CMSApplication::class);
		$app->method('getDocument')->willReturn(new Document);

		$model = new class(['name' => 'test']) extends BaseModel
		{};

		$view = new class(['name' => 'test']) extends AbstractView
		{
			public function display($tpl = null)
			{}
		};
		$mvcFactory = $this->createStub(MVCFactoryInterface::class);
		$mvcFactory->method('createView')->willReturn($view);
		$mvcFactory->method('createModel')->willReturn($model);

		$controller = new class(
			['default_view' => 'modeltest', 'base_path' => __DIR__],
			$mvcFactory,
			$app,
			new Input
		) extends BaseController
		{};

		$controller->display(false);

		$this->assertEquals($model, $view->getModel());
	}

	/**
	 * @testdox  The BaseController can check the edit id when it exists in user state
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testCheckEditIdExist()
	{
		$app = $this->createStub(CMSApplication::class);
		$app->method('getUserState')->willReturn([1]);

		$controller = new class(
			['base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$app,
			new Input
		) extends BaseController
		{
			public function checkEditId($context, $id)
			{
				return parent::checkEditId($context, $id);
			}
		};

		$this->assertTrue($controller->checkEditId('', 1));
	}

	/**
	 * @testdox  The BaseController cannot check the edit id when it doesn't exists in user state
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testCheckEditIdNotExist()
	{
		$app = $this->createStub(CMSApplication::class);
		$app->method('getUserState')->willReturn([1]);

		$controller = new class(
			['base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$app,
			new Input
		) extends BaseController
		{
			public function checkEditId($context, $id)
			{
				return parent::checkEditId($context, $id);
			}
		};

		$this->assertFalse($controller->checkEditId('', 2));
	}

	/**
	 * @testdox  The BaseController cannot check the edit id when it it is empty
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testCheckEditEmptyId()
	{
		$app = $this->createStub(CMSApplication::class);
		$app->method('getUserState')->willReturn([1]);

		$controller = new class(
			['base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$app,
			new Input
		) extends BaseController
		{
			public function checkEditId($context, $id)
			{
				return parent::checkEditId($context, $id);
			}
		};

		$this->assertTrue($controller->checkEditId('', 0));
	}

	/**
	 * @testdox  The BaseController can hold the edit id in app user state
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testHoldEditId()
	{
		$app = $this->createMock(CMSApplication::class);
		$app->expects($this->once())->method('setUserState')->with($this->equalTo('unit.id'), $this->equalTo([1]));

		$controller = new class(
			['base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$app,
			new Input
		) extends BaseController
		{
			public function holdEditId($context, $id)
			{
				return parent::holdEditId($context, $id);
			}
		};
		$controller->holdEditId('unit', 1);
	}

	/**
	 * @testdox  The BaseController cannot hold the edit id in app user state
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testHoldEditEmptyId()
	{
		$app = $this->createMock(CMSApplication::class);
		$app->expects($this->never())->method('setUserState');

		$controller = new class(
			['base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$app,
			new Input
		) extends BaseController
		{
			public function holdEditId($context, $id)
			{
				return parent::holdEditId($context, $id);
			}
		};
		$controller->holdEditId('unit', 0);
	}

	/**
	 * @testdox  The BaseController can release the edit id from app user state
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testReleaseEditId()
	{
		$app = $this->createMock(CMSApplication::class);
		$app->method('getUserState')->willReturn([1, 2]);
		$app->expects($this->once())->method('setUserState')->with($this->equalTo('unit.id'), $this->equalTo([1 => 2]));

		$controller = new class(
			['base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$app,
			new Input
		) extends BaseController
		{
			public function releaseEditId($context, $id)
			{
				return parent::releaseEditId($context, $id);
			}
		};
		$controller->releaseEditId('unit', 1);
	}

	/**
	 * @testdox  The BaseController cannot release the edit id from app user state when it doesn't exist
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testReleaseInvalidEditId()
	{
		$app = $this->createMock(CMSApplication::class);
		$app->method('getUserState')->willReturn([2]);
		$app->expects($this->never())->method('setUserState');

		$controller = new class(
			['base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$app,
			new Input
		) extends BaseController
		{
			public function releaseEditId($context, $id)
			{
				return parent::releaseEditId($context, $id);
			}
		};
		$controller->releaseEditId('unit', 1);
	}

	/**
	 * @testdox  The BaseController sets the correct redirect, message and type
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testSetRedirect()
	{
		$controller = new class(
			['base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$this->createStub(CMSApplication::class),
			new Input
		) extends BaseController
		{
			public function getRedirect()
			{
				return $this->redirect;
			}

			public function getMessage()
			{
				return $this->message;
			}

			public function getMessageType()
			{
				return $this->messageType;
			}
		};
		$controller->setRedirect('unit/test', 'unit', 'test');

		$this->assertEquals('unit/test', $controller->getRedirect());
		$this->assertEquals('unit', $controller->getMessage());
		$this->assertEquals('test', $controller->getMessageType());
	}

	/**
	 * @testdox  The BaseController sets the correct redirect and has the default type 'message'
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testSetRedirectWithEmptyType()
	{
		$controller = new class(
			['base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$this->createStub(CMSApplication::class),
			new Input
		) extends BaseController
		{
			public function getMessageType()
			{
				return $this->messageType;
			}
		};
		$controller->setRedirect('unit/test');

		$this->assertEquals('message', $controller->getMessageType());
	}

	/**
	 * @testdox  The BaseController redirects on the app
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testRedirect()
	{
		$app = $this->createMock(CMSApplication::class);
		$app->expects($this->once())->method('redirect')->with($this->equalTo('unit/test'));
		$app->expects($this->once())->method('enqueueMessage')->with($this->equalTo('unit test'));

		$controller = new class(
			['base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$app,
			new Input
		) extends BaseController
		{
			public function redirect()
			{
				$this->redirect = 'unit/test';
				$this->message = 'unit test';

				return parent::redirect();
			}
		};

		$this->assertFalse($controller->redirect());
	}

	/**
	 * @testdox  The BaseController sets the correct message and type
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testSetMessage()
	{
		$controller = new class(
			['base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$this->createStub(CMSApplication::class),
			new Input
		) extends BaseController
		{
			public function getMessage()
			{
				return $this->message;
			}

			public function getMessageType()
			{
				return $this->messageType;
			}
		};

		$this->assertEmpty($controller->setMessage('unit', 'test'));
		$this->assertEquals('unit', $controller->getMessage());
		$this->assertEquals('test', $controller->getMessageType());
	}

	/**
	 * @testdox  The BaseController redirects on the app
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testSetMessageTwice()
	{
		$controller = new class(
			['base_path' => __DIR__],
			$this->createStub(MVCFactoryInterface::class),
			$this->createStub(CMSApplication::class),
			new Input
		) extends BaseController
		{
			public function getMessage()
			{
				return $this->message;
			}
		};
		$controller->setMessage('unit');

		$this->assertEquals('unit', $controller->setMessage('test'));
	}
}
