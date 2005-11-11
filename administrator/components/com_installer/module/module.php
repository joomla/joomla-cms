<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installer
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

// ensure user has access to this function
if ( !$acl->acl_check( 'com_installer', $element, 'users', $my->usertype ) ) {
	mosRedirect( 'index2.php', $_LANG->_('ALERTNOTAUTH') );
}

require_once( $mainframe->getPath( 'installer_html', 'module' ) );
?><!--<?php
HTML_installer::showInstallForm( $_LANG->_( 'Install new Modules' ), $option, 'module', '', dirname(__FILE__) );
?>
<table class="content">
<?php
writableCell( 'media' );
writableCell( 'administrator/modules' );
writableCell( 'modules' );
?>
</table>-->
<?php
showInstalledModules( $option );

/**
* @param string The URL option
*/
function showInstalledModules( $_option ) {
	global $database, $mosConfig_absolute_path;
	global $_LANG;

	$filter 		= mosGetParam( $_POST, 'filter', '' );
	$select[] 		= mosHTML::makeOption( '', $_LANG->_( 'All' ) );
	$select[] 		= mosHTML::makeOption( '0', $_LANG->_( 'Site Modules' ) );
	$select[] 		= mosHTML::makeOption( '1', $_LANG->_( 'Admin Modules' ) );
	$lists['filter'] = mosHTML::selectList( $select, 'filter', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $filter );
	if ( $filter == NULL ) {
		$and = '';
	} else if ( !$filter ) {
		$and = "\n AND client_id = 0";
	} else if ( $filter ) {
		$and = "\n AND client_id = 1";
	}

	$query = "SELECT id, module, client_id"
	. "\n FROM #__modules"
	. "\n WHERE module LIKE 'mod_%' AND iscore='0'"
	. $and
	. "\n GROUP BY module, client_id"
	. "\n ORDER BY client_id, module"
	;
	$database->setQuery( $query );
	$rows = $database->loadObjectList();

	$n = count( $rows );
	for ($i = 0; $i < $n; $i++) {
		$row =& $rows[$i];

		// path to module directory
		if ($row->client_id == "1"){
			$moduleBaseDir	= mosPathName( mosPathName( JPATH_ADMINISTRATOR ) . "modules" );
		} else {
			$moduleBaseDir	= mosPathName( mosPathName( $mosConfig_absolute_path ) . "modules" );
		}

		// xml file for module
		$xmlfile = $moduleBaseDir. "/" .$row->module .".xml";

		if (file_exists( $xmlfile )) {
			$xmlDoc = new DOMIT_Lite_Document();
			$xmlDoc->resolveErrors( true );
			if (!$xmlDoc->loadXML( $xmlfile, false, true )) {
				continue;
			}

			$root = &$xmlDoc->documentElement;

			if ($root->getTagName() != 'mosinstall') {
				continue;
			}
			if ($root->getAttribute( "type" ) != "module") {
				continue;
			}

			$element 			= &$root->getElementsByPath( 'creationDate', 1 );
			$row->creationdate 	= $element ? $element->getText() : '';

			$element 			= &$root->getElementsByPath( 'author', 1 );
			$row->author 		= $element ? $element->getText() : '';

			$element 			= &$root->getElementsByPath( 'copyright', 1 );
			$row->copyright 	= $element ? $element->getText() : '';

			$element 			= &$root->getElementsByPath( 'authorEmail', 1 );
			$row->authorEmail 	= $element ? $element->getText() : '';

			$element 			= &$root->getElementsByPath( 'authorUrl', 1 );
			$row->authorUrl 	= $element ? $element->getText() : '';

			$element 			= &$root->getElementsByPath( 'version', 1 );
			$row->version 		= $element ? $element->getText() : '';
		}
	}

	HTML_module::showInstalledModules( $rows, $_option, $xmlfile, $lists );
}
?>
