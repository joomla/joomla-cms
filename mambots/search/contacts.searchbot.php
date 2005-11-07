<?php
/**
* @version $Id$
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
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$_MAMBOTS->registerFunction( 'onSearch', 'botSearchContacts' );

/**
* Contacts Search method
*
* The sql must return the following fields that are used in a common display
* routine: href, title, section, created, text, browsernav
* @param string Target search string
* @param string mathcing option, exact|any|all
* @param string ordering option, newest|oldest|popular|alpha|category
*/
function botSearchContacts( $text, $phrase='', $ordering='' ) {
	global $database, $my;
	global $_LANG;

	// load mambot params info
	$query = "SELECT id"
	. "\n FROM #__mambots"
	. "\n WHERE element = 'contacts.searchbot'"
	. "\n AND folder = 'search'"
	;
	$database->setQuery( $query );
	$id 	= $database->loadResult();
	$mambot = new mosMambot( $database );
	$mambot->load( $id );
	$botParams = new mosParameters( $mambot->params );
	
	$limit = $botParams->def( 'search_limit', 50 );
	
	$text = trim( $text );
	if ($text == '') {
		return array();
	}

	$section = $_LANG->_( 'Contact' );

	switch ( $ordering ) {
		case 'alpha':
			$order = 'a.name ASC';
			break;
			
		case 'category':
			$order = 'b.title ASC, a.name ASC';
			break;
			
		case 'popular':
		case 'newest':
		case 'oldest':
		default:
			$order = 'a.name DESC';
	}

	$query = "SELECT a.name AS title,"
	. "\n CONCAT_WS( ', ', a.name, a.con_position, a.misc ) AS text,"
	. "\n '' AS created,"
	. "\n CONCAT_WS( ' / ', '$section', b.title ) AS section,"
	. "\n '2' AS browsernav,"
	. "\n CONCAT( 'index.php?option=com_contact&task=view&&contact_id=', a.id ) AS href"
	. "\n FROM #__contact_details AS a"
	. "\n INNER JOIN #__categories AS b ON b.id = a.catid AND b.access <= '$my->gid'"
	. "\n WHERE ( a.name LIKE '%$text%'"
	. "\n OR a.misc LIKE '%$text%'"
	. "\n OR a.con_position LIKE '%$text%'"
	. "\n OR a.address LIKE '%$text%'"
	. "\n OR a.suburb LIKE '%$text%'"
	. "\n OR a.state LIKE '%$text%'"
	. "\n OR a.country LIKE '%$text%'"
	. "\n OR a.postcode LIKE '%$text%'"
	. "\n OR a.telephone LIKE '%$text%'"
	. "\n OR a.fax LIKE '%$text%' )"
	. "\n AND a.published = 1"
	. "\n ORDER BY $order"
	;
	$database->setQuery( $query, 0, $limit );	
	$rows = $database->loadObjectList();
	
	return $rows;
}
?>