<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Factory;

defined('_JEXEC') || die;

use FOF30\Controller\Controller;
use FOF30\Dispatcher\Dispatcher;
use FOF30\Factory\Exception\ControllerNotFound;
use FOF30\Factory\Exception\DispatcherNotFound;
use FOF30\Factory\Exception\ModelNotFound;
use FOF30\Factory\Exception\TransparentAuthenticationNotFound;
use FOF30\Factory\Exception\ViewNotFound;
use FOF30\Factory\Magic\DispatcherFactory;
use FOF30\Factory\Magic\TransparentAuthenticationFactory;
use FOF30\Model\Model;
use FOF30\Toolbar\Toolbar;
use FOF30\TransparentAuthentication\TransparentAuthentication;
use FOF30\View\View;

/**
 * Magic MVC object factory. This factory will "magically" create MVC objects even if the respective classes do not
 * exist, based on information in your fof.xml file.
 *
 * Note: This factory class will look for MVC objects in BOTH component sections (front-end, back-end), not just the one
 * you are currently running in. If no class is found a new object will be created magically. This is the same behaviour
 * as FOF 2.x.
 */
class MagicSwitchFactory extends SwitchFactory implements FactoryInterface
{
	/**
	 * Create a new Controller object
	 *
	 * @param   string  $viewName  The name of the view we're getting a Controller for.
	 * @param   array   $config    Optional MVC configuration values for the Controller object.
	 *
	 * @return  Controller
	 */
	public function controller($viewName, array $config = [])
	{
		try
		{
			return parent::controller($viewName, $config);
		}
		catch (ControllerNotFound $e)
		{
			$magic = new Magic\ControllerFactory($this->container);
			// Let's pass the section override (if any)
			$magic->setSection($this->getSection());

			return $magic->make($viewName, $config);
		}
	}

	/**
	 * Create a new Model object
	 *
	 * @param   string  $viewName  The name of the view we're getting a Model for.
	 * @param   array   $config    Optional MVC configuration values for the Model object.
	 *
	 * @return  Model
	 */
	public function model($viewName, array $config = [])
	{
		try
		{
			return parent::model($viewName, $config);
		}
		catch (ModelNotFound $e)
		{
			$magic = new Magic\ModelFactory($this->container);
			// Let's pass the section override (if any)
			$magic->setSection($this->getSection());

			return $magic->make($viewName, $config);
		}
	}

	/**
	 * Create a new View object
	 *
	 * @param   string  $viewName  The name of the view we're getting a View object for.
	 * @param   string  $viewType  The type of the View object. By default it's "html".
	 * @param   array   $config    Optional MVC configuration values for the View object.
	 *
	 * @return  View
	 */
	public function view($viewName, $viewType = 'html', array $config = [])
	{
		try
		{
			return parent::view($viewName, $viewType, $config);
		}
		catch (ViewNotFound $e)
		{
			$magic = new Magic\ViewFactory($this->container);
			// Let's pass the section override (if any)
			$magic->setSection($this->getSection());

			return $magic->make($viewName, $viewType, $config);
		}
	}

	/**
	 * Creates a new Toolbar
	 *
	 * @param   array  $config  The configuration values for the Toolbar object
	 *
	 * @return  Toolbar
	 */
	public function toolbar(array $config = [])
	{
		$appConfig = $this->container->appConfig;

		$defaultConfig = [
			'useConfigurationFile' => true,
			'renderFrontendButtons' => in_array($appConfig->get("views.*.config.renderFrontendButtons"), [
				true, 'true', 'yes', 'on', 1,
			]),
			'renderFrontendSubmenu' => in_array($appConfig->get("views.*.config.renderFrontendSubmenu"), [
				true, 'true', 'yes', 'on', 1,
			]),
		];

		$config = array_merge($defaultConfig, $config);

		return parent::toolbar($config);
	}

	/**
	 * Creates a new Dispatcher
	 *
	 * @param   array  $config  The configuration values for the Dispatcher object
	 *
	 * @return  Dispatcher
	 */
	public function dispatcher(array $config = [])
	{
		$dispatcherClass = $this->container->getNamespacePrefix($this->getSection()) . 'Dispatcher\\Dispatcher';

		try
		{
			return $this->createDispatcher($dispatcherClass, $config);
		}
		catch (DispatcherNotFound $e)
		{
			// Not found. Let's go on.
		}

		$dispatcherClass = $this->container->getNamespacePrefix('inverse') . 'Dispatcher\\Dispatcher';

		try
		{
			return $this->createDispatcher($dispatcherClass, $config);
		}
		catch (DispatcherNotFound $e)
		{
			// Not found. Return the magically created Dispatcher
			$magic = new DispatcherFactory($this->container);
			// Let's pass the section override (if any)
			$magic->setSection($this->getSection());

			return $magic->make($config);
		}
	}

	/**
	 * Creates a new TransparentAuthentication
	 *
	 * @param   array  $config  The configuration values for the TransparentAuthentication object
	 *
	 * @return  TransparentAuthentication
	 */
	public function transparentAuthentication(array $config = [])
	{
		$toolbarClass = $this->container->getNamespacePrefix($this->getSection()) . 'TransparentAuthentication\\TransparentAuthentication';

		try
		{
			return $this->createTransparentAuthentication($toolbarClass, $config);
		}
		catch (TransparentAuthenticationNotFound $e)
		{
			// Not found. Let's go on.
		}

		$toolbarClass = $this->container->getNamespacePrefix('inverse') . 'TransparentAuthentication\\TransparentAuthentication';

		try
		{
			return $this->createTransparentAuthentication($toolbarClass, $config);
		}
		catch (TransparentAuthenticationNotFound $e)
		{
			// Not found. Return the magically created TransparentAuthentication
			$magic = new TransparentAuthenticationFactory($this->container);
			// Let's pass the section override (if any)
			$magic->setSection($this->getSection());

			return $magic->make($config);
		}
	}
}
