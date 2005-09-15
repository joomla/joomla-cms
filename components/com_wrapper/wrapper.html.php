<?php
/**
* @version $Id: wrapper.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Wrapper
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
 * @subpackage Wrapper
 */
class wrapperScreens_front {
	/**
	 * @param string The main template file to include for output
	 * @param array An array of other standard files to include
	 * @return patTemplate A template object
	 */
	function &createTemplate( $bodyHtml='', $files=null ) {
		$tmpl =& mosFactory::getPatTemplate( $files );

		$directory = mosComponentDirectory( $bodyHtml, dirname( __FILE__ ) );
		$tmpl->setRoot( $directory );

		$tmpl->setAttribute( 'body', 'src', $bodyHtml );

		return $tmpl;
	}

	function view( &$row, &$params ) {
		$tmpl =& wrapperScreens_front::createTemplate( 'view.html' );

		$tmpl->addVar( 'body', 'url', 			$row->url );
		$tmpl->addVar( 'body', 'load', 			$row->load );
		$tmpl->addVar( 'body', 'loadx', 		$row->loadx );

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );

		$tmpl->displayParsedTemplate( 'body' );
	}
}
?>