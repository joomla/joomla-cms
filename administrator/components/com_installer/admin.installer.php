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
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.installer.installer');

require_once( $mainframe->getPath( 'admin_html' ) );

$element 	= mosGetParam( $_REQUEST, 'element', '' );
$client 	= mosGetParam( $_REQUEST, 'client', '' );

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

	default:
		if (array_key_exists ( $element, $classMap ) )
		{
			$path = dirname(__FILE__). DS .$element.DS.$element.'.php';

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
function uploadPackage( $option )
{
	//global $mainframe;

	//$client = $mainframe->getClient();
	//$element = mosGetParam( $_REQUEST, 'element', '' );

	$installer = new JInstaller(); // Create a blank installer until we work out what the file is!

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

	if ($resultdir !== false)
	{
		if (!$installer->upload( $userfile['name'] )) {
        	$msgStr = sprintf( JText::_( 'Upload Failed' ), $element );
			HTML_installer::showInstallMessage( $installer->getError(), $msgStr,
				$installer->returnTo( $option, $element, $client ) );
		}

		$installdir = $installer->i_installdir;
		$element    = JInstallerHelper::detectType($installer->unpackDir());

        $installer = JInstaller::getInstance($element);

		$ret = $installer->install($installdir);

		$retStr = $ret ? JText::_( 'Success' ) : JText::_( 'Failed' );
    	$msgStr = sprintf( JText::_( 'UPLOADSUCCESSOR' ), $element, $retStr );

		HTML_installer::showInstallMessage( $installer->getError(), $msgStr,
		$installer->returnTo( $option, $element, $client ) );
		JInstallerHelper::cleanupInstall( $userfile['name'], $installer->unpackDir() );
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
function installFromDirectory( $option )
{
	global $classMap;

	$client = '';
	$userfile = mosGetParam( $_REQUEST, 'userfile', '' );
	//$element = mosGetParam( $_REQUEST, 'element', '' );

	if (!$userfile) {
		mosRedirect( "index2.php?option=$option&element=$element", JText::_( 'Please select a directory' ) );
	}

	$installer = new JInstaller();
	$installer->installDir($userfile);

	if(!$installer->findInstallFile()) {
    	$msg = sprintf( JText::_( 'Unable to find valid XML install' ), $userfile );
    	$msgStr = sprintf( JText::_( 'Install Detection Error' ), $element );
		HTML_installer::showInstallMessage( $msg, $msgStr,
		$installer->returnTo( $option, $element, $client ) );
	}

	$element = JInstallerHelper::detectType($userfile.'/');
	$installerClass = $classMap[$element];

	if(!$installerClass) {
    	$msg = sprintf( JText::_( 'Unable to detect the type of install' ), $userfile );
    	$msgStr = sprintf( JText::_( 'Install Detection Error' ), $element );
		HTML_installer::showInstallMessage( $msg, $msgStr,
		$installer->returnTo( $option, $element, $client ) );
		return;
	}

	$installer = JInstaller::getInstance($element);

	$path = JPath::clean( $userfile );
	if (!is_dir( $path )) {
		$path = dirname( $path );
	}

	$ret = $installer->install( $path );
	$retStr = $ret ? JText::_( 'Success' ) : JText::_( 'Error' );
	$msg = sprintf( JText::_( 'Upload new' ), $element, $retStr );

	HTML_installer::showInstallMessage( $installer->getError(), $msg, $installer->returnTo( $option, $element, $client ) );
}

/**
* Install an element from a URL
* @param string The URL
*/
function installFromUrl($option)
{
	$userfile = mosGetParam( $_REQUEST, 'userfile', '' );
	$client = '';

	if(!$userfile) {
		mosRedirect( "index2.php?option=$option", JText::_( 'Please enter a URL' ) );
	}

	$installer = new JInstaller();
	$location = $installer->downloadPackage($url);

	if(!$location) {
		return $processor;
	}

	JPath::setPermissions($location);

	$installer->extractArchive();

	$type = JInstallerHelper::detectType($installer->unpackDir());

	$installer = autoInstallGeneric('directory',$installer->unpackDir(), $type);
    $ret = $installer->msg;

    $retStr = $ret ? JText::_( 'Success' ) : JText::_( 'Error' );
	$msg = sprintf( JText::_( 'Install new element' ), $type, $retStr );

	HTML_installer::showInstallMessage(	$installer->getError(), $msg, $installer->returnTo( $option, $type, $client ) );
}

/**
*
* @param
*/
function removeElement( $installerClass, $option, $element, $client )
{
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
function uploadFile( $filename, $userfile_name, &$msg )
{
	$baseDir = JPath::clean( JPATH_SITE . DS .'media' );

	return JFile::upload($filename, $baseDir . $userfile_name, $msg );

//	if (file_exists( $baseDir )) {
//		if (is_writable( $baseDir )) {
//			if (move_uploaded_file( $filename, $baseDir . $userfile_name )) {
//				if (mosChmod( $baseDir . $userfile_name )) {
//					return true;
//				} else {
//					$msg = JText::_( 'WARNPERMISSIONS' );
//				}
//			} else {
//				$msg = JText::_( 'Failed to move uploaded file to' );
//			}
//		} else {
//			$msg = JText::_( 'UPLOADFAILEDNOTWRITABLE' );
//		}
//	} else {
//		$msg = JText::_( 'UPLOADFAILEDNOTEXIST' );
//	}
//	return false;
}

function &autoInstallGeneric($method=null,$data=null,$type=null)
{
	$msg = "SUCCESS";

	$installer = JInstaller::getInstance($type);

	switch($method)
	{
		case 'upload':
			$userfile = mosGetParam( $_FILES, 'userfile', null );
			if (!$installer->uploadArchive( $userfile )) {
				$msg = $installer->error();
			}
			if (!$installer->extractArchive()) {
				$msg = $installer->error();
			}
			break;
		default:
			$extractdir = $data;
			$installer->installDir( $extractdir );
            // Try to find the correct install dir. in case that the package have subdirs
          	// Save the install dir for later cleanup
			$filesindir = mosReadDirectory( $installer->installDir(), '' );

			if (count( $filesindir ) == 1) {
				if (is_dir( $extractdir . $filesindir[0] )) {
					$installer->installDir( JPath::clean( $extractdir . $filesindir[0] ) );
				}
			}
			break;
		}

		if (!$installer->install()) {
			//	$installer->cleanupInstall();
			$msg = $installer->error();
        }

		$installer->msg = $msg;
		return $installer;
	}

/**
* Unified intaller
*/
function doInstaller()
{
	global $option;
	HTML_installer::showInstallForm( $option, 'element', '', dirname(__FILE__) );
?>
<table class="content">
<?php
writableCell( 'media' );
writableCell( 'components' );
writableCell( 'modules' );
writableCell( 'templates' );
writableCell( 'language' );
writableCell( 'mambots' );
writableCell( 'mambots/content' );
writableCell( 'mambots/search' );
writableCell( 'images/stories' );
writableCell( 'administrator/components' );
writableCell( 'administrator/modules' );
writableCell( 'administrator/language' );
writableCell( 'administrator/templates' );
?>
</table>
<?php
}
?>
