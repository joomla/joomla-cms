<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class from com_search
 *
 * @package     Joomla.Site
 * @subpackage  com_search
 * @since       3.2
 */
class SearchRouter implements JComponentRouter
{
	/**
	 * @param   array
	 * @return  array
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
	 * @param   array
	 * @return  array
	 */
	public function parse(&$segments)
	{
		$vars = array();

		$searchword	= array_shift($segments);
		$vars['searchword'] = $searchword;
		$vars['view'] = 'search';

		return $vars;
	}
}
