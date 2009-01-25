<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

/**
 * @param	array
 * @return	array
 */
function WrapperBuildRoute( &$query )
{
	$segments = array();

	if (isset($query['view'])) {
		unset($query['view']);
	}

	return $segments;
}

/**
 * @param	array
 * @return	array
 */
function WrapperParseRoute( $segments )
{
	$vars = array();

	$vars['view'] = 'wrapper';

	return $vars;
}