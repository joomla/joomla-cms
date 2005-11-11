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

require_once( $mainframe->getPath( 'installer_html', 'component' ) );

?> <!--
HTML_installer::showInstallForm( 'Install new Component', $option, 'component', '', dirname(__FILE__) );
?>
<table class="content">
<?php
writableCell( 'media' );
writableCell( 'administrator/components' );
writableCell( 'components' );
writableCell( 'images/stories' );
?>
</table> -->
<?php
showInstalledComponents( $option );

/**
* @param string The URL option
*/
function showInstalledComponents( $option ) {
	global $database;

	$query = "SELECT *"
	. "\n FROM #__components"
	. "\n WHERE parent = 0"
	. "\n AND iscore = 0"
	. "\n ORDER BY name"
	;
	$database->setQuery( $query	);
	$rows = $database->loadObjectList();

	// Read the component dir to find components
	$componentBaseDir	= mosPathName( JPATH_ADMINISTRATOR . '/components' );
	$componentDirs 		= mosReadDirectory( $componentBaseDir );

	$n = count( $rows );
	for ($i = 0; $i < $n; $i++) {
		$row =& $rows[$i];

		$dirName = $componentBaseDir . $row->option;
		$xmlFilesInDir = mosReadDirectory( $dirName, '.xml$' );

		foreach ($xmlFilesInDir as $xmlfile) {
			// Read the file to see if it's a valid component XML file
			$xmlDoc = new DOMIT_Lite_Document();
			$xmlDoc->resolveErrors( true );

			if (!$xmlDoc->loadXML( $dirName . '/' . $xmlfile, false, true )) {
				continue;
			}

			$root = &$xmlDoc->documentElement;

			if ($root->getTagName() != 'mosinstall') {
				continue;
			}
			if ($root->getAttribute( "type" ) != "component") {
				continue;
			}

			$element 			= &$root->getElementsByPath('creationDate', 1);
			$row->creationdate 	= $element ? $element->getText() : 'Unknown';

			$element 			= &$root->getElementsByPath('author', 1);
			$row->author 		= $element ? $element->getText() : 'Unknown';

			$element 			= &$root->getElementsByPath('copyright', 1);
			$row->copyright 	= $element ? $element->getText() : '';

			$element 			= &$root->getElementsByPath('authorEmail', 1);
			$row->authorEmail 	= $element ? $element->getText() : '';

			$element 			= &$root->getElementsByPath('authorUrl', 1);
			$row->authorUrl 	= $element ? $element->getText() : '';

			$element 			= &$root->getElementsByPath('version', 1);
			$row->version 		= $element ? $element->getText() : '';

			$row->mosname 		= strtolower( str_replace( " ", "_", $row->name ) );
		}
	}

	HTML_component::showInstalledComponents( $rows, $option );
}
?>
