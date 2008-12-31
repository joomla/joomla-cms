<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * @param	array	A named array
 * @return	array
 */
function BannersBuildRoute( &$query )
{
	$segments = array();

	if (isset($query['task'])) {
		$segments[] = $query['task'];
		unset( $query['task'] );
	}
	if (isset($query['bid'])) {
		$segments[] = $query['bid'];
		unset( $query['bid'] );
	}

	return $segments;
}

/**
 * @param	array	A named array
 * @param	array
 *
 * Formats:
 *
 * index.php?/banners/task/bid/Itemid
 *
 * index.php?/banners/bid/Itemid
 */
function BannersParseRoute( $segments )
{
	$vars = array();

	// view is always the first element of the array
	$count = count($segments);

	if ($count)
	{
		$count--;
		$segment = array_shift( $segments );
		if (is_numeric( $segment )) {
			$vars['bid'] = $segment;
		} else {
			$vars['task'] = $segment;
		}
	}

	if ($count)
	{
		$count--;
		$segment = array_shift( $segments) ;
		if (is_numeric( $segment )) {
			$vars['bid'] = $segment;
		}
	}

	return $vars;
}