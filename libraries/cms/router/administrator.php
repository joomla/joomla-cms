<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Router
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Class to create and parse routes
 *
 * @since  1.5
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

		$basePath = JUri::base(true);

		// If the admin router is called from site client.
		if (JPATH_BASE == JPATH_SITE)
		{
			$basePath = $basePath . '/administrator';
		}

		// Add basepath to the uri
		$uri->setPath($basePath . '/' . $route);

		return $uri;
	}
}
