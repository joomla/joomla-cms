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
use Joomla\CMS\MVC\Controller\BaseController;
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

		$this->assertEquals($user, $controller->getModel()->getCurrentUser());
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

		$this->assertEquals($user, $controller->getView('testGetViewWithIdentity')->getCurrentUser());
	}
}
