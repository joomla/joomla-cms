<?php
/**
* @version $Id: admin.templates.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Templates
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
 * @subpackage Languages
 */
class templateScreens {
	/**
	 * Static method to create the template object
	 * @param int The client identifier
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function &createTemplate( $client=0, $files=null ) {
		$tmpl =& mosFactory::getPatTemplate( $files );
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );
		$tmpl->addVar( 'body', 'client', $client );

		return $tmpl;
	}

	/**
	 * Installation form
	 * @param int The client identifier
	 */
	function installOptions() {
		$tmpl =& templateScreens::createTemplate( 0, array( 'installer.html' ) );
		$tmpl->setAttribute( 'body', 'src', 'installOptions.html' );

		$tmpl->addVar( 'body', 'sitepath', $GLOBALS['mosConfig_absolute_path'] );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Finished install
	 */
	function installDone( $element, $errno, $error ) {
		$tmpl =& templateScreens::createTemplate( 0 );
		$tmpl->setAttribute( 'body', 'src', 'installDone.html' );

		$tmpl->addVar( 'body', 'element', $element );
		$tmpl->addVar( 'body', 'errno', $errno );
		$tmpl->addVar( 'body', 'message', $error );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Installation form
	 * @param int The client identifier
	 */
	function packageOptions( $element, $client ) {
		$tmpl =& templateScreens::createTemplate( $client, array( 'installer.html' ) );
		$tmpl->setAttribute( 'body', 'src', 'packageOptions.html' );

		$fileName = mosMainFrame::getClientName( $client ) . 'Template_' . $element;
		$tmpl->addVar( 'body', 'filename', $fileName );
		$tmpl->addVar( 'body', 'element', $element );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Lists package files
	 * @param array An array of files
	 */
	function listFiles( $files ) {
		$tmpl =& templateScreens::createTemplate( 0, array( 'files.html' ) );
		$tmpl->setAttribute( 'body', 'src', 'listFiles.html' );

		$tmpl->addRows( 'file-list-rows', $files );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Show the edit XML form
	 * @param array An array of xml variables
	 * @param string The template name
	 * @param int The client identifier
	 */
  	function editXML( &$vars, $element, $client ) {
		$tmpl =& templateScreens::createTemplate( $client, array( 'xml.html' ) );
		$tmpl->setAttribute( 'body', 'src', 'editXML.html' );

		if (isset( $vars['meta'] )) {
			$tmpl->addVars( 'body', $vars['meta'], 'meta_' );
		}

		if (isset( $vars['siteFiles'] )) {
			$tmpl->addRows( 'site-files-list', $vars['siteFiles'] );
		}
		$tmpl->addVar( 'body', 'element', $element );
		$tmpl->addVar( 'body', 'client', $client );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * @param array Data rows
	 * @param object Page navigation
	 * @param int The client identifier
	 */
	function view( &$rows, &$pageNav, $client ) {
		$tmpl =& templateScreens::createTemplate( $client, array( ) );
		$tmpl->setAttribute( 'body', 'src', 'view.html' );

		$tmpl->addObject( 'body-list-rows', $rows, 'row_' );

		// setup the page navigation footer
		$pageNav->setTemplateVars( $tmpl );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Preview site
	 * @param boolean True if to preview module positions
	 * @param int The client identifier
	 */
	function preview( $tp=0, $client=0 ) {
		$tmpl =& templateScreens::createTemplate( $client );
		$tmpl->setAttribute( 'body', 'src', 'preview.html' );

		$tmpl->addVar( 'body', 'tp', $tp );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Preview site
	 * @param boolean True if to preview module positions
	 * @param int The client identifier
	 */
	function positions( $positions, $client ) {
		$tmpl =& templateScreens::createTemplate( $client );
		$tmpl->setAttribute( 'body', 'src', 'positions.html' );

		$tmpl->addObject( 'positions-list', $positions );
		//$tmpl->addVar( 'body', 'cols', 'row_' );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Edit the html for a template
	 * @param array An array of template variables
	 * @param array An array of module positions
	 * @param int The client identifier
	 */
	function editHTML( &$vars, $positions, $client ) {
		$tmpl =& templateScreens::createTemplate( $client );
		$tmpl->setAttribute( 'body', 'src', 'editHTML.html' );

		$tmpl->addVars( 'body', $vars );
		$tmpl->addObject( 'positions-list', $positions, 'pos_' );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Edit the html for a template
	 * @param array An array of template variables
	 * @param int The client identifier
	 */
	function editCSS( &$vars, $client ) {
		$tmpl =& templateScreens::createTemplate( $client );
		$tmpl->setAttribute( 'body', 'src', 'editCSS.html' );

		$tmpl->addVars( 'body', $vars );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Assign templates to a menu item
	 */
	function assign( $element, $menulist, $client ) {
		$tmpl =& templateScreens::createTemplate( $client );
		$tmpl->setAttribute( 'body', 'src', 'assign.html' );

		$tmpl->addVar( 'body', 'element', $element );
		// the menulist should be done more generically
		$tmpl->addVar( 'body', 'menulist', $menulist );

		$tmpl->displayParsedTemplate( 'form' );
	}
}
?>