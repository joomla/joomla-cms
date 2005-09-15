<?php
/**
* @version $Id: admin.components.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Components
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
class componentScreens {
	/**
	 * Static method to create the component object
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
	 * Create Options
	 */
	function createOptions() {
		global $_LANG;

		$tmpl =& componentScreens::createTemplate( 0, array( 'forms.html' ) );
		$tmpl->setAttribute( 'body', 'src', 'createOptions.html' );

		$client = array(
			patHTML::makeOption( 0, $_LANG->_( 'Site' ) ),
			patHTML::makeOption( 1, $_LANG->_( 'Administrator' ) ),
		);

		patHTML::radioSet( $tmpl, 'body', 'client_id', 0, $client, 'RADIO_CLIENT' );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Installation form
	 * @param int The client identifier
	 */
	function installOptions( &$vars ) {
		$tmpl =& componentScreens::createTemplate( 0, array( 'installer.html' ) );
		$tmpl->setAttribute( 'body', 'src', 'installOptions.html' );

		$tmpl->addVars( 'body', $vars );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Finished install
	 */
	function installDone( &$installer ) {
		$tmpl =& componentScreens::createTemplate( 0 );
		$tmpl->setAttribute( 'body', 'src', 'installDone.html' );

		$tmpl->addVar( 'body', 'element', $installer->elementName() );
		$tmpl->addVar( 'body', 'errno', $installer->errno() );
		$tmpl->addVar( 'body', 'message', $installer->error() );
		$tmpl->addVar( 'body', 'ilog', $installer->getLog() );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Installation form
	 * @param int The client identifier
	 */
	function packageOptions( $row ) {
		$tmpl =& componentScreens::createTemplate( 0, array( 'installer.html' ) );
		$tmpl->setAttribute( 'body', 'src', 'packageOptions.html' );

		$fileName = $row->option;
		$tmpl->addVar( 'body', 'filename', $fileName );
		$tmpl->addVar( 'body', 'element', $row->id );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Lists package files
	 * @param array An array of files
	 */
	function listFiles( $files ) {
		$tmpl =& componentScreens::createTemplate( 0, array( 'files.html' ) );
		$tmpl->setAttribute( 'body', 'src', 'listFiles.html' );

		$tmpl->addRows( 'file-list-rows', $files );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * Show the edit XML form
	 * @param array An array of xml variables
	 * @param object
	 * @param array
	 */
  	function editXML( &$vars, $row, $lists ) {
		$tmpl =& componentScreens::createTemplate( 0, array( 'xml.html' ) );
		$tmpl->setAttribute( 'body', 'src', 'editXML.html' );

		$tmpl->addObject( 'body', $row, 'row_' );
		if (isset( $vars['meta'] )) {
			$tmpl->addVars( 'body', $vars['meta'], 'meta_' );
		}

		if (isset( $vars['siteFiles'] )) {
			$tmpl->addRows( 'site-files-list', $vars['siteFiles'] );
		}
		if (isset( $vars['adminFiles'] )) {
			$tmpl->addRows( 'admin-files-list', $vars['adminFiles'] );
		}
		if (isset( $vars['adminMenus'] )) {
			$tmpl->addRows( 'admin-menus-list', $vars['adminMenus'] );
		}
		if (isset( $vars['adminSubMenus'] )) {
			$tmpl->addRows( 'admin-submenus-list', $vars['adminSubMenus'] );
		}

		$temp = array();
		foreach ($lists['tables'] as $table) {
			$temp[] = array(
				'value' => $table,
				'text' 	=> $table
			);
		}
		$tmpl->addRows( 'select-list-options', $temp );
		$tmpl->parseIntoVar( 'select-list-options', 'body', 'DATABASE_TABLES' );

		if (isset( $vars['installQueries'] )) {
			$tmpl->addRows( 'install-query-list', $vars['installQueries'] );
		}

		if (isset( $vars['uninstallQueries'] )) {
			$tmpl->addRows( 'uninstall-query-list', $vars['uninstallQueries'] );
		}

		if (isset( $vars['installFile'] )) {
			$tmpl->addVar( 'body', 'install_file', $vars['installFile']);
		}
		if (isset( $vars['uninstallFile'] )) {
			$tmpl->addVar( 'body', 'uninstall_file', $vars['uninstallFile']);
		}
		$tmpl->addVar( 'body', 'params', $vars['params'] );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * @param array Data rows
	 * @param object Page navigation
	 * @param int The client identifier
	 */
	function view( &$rows, &$pageNav, $client ) {
		$tmpl =& componentScreens::createTemplate( $client, array( ) );
		$tmpl->setAttribute( 'body', 'src', 'view.html' );

		$tmpl->addObject( 'body-list-rows', $rows, 'row_' );

		// setup the page navigation footer
		$pageNav->setTemplateVars( $tmpl );

		$tmpl->displayParsedTemplate( 'form' );
	}

	function listComponents() {
		mosLoadAdminModule( 'components' );
	}
}
?>
