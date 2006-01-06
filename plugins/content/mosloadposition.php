<?php
/**
* @version $Id: mosloadposition.php 1541 2005-12-22 21:22:26Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onPrepareContent', 'botMosLoadPosition' );

/**
* Plugin that loads module positions within content
*/
function botMosLoadPosition( $published, &$row, &$params, $page=0 ) {
	global $database;

 	// expression to search for
 	$regex = '/{mosloadposition\s*.*?}/i';

	// check whether plugin has been unpublished
	if ( !$published ) {
		$row->text = preg_replace( $regex, '', $row->text );
		return true;
	}

 	// find all instances of plugin and put in $matches
	preg_match_all( $regex, $row->text, $matches );

	// Number of plugins
 	$count = count( $matches[0] );

 	// plugin only processes if there are any instances of the plugin in the text
 	if ( $count ) {
		// load plugin params info
		$query = "SELECT id"
		. "\n FROM #__plugins"
		. "\n WHERE element = 'mosloadposition'"
		. "\n AND folder = 'content'"
		;
		$database->setQuery( $query );
	 	$id 	= $database->loadResult();
	 	$plugin = new JPluginModel( $database );
	  	$plugin->load( $id );
	 	$botParams = new JParameters( $plugin->params );

	 	$style	= $botParams->def( 'style', -2 );

 		processPositions( $row, $matches, $count, $regex, $style );
	}
}

function processPositions ( &$row, &$matches, $count, $regex, $style ) {
	global $database;

	$query = "SELECT position"
	. "\n FROM #__template_positions"
	. "\n ORDER BY position"
	;
	$database->setQuery( $query );
 	$positions 	= $database->loadResultArray();

 	for ( $i=0; $i < $count; $i++ ) {
 		$load = str_replace( 'mosloadposition', '', $matches[0][$i] );
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