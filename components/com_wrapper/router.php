<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_wrapper
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class from com_wrapper
 *
 * @package     Joomla.Site
 * @subpackage  com_wrapper
 * @since       3.2
 */
class WrapperRouter implements JComponentRouter
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

		$vars['view'] = 'wrapper';

		return $vars;
	}
}
