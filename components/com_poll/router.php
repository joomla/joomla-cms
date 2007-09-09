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

	if(isset($query['view'])) 
	{
		if(!isset($query['Itemid'])) {
			$segments[] = $query['view'];
		} 
		
		unset($query['view']);
	};
	
	if (isset( $query['id'] ))
	{
		$segments[] = $query['id'];
		unset( $query['id'] );
	};

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
	$menu	=& JSite::getMenu();
	$item	=& $menu->getActive();
	
	//Standard routing for articles
	if(!isset($item)) 
	{
		$vars['view']  = $segments[$count - 2];
		$vars['id']    = $segments[$count - 1];
		return $vars;
	}

	// Count route segments
	$count			= count( $segments );
	$vars['id']		= $segments[$count-1];

	return $vars;
}