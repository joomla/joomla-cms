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
 * Basic Web application router class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       12.3
 */
class JApplicationWebRouterBase extends JApplicationWebRouter
{
	/**
	 * @var    array  An array of pattern => controller pairs for routing the request.
	 * @since  12.3
	 */
	protected $maps = array();

	/**
	 * Add a route map to the router.  If the pattern already exists it will be overwritten.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   string  $controller  The controller name to map to the given pattern.
	 *
	 * @return  JApplicationWebRouter  This object for method chaining.
	 *
	 * @since   12.3
	 */
	public function addMap($pattern, $controller)
	{
		$this->maps[(string) $pattern] = (string) $controller;

		return $this;
	}

	/**
	 * Add a route map to the router.  If the pattern already exists it will be overwritten.
	 *
	 * @param   array  $maps  A list of route maps to add to the router as $pattern => $controller.
	 *
	 * @return  JApplicationWebRouter  This object for method chaining.
	 *
	 * @since   12.3
	 */
	public function addMaps($maps)
	{
		foreach ($maps as $pattern => $controller)
		{
			$this->maps[(string) $pattern] = (string) $controller;
		}

		return $this;
	}

	/**
	 * Parse the given route and return the name of a controller mapped to the given route.
	 *
	 * @param   string  $route  The route string for which to find and execute a controller.
	 *
	 * @return  string  The controller name for the given route excluding prefix.
	 *
	 * @since   12.3
	 * @throws  InvalidArgumentException
	 */
	protected function parseRoute($route)
	{
		// Initialize variables.
		$controller = false;

		// Sanitize and explode the route.
		$route = explode('/', trim(parse_url($route, PHP_URL_PATH), ' /'));

		// Cache the route length so we don't have to calculate this on every iteration through the pattern loop.
		$routeLength = count($route);

		// If the route is empty then simply return the default route.  No parsing necessary.
		if ($routeLength == 1 && $route[0] == '')
		{
			return $this->default;
		}

		// Iterate through all of the known route maps looking for a match.
		foreach ($this->maps as $pattern => $name)
		{
			// Reset the route variables each time.  Cleanliness is next to buglessness ... or something. :-)
			$vars = array();

			// Sanitize and explode the pattern.
			$pattern = explode('/', trim(parse_url($pattern, PHP_URL_PATH), ' /'));

			// If we don't have the same number of segments then we definitely do not have a match.
			if ($routeLength != count($pattern))
			{
				continue;
			}

			// Iterate through all of the segments of the pattern to validate static and variable segments.
			foreach ($pattern as $i => $segment)
			{
				// If we are looking at a variable segment then save the value.
				if (strpos($segment, ':') === 0)
				{
					$vars[substr($segment, 1)] = $route[$i];
				}
				// If we are looking at a static segment and the value doesn't match the route segment then the pattern doesn't match.
				elseif ($segment != $route[$i])
				{
					continue 2;
				}
			}

			// If we have gotten this far then we have a positive match.
			$controller = $name;

			// Time to set the input variables.
			// We are only going to set them if they don't already exist to avoid overwriting things.
			foreach ($vars as $k => $v)
			{
				$this->input->def($k, $v);

				// Don't forget to do an explicit set on the GET superglobal.
				$this->input->get->def($k, $v);
			}

			break;
		}

		// We were unable to find a route match for the request.  Panic.
		if (!$controller)
		{
			throw new InvalidArgumentException(sprintf('Unable to handle request for route `%s`.', implode('/', $route)), 404);
		}

		return $controller;
	}
}
