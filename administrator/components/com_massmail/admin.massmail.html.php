<?php
/**
* @version $Id: admin.massmail.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage massmail
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
 * @subpackage massmail
 */
class massmailScreens {
	/**
	 * Static method to create the template object
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function &createTemplate( $files=null) {
		mosFS::load( '@patTemplate' );

		$tmpl =& mosFactory::getPatTemplate( $files );
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );

		return $tmpl;
	}

	/**
	 * Example screen with data table and pagination
	 * @param array An array of list data
	 * @param object Page navigation
	 */
	function messageForm( &$lists ) {
		$tmpl =& massmailScreens::createTemplate();
		$tmpl->setAttribute( 'body', 'src', 'messageForm.html' );

		$tmpl->addRows( 'options-list', $lists['gid'] );

		$tmpl->displayParsedTemplate( 'form' );
	}
}
?>