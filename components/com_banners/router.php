<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
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