<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Build function for a fictitious legacy com_comtest router
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.4
 */
function ComtestBuildRoute(&$query)
{
	$return = array();

	foreach ($query as $key => $var)
	{
		$return[] = $key . '-' . $var;
	}

	return $return;
}

/**
 * Parse function for a fictitious legacy com_comtest router
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.4
 */
function ComtestParseRoute(&$segments)
{
	$return = array();

	foreach ($segments as $segment)
	{
		list($key, $var) = explode(':', $segment, 2);
		$return[$key] = $var;
	}

	return $return;
}
