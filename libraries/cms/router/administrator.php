<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Router
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Class to create and parse routes
 *
 * @package     Joomla.Libraries
 * @subpackage  Router
 * @since       1.5
 */
class JRouterAdministrator extends JRouter
{
	/**
	 * Function to convert a route to an internal URI.
	 *
	 * @param   JUri  &$uri  The uri.
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public function parse(&$uri)
	{
		return array();
	}

	/**
	 * Function to convert an internal URI to a route
	 *
	 * @param   string  $url  The internal URL
	 *
	 * @return  string  The absolute search engine friendly URL
	 *
	 * @since   1.5
	 */
	public function build($url)
	{
		// Create the URI object
		$uri = parent::build($url);

		// Get the path data
		$route = $uri->getPath();

		// Add basepath to the uri
		$uri->setPath(JUri::base(true) . '/' . $route);

		return $uri;
	}
}
