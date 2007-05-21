<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */


/**
 * @param	array
 * @return	array
 */
function PollBuildRoute( &$query )
{
	$segments = array();

	if (isset( $query['task'] ))
	{
		$segments[] = $query['task'];
		unset($query['task']);
	};

	if (isset( $query['id'] ))
	{
		$segments[] = $query['id'];
		unset( $query['id'] );
	};

	unset( $query['view'] );

	return $segments;
}

/**
 * @param	array
 * @return	array
 */
function PollParseRoute( $segments )
{
	$vars = array();

	//Get the active menu item
	$menu	=& JMenu::getInstance();
	$item	=& $menu->getActive();

	// Count route segments
	$count	= count( $segments );
	if ($count == 2) {
		$vars['id']		= $segments[$count-1];
		$vars['task']	= $segments[$count-2];
	}
	//Handle View and Identifier

	return $vars;
}