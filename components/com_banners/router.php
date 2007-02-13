<?php
/**
 * @version		$Id$
 * @package  Joomla
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights
 * reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * @param	array	A named array
 * @param	object
 * @return	array
 */
function BannersBuildRoute(&$ARRAY)
{
	$parts = array();

	if (isset($ARRAY['task'])) {
		$parts[] = $ARRAY['task'];
	}
	if (isset($ARRAY['bid'])) {
		$parts[] = $ARRAY['bid'];
	}

	unset( $ARRAY['task'] );
	unset( $ARRAY['bid'] );

	return $parts;
}

/**
 * @param	array	A named array
 * @param	object
 *
 * Formats:
 *
 * index.php?/banners/task/bid/Itemid
 *
 * index.php?/banners/bid/Itemid
 */
function BannersParseRoute(&$ARRAY)
{
	// view is always the first element of the array
	$nArray	= count($ARRAY);
	if ($nArray) {
		$nArray--;
		$part = array_shift($ARRAY);
		if (is_numeric( $part )) {
			JRequest::setVar('bid', $part, 'get');
		} else {
			JRequest::setVar('task', $part, 'get');
		}
	}
	if ($nArray) {
		$nArray--;
		$part = array_shift($ARRAY);
		if (is_numeric( $part )) {
			JRequest::setVar('bid', $part, 'get');
		}
	}
}
?>