<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Class to create and parse routes.
 *
 * @since  1.5
 */
class JRouterInstallation extends JRouter
{
	/**
	 * Function to convert a route to an internal URI
	 *
	 * @param   JUri  &$url  The uri.
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function parse(&$url)
	{
		return true;
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
		$url = str_replace('&amp;', '&', $url);

		return new JUri($url);
	}
}
