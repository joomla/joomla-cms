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
 * Basic Web application router class for the Joomla Platform.
 *
 * @since       12.2
 * @deprecated  3.7.0  Use the `joomla/router` package via Composer instead
 */
class JApplicationWebRouterBase extends JApplicationWebRouter
{
	/**
	 * @var    array  An array of rules, each rule being an associative array('regex'=> $regex, 'vars' => $vars, 'controller' => $controller)
	 *                for routing the request.
	 * @since  12.2
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
	 * @since   12.2
	 */
	public function addMap($pattern, $controller)
	{
		// Sanitize and explode the pattern.
		$pattern = explode('/', trim(parse_url((string) $pattern, PHP_URL_PATH), ' /'));

		// Prepare the route variables
		$vars = array();

		// Initialize regular expression
		$regex = array();

		// Loop on each segment
		foreach ($pattern as $segment)
		{
			// Match a splat with no variable.
			if ($segment == '*')
			{
				$regex[] = '.*';
			}
			// Match a splat and capture the data to a named variable.
			elseif ($segment[0] == '*')
			{
				$vars[] = substr($segment, 1);
				$regex[] = '(.*)';
			}
			// Match an escaped splat segment.
			elseif ($segment[0] == '\\' && $segment[1] == '*')
			{
				$regex[] = '\*' . preg_quote(substr($segment, 2));
			}
			// Match an unnamed variable without capture.
			elseif ($segment == ':')
			{
				$regex[] = '[^/]*';
			}
			// Match a named variable and capture the data.
			elseif ($segment[0] == ':')
			{
				$vars[] = substr($segment, 1);
				$regex[] = '([^/]*)';
			}
			// Match a segment with an escaped variable character prefix.
			elseif ($segment[0] == '\\' && $segment[1] == ':')
			{
				$regex[] = preg_quote(substr($segment, 1));
			}
			// Match the standard segment.
			else
			{
				$regex[] = preg_quote($segment);
			}
		}

		$this->maps[] = array(
			'regex' => chr(1) . '^' . implode('/', $regex) . '$' . chr(1),
			'vars' => $vars,
			'controller' => (string) $controller,
		);

		return $this;
	}

	/**
	 * Add a route map to the router.  If the pattern already exists it will be overwritten.
	 *
	 * @param   array  $maps  A list of route maps to add to the router as $pattern => $controller.
	 *
	 * @return  JApplicationWebRouter  This object for method chaining.
	 *
	 * @since   12.2
	 */
	public function addMaps($maps)
	{
		foreach ($maps as $pattern => $controller)
		{
			$this->addMap($pattern, $controller);
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
	 * @since   12.2
	 * @throws  InvalidArgumentException
	 */
	protected function parseRoute($route)
	{
		$controller = false;

		// Trim the query string off.
		$route = preg_replace('/([^?]*).*/u', '\1', $route);

		// Sanitize and explode the route.
		$route = trim(parse_url($route, PHP_URL_PATH), ' /');

		// If the route is empty then simply return the default route.  No parsing necessary.
		if ($route == '')
		{
			return $this->default;
		}

		// Iterate through all of the known route maps looking for a match.
		foreach ($this->maps as $rule)
		{
			if (preg_match($rule['regex'], $route, $matches))
			{
				// If we have gotten this far then we have a positive match.
				$controller = $rule['controller'];

				// Time to set the input variables.
				// We are only going to set them if they don't already exist to avoid overwriting things.
				foreach ($rule['vars'] as $i => $var)
				{
					$this->input->def($var, $matches[$i + 1]);

					// Don't forget to do an explicit set on the GET superglobal.
					$this->input->get->def($var, $matches[$i + 1]);
				}

				$this->input->def('_rawRoute', $route);

				break;
			}
		}

		// We were unable to find a route match for the request.  Panic.
		if (!$controller)
		{
			throw new InvalidArgumentException(sprintf('Unable to handle request for route `%s`.', $route), 404);
		}

		return $controller;
	}
}
