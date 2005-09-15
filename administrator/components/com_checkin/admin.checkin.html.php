<?php
/**
* @version $Id: admin.checkin.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Checkin
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Joomla
 * @subpackage Checkin
 */
class checkinScreens {
	/**
	 * Static method to create the template object
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function &createTemplate( $files=null) {
		$tmpl =& mosFactory::getPatTemplate( $files );
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );

		return $tmpl;
	}

	/**
	* index
	* @param array Data rows
	* @param object Page navigation
	*/
	function checkinList( &$rows, &$pageNav, &$lists, &$vars ) {
		$tmpl =& checkinScreens::createTemplate( );
		$tmpl->setAttribute( 'body', 'src', 'checkinList.html' );

		$tmpl->addObject( 'body-list-rows', $rows, 'row_' );
		$tmpl->addVars( 'body', $lists );
		$tmpl->addVars( 'body', $vars );

		// setup the page navigation footer
		$pageNav->setTemplateVars( $tmpl );

		$tmpl->displayParsedTemplate( 'form' );
	}
}
?>