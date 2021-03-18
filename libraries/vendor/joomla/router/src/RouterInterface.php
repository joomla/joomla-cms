<?php
/**
 * Part of the Joomla Framework Router Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Router;

/**
 * Interface defining a HTTP path router.
 *
 * @since  __DEPLOY_VERSION__
 */
interface RouterInterface
{
	/**
	 * Add a route to the router.
	 *
	 * @param   Route  $route  The route definition
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addRoute(Route $route): RouterInterface;

	/**
	 * Add an array of route maps or objects to the router.
	 *
	 * @param   Route[]|array[]  $routes  A list of route maps or Route objects to add to the router.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \UnexpectedValueException  If missing the `pattern` or `controller` keys from the mapping array.
	 */
	public function addRoutes(array $routes): RouterInterface;

	/**
	 * Get the routes registered with this router.
	 *
	 * @return  Route[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getRoutes(): array;

	/**
	 * Parse the given route and return the information about the route, including the controller assigned to the route.
	 *
	 * @param   string  $route   The route string for which to find and execute a controller.
	 * @param   string  $method  Request method to match, should be a valid HTTP request method.
	 *
	 * @return  ResolvedRoute
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception\MethodNotAllowedException if the route was found but does not support the request method
	 * @throws  Exception\RouteNotFoundException if the route was not found
	 */
	public function parseRoute($route, $method = 'GET');

	/**
	 * Add a GET route to the router.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 * @param   array   $defaults    An array of default values that are used when the URL is matched.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function get(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface;

	/**
	 * Add a POST route to the router.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 * @param   array   $defaults    An array of default values that are used when the URL is matched.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function post(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface;

	/**
	 * Add a PUT route to the router.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 * @param   array   $defaults    An array of default values that are used when the URL is matched.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function put(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface;

	/**
	 * Add a DELETE route to the router.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 * @param   array   $defaults    An array of default values that are used when the URL is matched.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function delete(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface;

	/**
	 * Add a HEAD route to the router.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 * @param   array   $defaults    An array of default values that are used when the URL is matched.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function head(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface;

	/**
	 * Add a OPTIONS route to the router.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 * @param   array   $defaults    An array of default values that are used when the URL is matched.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function options(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface;

	/**
	 * Add a TRACE route to the router.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 * @param   array   $defaults    An array of default values that are used when the URL is matched.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function trace(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface;

	/**
	 * Add a PATCH route to the router.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 * @param   array   $defaults    An array of default values that are used when the URL is matched.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function patch(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface;

	/**
	 * Add a route to the router that accepts all request methods.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 * @param   array   $defaults    An array of default values that are used when the URL is matched.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function all(string $pattern, $controller, array $rules = [], array $defaults = []): RouterInterface;
}
