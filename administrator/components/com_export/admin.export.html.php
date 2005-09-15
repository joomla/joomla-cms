<?php
/**
* @version $Id: admin.export.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Export
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
 * @subpackage Export
 */
class exportScreens {
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

	function message( $text ) {
		$tmpl =& exportScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'common.html' );
		$tmpl->addVar( 'mosmsg', 'mosmsg', $text );
		$tmpl->displayParsedTemplate( 'message' );
	}

	/**
	* Display screen for export options
	* @param array Data lists
	*/
	function exportOptions( &$lists ) {
		$tmpl =& exportScreens::createTemplate();

		$tmpl->setAttribute( 'body', 'src', 'exportOptions.html' );
		$tmpl->readTemplatesFromInput( 'common.html' );

		$tmpl->addVar( 'database-table-list', 'value', $lists['tables'] );

		$options = array();
		foreach ($lists['formatters'] as $class) {
			$formatter = new $class;
			$options[] = $formatter->options( $tmpl );
		}
		$tmpl->addVar( 'options-list', 'options', $options );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Display export results to screen
	 * @param string The buffer to display
	 * @param array An array of data variables
	 */
	function exportToScreen( &$buffer, $vars ) {
		$tmpl =& exportScreens::createTemplate();

		$tmpl->setAttribute( 'body', 'src', 'export.html' );
		$tmpl->readTemplatesFromInput( 'common.html' );

		$tmpl->addVar( 'body', 'buffer', $buffer );
		if ($vars) {
			$tmpl->addVars( 'body', $vars, 'var_' );
			$tmpl->addVar( 'sql-dump-header', 'show_header', 'true' );
		}

		$tmpl->displayParsedTemplate( 'form' );
		//$tmpl->dump();
	}

	/**
	 * An internal method for writing output to a file
	 * @param string The buffer to display
	 * @param array An array of data variables
	 * @return string
	 */
	function exportToFile( &$buffer, $vars ) {
		$tmpl =& exportScreens::createTemplate();

		$tmpl->setAttribute( 'body', 'src', 'exportToFile.html' );
		$tmpl->readTemplatesFromInput( 'common.html' );

		$tmpl->addVar( 'body', 'buffer', $buffer );
		if ($vars) {
			$tmpl->addVars( 'body', $vars, 'var_' );
			$tmpl->addVar( 'sql-dump-header', 'show_header', 'true' );
		}

		return $tmpl->getParsedTemplate( 'body' );
		//$tmpl->dump();
	}

	/**
	 * Displays a list of files in the /file folder
	 * @param array An array of files
	 */
	function restoreList( &$files ) {
		$tmpl =& exportScreens::createTemplate();

		$tmpl->setAttribute( 'body', 'src', 'restoreList.html' );
		$tmpl->readTemplatesFromInput( 'common.html' );

		$tmpl->addRows( 'body-list-rows', $files );
		$tmpl->displayParsedTemplate( 'form' );
	}
}
?>