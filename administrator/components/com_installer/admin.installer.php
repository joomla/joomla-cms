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

// XML library
require_once( $mosConfig_absolute_path . '/includes/domit/xml_domit_lite_include.php' );
require_once( $mainframe->getPath( 'admin_html' ) );
//require_once( $mainframe->getPath( 'class' ) );

$element 	= mosGetParam( $_REQUEST, 'element', '' );
$client 	= mosGetParam( $_REQUEST, 'client', '' );
$path 		= $GLOBALS['mosConfig_admin_path'] . "/components/com_installer/$element/$element.php";

jimport('joomla.installers.factory');

// ensure user has access to this function
if (!$acl->acl_check( 'com_installer', 'installer', 'users', $my->usertype ) ) {
	mosRedirect( 'index2.php', $_LANG->_('ALERTNOTAUTH') );
}

// map the element to the required derived class
$classMap = array(
	'component' => 'mosInstallerComponent',
	'language' 	=> 'mosInstallerLanguage',
	'mambot' 	=> 'mosInstallerMambot',
	'module' 	=> 'mosInstallerModule',
	'template' 	=> 'mosInstallerTemplate'
);
//echo $task;
switch ($task) {
	case 'uploadfile':
		uploadPackage( $option );
		break;

	case 'installfromdir':
		installFromDirectory( $option );
		break;
	
	case 'installfromurl':
		installFromUrl( $option );
		break;

	case 'remove':
		removeElement( $classMap[$element], $option, $element, $client );
		break;

	case 'installer':
		doInstaller();
		break;
	case 'updater':
		doUpdate();
		break;
	default:
		if (array_key_exists ( $element, $classMap ) ){
			require_once( $mainframe->getPath( 'installer_class', $element ) );
			$path = $GLOBALS['mosConfig_admin_path'] . "/components/com_installer/$element/$element.php";

			if (file_exists( $path )) {
				require $path;
			} else {
				doInstaller();
				//echo $_LANG->_( 'Installer not found for element' ) .' ['. $element .']';
			}
		} else {
			doInstaller();
			//echo $_LANG->_( 'Installer not available for element' ) .' ['. $element .']';
		}
		break;
}


/**
* @param string The class name for the installer
* @param string The URL option
* @param string The element name
*/
function uploadPackage( $option ) {
	global $_LANG;
	$installerFactory = new JInstallerFactory();
	$installer = new mosInstaller(); // Create a blank installer until we work out what the file is!
	// Check if file uploads are enabled
	if (!(bool)ini_get('file_uploads')) {
		HTML_installer::showInstallMessage( $_LANG->_( 'WARNINSTALLFILE' ),
			$_LANG->_( 'Installer - Error' ), $installer->returnTo( $option, $element, $client ) );
		exit();
	}

	// Check that the zlib is available
	if(!extension_loaded('zlib')) {
		HTML_installer::showInstallMessage( $_LANG->_( 'WARNINSTALLZLIB' ), $_LANG->_( 'Installer - Error' ), $installer->returnTo( $option, $element, $client ) );
		exit();
	}

	$userfile = mosGetParam( $_FILES, 'userfile', null );

	if (!$userfile) {
		HTML_installer::showInstallMessage( $_LANG->_( 'No file selected' ), $_LANG->_( 'Upload new element - error' ),
			$installer->returnTo( $option, $element, $client ));
		exit();
	}

	$userfile_name = $userfile['name'];
	$client = '';
	$msg = '';
	$resultdir = uploadFile( $userfile['tmp_name'], $userfile['name'], $msg );
	if ($resultdir !== false) {
		if (!$installer->upload( $userfile['name'] )) {
			HTML_installer::showInstallMessage( $installer->getError(), $_LANG->_( 'Upload' ) .' '. $element .' - '. $_LANG->_( 'Upload Failed' ),
				$installer->returnTo( $option, $element, $client ) );
		}
		$installdir = $installer->i_installdir;
		$element = $installerFactory->detectType($installer->unpackDir());
		$installerFactory->createClass($element);
                $installer = $installerFactory->getClass();
		$ret = $installer->install($installdir);

		HTML_installer::showInstallMessage( $installer->getError(), $_LANG->_( 'Upload' ) .' '. $element .' - '.($ret ? $_LANG->_( 'Success' ) : $_LANG->_( 'Failed' )),
			$installer->returnTo( $option, $element, $client ) );
		cleanupInstall( $userfile['name'], $installer->unpackDir() );
	} else {
		HTML_installer::showInstallMessage( $msg, $_LANG->_( 'Upload' ) .' '. $element .' - '. $_LANG->_( 'Upload Error' ),
			$installer->returnTo( $option, $element, $client ) );
	}
}

/**
* Install a template from a directory
* @param string The URL option
*/
function installFromDirectory( $option ) {
	global $_LANG, $classMap;

	$client = '';
	$userfile = mosGetParam( $_REQUEST, 'userfile', '' );

	if (!$userfile) {
		mosRedirect( "index2.php?option=$option&element=$element", $_LANG->_( 'Please select a directory' ) );
	}
	$installerFactory = new JInstallerFactory();
	$installer = new mosInstaller();
	$installer->installDir($userfile);
	if(!$installer->findInstallFile()) {
		HTML_installer::showInstallMessage( "Unable to find valid XML install" . ' ' . $userfile, 
			$_LANG->_( 'Install' ) .' '. $element .' - '. $_LANG->_( 'Detection Error' ),
			$installer->returnTo( $option, $element, $client ) );
	}
	
	$element = $installerFactory->detectType($userfile.'/');
	$installerClass = $classMap[$element];
	if(!$installerClass) {
		HTML_installer::showInstallMessage( "Unable to detect the type of install" . ' ' . $userfile,
			$_LANG->_( 'Install' ) .' '. $element .' - '. $_LANG->_( 'Detection Error' ),
			$installer->returnTo( $option, $element, $client ) );
		return;
	}
		
	jimport('joomla.installers.'.$element);
	$installer = new $installerClass();

	$path = mosPathName( $userfile );
	if (!is_dir( $path )) {
		$path = dirname( $path );
	}

	$ret = $installer->install( $path );
	HTML_installer::showInstallMessage( $installer->getError(), $_LANG->_( 'Upload new' ) .' '.$element.' - '.($ret ? $_LANG->_( 'Success' ) : $_LANG->_( 'Error' )), $installer->returnTo( $option, $element, $client ) );
}

/**
* Install an element from a URL
* @param string The URL
*/
function installFromUrl($option) {
	global $_LANG;
	$installerFactory = new JInstallerFactory();
	$userfile = mosGetParam( $_REQUEST, 'userfile', '' );
	$client = '';
	if(!$userfile) {
		mosRedirect( "index2.php?option=$option", $_LANG->_( 'Please enter a URL' ) );
	}
	$installer = $installerFactory->webInstall( $userfile );
	$element = $installerFactory->getType();
        $ret = $installer->msg;
	HTML_installer::showInstallMessage( 
		$installer->getError(), 
		$_LANG->_( 'Install new' ) .' '.$element.' - '.($ret ? $_LANG->_( 'Success' ) : $_LANG->_( 'Error' )), 
		$installer->returnTo( $option, $element, $client ) );	
}

/**
*
* @param
*/
function removeElement( $installerClass, $option, $element, $client ) {
	global $_LANG;

	$cid = mosGetParam( $_REQUEST, 'cid', array(0) );
	if (!is_array( $cid )) {
		$cid = array(0);
	}

	jimport('joomla.installers.'.$element);
	$installer 	= new $installerClass();
	$result 	= false;
	if ($cid[0]) {
		$result = $installer->uninstall( $cid[0], $option, $client );
	}

	$msg = $installer->getError();

	mosRedirect( $installer->returnTo( $option, $element, $client ), $result ? $_LANG->_( 'Success' ) .' '. $msg : $_LANG->_( 'Failed' ) .' '. $msg );
}
/**
* @param string The name of the php (temporary) uploaded file
* @param string The name of the file to put in the temp directory
* @param string The message to return
*/
function uploadFile( $filename, $userfile_name, &$msg ) {
	global $mosConfig_absolute_path;
	global $_LANG;

	$baseDir = mosPathName( $mosConfig_absolute_path . '/media' );

	if (file_exists( $baseDir )) {
		if (is_writable( $baseDir )) {
			if (move_uploaded_file( $filename, $baseDir . $userfile_name )) {
				if (mosChmod( $baseDir . $userfile_name )) {
					return true;
				} else {
					$msg = $_LANG->_( 'WARNPERMISSIONS' );
				}
			} else {
				$msg = $_LANG->_( 'Failed to move uploaded file to' ) .'<code>/media</code>'. $_LANG->_( 'directory.' );
			}
		} else {
			$msg = $_LANG->_( 'Upload failed as' ) .'<code>/media</code>'. $_LANG->_( 'directory is not writable.' );
		}
	} else {
		$msg = $_LANG->_( 'Upload failed as' ) .'<code>/media</code>'. $_LANG->_( 'directory does not exist.' );
	}
	return false;
}

/**
* Temporary Updater
*/
function doUpdate() {
	?>Updater not written yet, but this is where it would go if it was!<?php
}

/**
* Unified intaller
*/
function doInstaller() {
	global $option;
	HTML_installer::showInstallForm( 'Install new Extension', $option, 'element', '', dirname(__FILE__) );
?>
<table class="content">
<?php
writableCell( 'media' );
writableCell( 'images/stories' );
writableCell( 'administrator/components' );
writableCell( 'components' );
writableCell( 'administrator/modules' );
writableCell( 'modules' );
writableCell( 'administrator/templates' );
writableCell( 'templates' );
writableCell( 'language' );
writableCell( 'mambots' );
writableCell( 'mambots/content' );
writableCell( 'mambots/search' );

?>
</table>
<?php 
}
?>
