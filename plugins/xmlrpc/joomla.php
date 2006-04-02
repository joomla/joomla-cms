<?php
/**
* @version $Id$
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

$mainframe->registerEvent( 'onGetWebServices', 'wsGetJoomlaWebServices' );

/**
* @return array An array of associative arrays defining the available methods
*/
function wsGetJoomlaWebServices() {
	return array(
		array(
			'name' => 'joomla.searchSite',
			'method' => 'wsSearchSite',
			'help' => 'Searches a remote site',
			'signature' => array('string','string','string') // ??
		),
	);
}

/**
* Remote Search method
*
* The sql must return the following fields that are used in a common display
* routine: href, title, section, created, text, browsernav
* @param string Target search string
* @param string mathcing option, exact|any|all
* @param string ordering option, newest|oldest|popular|alpha|category
*/
function wsSearchSite( $searchword, $phrase='', $order='' ) 
{
	global $mainframe;
	
	$database =& $mainframe->getDBO();
	
	$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : $mainframe->getBaseURL();

	if (!defined( '_MAMBOT_REMOTE_SEACH')) {
		// flag that the site is being searched remotely
		define( '_MAMBOT_REMOTE_SEACH', 1 );
	}

	$searchword = $database->getEscaped( trim( $searchword ) );
	$phrase = '';
	$ordering = '';

	JPluginHelper::importPlugin( 'search' );
	$results = $mainframe->triggerEvent( 'onSearch', array( $searchword, $phrase, $ordering ) );

	foreach ($results as $i=>$rows) {
		foreach ($rows as $j=>$row) {
			$results[$i][$j]->href = $url . '/' . $row->href;
			$results[$i][$j]->text = mosPrepareSearchContent( $row->text, 200, $searchword);
		}
	}
	return $results;

	//return new dom_xmlrpc_fault( '-1', 'Fault' );
}

?>