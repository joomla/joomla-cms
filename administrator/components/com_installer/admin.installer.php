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
require_once( $mainframe->getPath( 'admin_html' ) );
//require_once( $mainframe->getPath( 'class' ) );

$element 	= mosGetParam( $_REQUEST, 'element', '' );
$client 	= mosGetParam( $_REQUEST, 'client', '' );
$path 		= JPATH_ADMINISTRATOR . "/components/com_installer/$element/$element.php";

jimport('joomla.installers.factory');

// ensure user has access to this function
if (!$acl->acl_check( 'com_installer', 'installer', 'users', $my->usertype ) ) {
	mosRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
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
			$path = JPATH_ADMINISTRATOR . "/components/com_installer/$element/$element.php";

			if (file_exists( $path )) {
				require $path;
			} else {
				doInstaller();
				//echo JText::_( 'Installer not found for element' ) .' ['. $element .']';
			}
		} else {
			doInstaller();
			//echo JText::_( 'Installer not available for element' ) .' ['. $element .']';
		}
		break;
}


/**
* @param string The class name for the installer
* @param string The URL option
* @param string The element name
*/
function uploadPackage( $option ) {
	
	$installerFactory = new JInstallerFactory();
	$installer = new mosInstaller(); // Create a blank installer until we work out what the file is!
	// Check if file uploads are enabled
	if (!(bool)ini_get('file_uploads')) {
		HTML_installer::showInstallMessage( JText::_( 'WARNINSTALLFILE' ),
			JText::_( 'Installer - Error' ), $installer->returnTo( $option, $element, $client ) );
		exit();
	}

	// Check that the zlib is available
	if(!extension_loaded('zlib')) {
		HTML_installer::showInstallMessage( JText::_( 'WARNINSTALLZLIB' ), JText::_( 'Installer - Error' ), $installer->returnTo( $option, $element, $client ) );
		exit();
	}

	$userfile = mosGetParam( $_FILES, 'userfile', null );

	if (!$userfile) {
		HTML_installer::showInstallMessage( JText::_( 'No file selected' ), JText::_( 'Upload new element - error' ),
			$installer->returnTo( $option, $element, $client ));
		exit();
	}

	$userfile_name = $userfile['name'];
	$client = '';
	$msg = '';
	$resultdir = uploadFile( $userfile['tmp_name'], $userfile['name'], $msg );
	if ($resultdir !== false) {
		if (!$installer->upload( $userfile['name'] )) {
        	$msgStr = sprintf( JText::_( 'Upload Failed' ), $element );
			HTML_installer::showInstallMessage( $installer->getError(), $msgStr,
				$installer->returnTo( $option, $element, $client ) );
		}
		$installdir = $installer->i_installdir;
		$element = $installerFactory->detectType($installer->unpackDir());
		$installerFactory->createClass($element);
                $installer = $installerFactory->getClass();
		$ret = $installer->install($installdir);
        $retStr = $ret ? JText::_( 'Success' ) : JText::_( 'Failed' );
    	$msgStr = sprintf( JText::_( 'UPLOADSUCCESSOR' ), $element, $retStr );
    	
		HTML_installer::showInstallMessage( $installer->getError(), $msgStr,
			$installer->returnTo( $option, $element, $client ) );
		cleanupInstall( $userfile['name'], $installer->unpackDir() );
	} else {
    	$msgStr = sprintf( JText::_( 'Upload Error' ), $element );
		HTML_installer::showInstallMessage( $msg, $msgStr,
			$installer->returnTo( $option, $element, $client ) );
	}
}

/**
* Install a template from a directory
* @param string The URL option
*/
function installFromDirectory( $option ) {
	global $classMap;

	$client = '';
	$userfile = mosGetParam( $_REQUEST, 'userfile', '' );

	if (!$userfile) {
		mosRedirect( "index2.php?option=$option&element=$element", JText::_( 'Please select a directory' ) );
	}
	$installerFactory = new JInstallerFactory();
	$installer = new mosInstaller();
	$installer->installDir($userfile);
	if(!$installer->findInstallFile()) {
    	$msg = sprintf( JText::_( 'Unable to find valid XML install' ), $userfile );
    	$msgStr = sprintf( JText::_( 'Install Detection Error' ), $element );
		HTML_installer::showInstallMessage( $msg, $msgStr,
			$installer->returnTo( $option, $element, $client ) );
	}
	
	$element = $installerFactory->detectType($userfile.'/');
	$installerClass = $classMap[$element];
	if(!$installerClass) {
    	$msg = sprintf( JText::_( 'Unable to detect the type of install' ), $userfile );
    	$msgStr = sprintf( JText::_( 'Install Detection Error' ), $element );
		HTML_installer::showInstallMessage( $msg, $msgStr,
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
	HTML_installer::showInstallMessage( $installer->getError(), JText::_( 'Upload new' ) .' '.$element.' - '.($ret ? JText::_( 'Success' ) : JText::_( 'Error' )), $installer->returnTo( $option, $element, $client ) );
}

/**
* Install an element from a URL
* @param string The URL
*/
function installFromUrl($option) {
	
	$installerFactory = new JInstallerFactory();
	$userfile = mosGetParam( $_REQUEST, 'userfile', '' );
	$client = '';
	if(!$userfile) {
		mosRedirect( "index2.php?option=$option", JText::_( 'Please enter a URL' ) );
	}
	$installer = $installerFactory->webInstall( $userfile );
	$element = $installerFactory->getType();
    $ret = $installer->msg;
    
    $retStr = $ret ? JText::_( 'Success' ) : JText::_( 'Error' );
	$msg = sprintf( JText::_( 'Install new element' ), $element, $retStr );
	
	HTML_installer::showInstallMessage(	$installer->getError(), $msg, $installer->returnTo( $option, $element, $client ) );	
}

/**
*
* @param
*/
function removeElement( $installerClass, $option, $element, $client ) {

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

	mosRedirect( $installer->returnTo( $option, $element, $client ), $result ? JText::_( 'Success' ) .' '. $msg : JText::_( 'Failed' ) .' '. $msg );
}
/**
* @param string The name of the php (temporary) uploaded file
* @param string The name of the file to put in the temp directory
* @param string The message to return
*/
function uploadFile( $filename, $userfile_name, &$msg ) {
	global $mosConfig_absolute_path;

	$baseDir = mosPathName( $mosConfig_absolute_path . '/media' );

	if (file_exists( $baseDir )) {
		if (is_writable( $baseDir )) {
			if (move_uploaded_file( $filename, $baseDir . $userfile_name )) {
				if (mosChmod( $baseDir . $userfile_name )) {
					return true;
				} else {
					$msg = JText::_( 'WARNPERMISSIONS' );
				}
			} else {
				$msg = JText::_( 'Failed to move uploaded file to' );
			}
		} else {
			$msg = JText::_( 'UPLOADFAILEDNOTWRITABLE' );
		}
	} else {
		$msg = JText::_( 'UPLOADFAILEDNOTEXIST' );
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
	HTML_installer::showInstallForm( JText::_( 'Install new Extension' ), $option, 'element', '', dirname(__FILE__) );
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
