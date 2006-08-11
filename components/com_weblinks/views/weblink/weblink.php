<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Weblinks
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package Joomla
 * @subpackage Weblinks
 * @since 1.0
 */
class WeblinksViewWeblink 
{
	/**
	 * Displays the edit form for new and existing web links (FRONTEND)
	 *
	 * A new record is defined when <var>$row</var> is passed with the <var>id</var>
	 * property set to 0.
	 *
	 * @param object $row The JWeblinkModel object to edit
	 * @param string $categories The html for the categories select list
	 * @since 1.0
	 */
	function editWeblink( &$row, &$categories ) 
	{
		global $mainframe;

		$option = JRequest::getVar('option');
		require_once( JPATH_SITE . '/includes/HTML_toolbar.php' );

		$Returnid = JRequest::getVar( 'Returnid', 0, '', 'int' );
		
		require(dirname(__FILE__).DS.'tmpl'.DS.'edit.php');	
	}
}
?>