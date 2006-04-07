<?php
/**
* @version $Id: mosloadposition.php 2412 2006-02-16 17:24:10Z stingrey $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onPrepareContent', 'pluginLoadModule' );

/**
* Plugin that loads module positions within content
*/
function pluginLoadModule( &$row, &$params, $page=0 ) 
{
	global $database;

	// simple performance check to determine whether bot should process further
	if ( JString::strpos( $row->text, 'loadposition' ) === false ) {
		return true;
	}
	
	// Get plugin info
	$plugin =& JPluginHelper::getPlugin('content', 'loadmodule'); 

 	// expression to search for
 	$regex = '/{loadposition\s*.*?}/i';

	// check whether plugin has been unpublished
	if ( !$plugin->published ) {
		$row->text = preg_replace( $regex, '', $row->text );
		return true;
	}

 	// find all instances of plugin and put in $matches
	preg_match_all( $regex, $row->text, $matches );

	// Number of plugins
 	$count = count( $matches[0] );

 	// plugin only processes if there are any instances of the plugin in the text
 	if ( $count ) {
		// Get plugin parameters
	 	$pluginParams = new JParameter( $plugin->params );

	 	$style	= $pluginParams->def( 'style', -2 );

 		processPositions( $row, $matches, $count, $regex, $style );
	}
}

function processPositions ( &$row, &$matches, $count, $regex, $style ) 
{
	global $database;

	$query = "SELECT position"
	. "\n FROM #__template_positions"
	. "\n ORDER BY position"
	;
	$database->setQuery( $query );
 	$positions 	= $database->loadResultArray();

 	for ( $i=0; $i < $count; $i++ ) {
 		$load = str_replace( 'oadposition', '', $matches[0][$i] );
 		$load = str_replace( '{', '', $load );
 		$load = str_replace( '}', '', $load );
 		$load = trim( $load );

		foreach ( $positions as $position ) {
	 		if ( $position == @$load ) {
				$modules	= loadPosition( $load, $style );
				$row->text 	= preg_replace( '{'. $matches[0][$i] .'}', $modules, $row->text );
				break;
	 		}
 		}
 	}

  	// removes tags without matching module positions
	$row->text = preg_replace( $regex, '', $row->text );
}

function loadPosition( $position, $style=-2 ) {
	$modules = '';
	if ( mosCountModules( $position ) ) {
		ob_start();
		mosLoadModules ( $position, $style );
		$modules = ob_get_contents();
		ob_end_clean();
	}

	return $modules;
}
?>