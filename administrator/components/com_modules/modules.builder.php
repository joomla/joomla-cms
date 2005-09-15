<?php
/**
* @version $Id: modules.builder.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Modules
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
 * @subpackage Components
 */
class moduleCreator {
	/**
	 * Static method to create the component object
	 * @param int The client identifier
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function &createTemplate( $files=null ) {
		$tmpl =& mosFactory::getPatTemplate( $files );
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl/create' );
		$tmpl->setNamespace( 'pat' );

		return $tmpl;
	}

	/**
	 * Main PHP File
	 */
	function phpMain( &$tmpl ) {
		$buffer = '';
		$buffer .= $tmpl->getParsedTemplate( 'php-start' );
		$buffer .= $tmpl->getParsedTemplate( 'php-module' );
		$buffer .= $tmpl->getParsedTemplate( 'php-end' );

		return $buffer;
	}

	/**
	 * HTML View
	 */
	function htmlMain( &$tmpl ) {

		$buffer = '';
		$buffer .= $tmpl->getParsedTemplate( 'html-start' );
		$buffer .= $tmpl->getParsedTemplate( 'html-module' );

		return $buffer;
	}

	/**
	 * Blank index file
	 */
	function htmlIndex() {
		return '<html><body></body></html>';
	}

	/**
	 * XML Main
	 */
	function xmlMain( &$tmpl ) {

		$buffer = '';
		$buffer .= $tmpl->getParsedTemplate( 'xml-main' );

		return $buffer;
	}


}
?>