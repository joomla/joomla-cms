<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Class to define an abstract Web application router.
 *
 * @since       3.0
 * @deprecated  4.0  Use the `joomla/router` package via Composer instead
 */
abstract class JApplicationWebRouter
{
	/**
	 * @var    JApplicationWeb  The web application on whose behalf we are routing the request.
	 * @since  3.0
	 */
	protected $app;

	/**
	 * @var    string  The default page controller name for an empty route.
	 * @since  3.0
	 */
	protected $default;

	/**
	 * @var    string  Controller class name prefix for creating controller objects by name.
	 * @since  3.0
	 */
	protected $controllerPrefix;

	/**
	 * @var    JInput  An input object from which to derive the route.
	 * @since  3.0
	 */
	protected $input;

	/**
	 * Constructor.
	 *
	 * @param   JApplicationWeb  $app    The web application on whose behalf we are routing the request.
	 * @param   JInput           $input  An optional input object from which to derive the route.  If none
	 *                                   is given than the input from the application object will be used.
	 *
	 * @since   3.0
	 */
	public function __construct(JApplicationWeb $app, JInput $input = null)
	{
		$this->app   = $app;
		$this->input = ($input === null) ? $this->app->input : $input;
	}

	/**
	 * Find and execute the appropriate controller based on a given route.
	 *
	 * @param   string  $route  The route string for which to find and execute a controller.
	 *
	 * @return  mixed   The return value of the controller executed
	 *
	 * @since   3.0
	 * @throws  InvalidArgumentException
	 * @throws  RuntimeException
	 */
	public function execute($route)
	{
		// Get the controller name based on the route patterns and requested route.
		$name = $this->parseRoute($route);

		// Get the controller object by name.
		$controller = $this->fetchController($name);

		// Execute the controller.
		return $controller->execute();
	}

	/**
	 * Set the controller name prefix.
	 *
	 * @param   string  $prefix  Controller class name prefix for creating controller objects by name.
	 *
	 * @return  JApplicationWebRouter  This object for method chaining.
	 *
	 * @since   3.0
	 */
	public function setControllerPrefix($prefix)
	{
		$this->controllerPrefix	= (string) $prefix;

		return $this;
	}

	/**
	 * Set the default controller name.
	 *
	 * @param   string  $name  The default page controller name for an empty route.
	 *
	 * @return  JApplicationWebRouter  This object for method chaining.
	 *
	 * @since   3.0
	 */
	public function setDefaultController($name)
	{
		$this->default = (string) $name;

		return $this;
	}

	/**
	 * Parse the given route and return the name of a controller mapped to the given route.
	 *
	 * @param   string  $route  The route string for which to find and execute a controller.
	 *
	 * @return  string  The controller name for the given route excluding prefix.
	 *
	 * @since   3.0
	 * @throws  InvalidArgumentException
	 */
	abstract protected function parseRoute($route);

	/**
	 * Get a JController object for a given name.
	 *
	 * @param   string  $name  The controller name (excluding prefix) for which to fetch and instance.
	 *
	 * @return  JController
	 *
	 * @since   3.0
	 * @throws  RuntimeException
	 */
	protected function fetchController($name)
	{
		// Derive the controller class name.
		$class = $this->controllerPrefix . ucfirst($name);

		// If the controller class does not exist panic.
		if (!class_exists($class) || !is_subclass_of($class, 'JController'))
		{
			throw new RuntimeException(sprintf('Unable to locate controller `%s`.', $class), 404);
		}

		// Instantiate the controller.
		$controller = new $class($this->input, $this->app);

		return $controller;
	}
}
