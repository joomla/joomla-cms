<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * RESTful Web application router class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       12.3
 */
class JApplicationWebRouterRest extends JApplicationWebRouterBase
{
	/**
	 * @var    array  An array of HTTP Method => controller suffix pairs for routing the request.
	 * @since  12.3
	 */
	protected $suffixMap = array(
		'GET' => 'Get',
		'POST' => 'Create',
		'PUT' => 'Update',
		'PATCH' => 'Update',
		'DELETE' => 'Delete',
		'HEAD' => 'Head',
		'OPTIONS' => 'Options'
	);

	/**
	 * Set a controller class suffix for a given HTTP method.
	 *
	 * @param   string  $method  The HTTP method for which to set the class suffix.
	 * @param   string  $suffix  The class suffix to use when fetching the controller name for a given request.
	 *
	 * @return  JApplicationWebRouter  This object for method chaining.
	 *
	 * @since   12.3
	 */
	public function setHttpMethodSuffix($method, $suffix)
	{
		$this->suffixMap[strtoupper((string) $method)] = (string) $suffix;
	}

	/**
	 * Get a JController object for a given name.
	 *
	 * @param   string  $name  The controller name (excluding prefix) for which to fetch and instance.
	 *
	 * @return  JController
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	protected function fetchController($name)
	{
		// Validate that we have a map to handle the given HTTP method.
		if (!isset($this->suffixMap[$this->input->getMethod()]))
		{
			throw new RuntimeException(sprintf('Unable to support the HTTP method `%s`.', $this->input->getMethod()), 404);
		}

		// Derive the controller class name.
		$class = $this->controllerPrefix . ucfirst($name) . $this->suffixMap[$this->input->getMethod()];

		// If the controller class does not exist panic.
		if (!class_exists($class))
		{
			throw new RuntimeException(sprintf('Unable to locate controller `%s`.', $class), 404);
		}

		// Instantiate the controller.
		$controller = new $class($this->input, $this->app);

		return $controller;
	}
}
