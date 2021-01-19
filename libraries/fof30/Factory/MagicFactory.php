<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Factory;

defined('_JEXEC') || die;

use FOF30\Controller\Controller;
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
 * Note: This factory class will ONLY look for MVC objects in the same component section (front-end, back-end) you are
 * currently running in. If they are not found a new one will be created magically.
 */
class MagicFactory extends BasicFactory implements FactoryInterface
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

	public function dispatcher(array $config = [])
	{
		$dispatcherClass = $this->container->getNamespacePrefix() . 'Dispatcher\\Dispatcher';

		try
		{
			return $this->createDispatcher($dispatcherClass, $config);
		}
		catch (DispatcherNotFound $e)
		{
			// Not found. Return the magically created Dispatcher
			$magic = new DispatcherFactory($this->container);

			return $magic->make($config);
		}
	}

	/**
	 * Creates a new TransparentAuthentication handler
	 *
	 * @param   array  $config  The configuration values for the TransparentAuthentication object
	 *
	 * @return  TransparentAuthentication
	 */
	public function transparentAuthentication(array $config = [])
	{
		$authClass = $this->container->getNamespacePrefix() . 'TransparentAuthentication\\TransparentAuthentication';

		try
		{
			return $this->createTransparentAuthentication($authClass, $config);
		}
		catch (TransparentAuthenticationNotFound $e)
		{
			// Not found. Return the magically created TA
			$magic = new TransparentAuthenticationFactory($this->container);

			return $magic->make($config);
		}
	}
}
