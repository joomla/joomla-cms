<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterBase;

/**
 * Routing class from com_search
 *
 * @since  3.3
 */
class SearchRouter extends RouterBase
{
	/**
	 * Build the route for the com_search component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */
	public function build(&$query)
	{
		$segments = array();

		if (isset($query['view']))
		{
			unset($query['view']);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */
	public function parse(&$segments)
	{
		$vars = array();

		// Fix up search for URL
		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			// Urldecode twice because it is encoded twice
			$segments[$i] = urldecode(urldecode(stripcslashes($segments[$i])));
		}

		$searchword         = array_shift($segments);
		$vars['searchword'] = $searchword;
		$vars['view']       = 'search';

		return $vars;
	}
}
