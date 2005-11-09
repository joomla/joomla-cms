<?php
/**
* @version $Id: template.class.php 819 2005-11-02 12:21:40Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @subpackage Installer
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
if (!$acl->acl_check( 'com_templates', 'manage', 'users', $GLOBALS['my']->usertype )) {
	mosRedirect( 'index2.php', $_LANG->_('ALERTNOTAUTH') );
}

/**
* Template installer
* @package Joomla
* @subpackage Installer
*/
class mosInstallerTemplate extends mosInstaller {
	/**
	* Custom install method
	* @param boolean True if installing from directory
	*/
	function install( $p_fromdir = null ) {
		global $mosConfig_absolute_path,$database;
		global $_LANG;

		if (!$this->preInstallCheck( $p_fromdir, 'template' )) {
			return false;
		}

		$xmlDoc 	=& $this->xmlDoc();
		$mosinstall =& $xmlDoc->documentElement;

		$client = '';
		if ($mosinstall->getAttribute( 'client' )) {
			$validClients = array( 'administrator' );
			if (!in_array( $mosinstall->getAttribute( 'client' ), $validClients )) {
				$this->setError( 1, $_LANG->_( 'Unknown client type' ) .' ['.$mosinstall->getAttribute( 'client' ).']' );
				return false;
			}
			$client = 'admin';
		}

		// Set some vars
		$e = &$mosinstall->getElementsByPath( 'name', 1 );
		$this->elementName($e->getText());
		$this->elementDir( mosPathName( $mosConfig_absolute_path
		. ($client == 'admin' ? '/administrator' : '')
		. '/templates/' . strtolower(str_replace(" ","_",$this->elementName())))
		);

		if (!file_exists( $this->elementDir() ) && !mosMakePath( $this->elementDir() )) {
			$this->setError(1, $_LANG->_( 'Failed to create directory' ) .' "' . $this->elementDir() . '"' );
			return false;
		}

		if ($this->parseFiles( 'files' ) === false) {
			return false;
		}
		if ($this->parseFiles( 'images' ) === false) {
			return false;
		}
		if ($this->parseFiles( 'css' ) === false) {
			return false;
		}
		if ($this->parseFiles( 'media' ) === false) {
			return false;
		}
		if ($e = &$mosinstall->getElementsByPath( 'description', 1 )) {
			$this->setError( 0, $this->elementName() . '<p>' . $e->getText() . '</p>' );
		}

		return $this->copySetupFile('front');
	}
	/**
	* Custom install method
	* @param int The id of the module
	* @param string The URL option
	* @param int The client id
	*/
	function uninstall( $id, $option, $client=0 ) {
		global $database, $mosConfig_absolute_path;
		global $_LANG;

		// Delete directories
		$path = $mosConfig_absolute_path
		. ($client == 'admin' ? '/administrator' : '' )
		. '/templates/' . $id;

		$id = str_replace( '..', '', $id );
		if (trim( $id )) {
			if (is_dir( $path )) {
				return deldir( mosPathName( $path ) );
			} else {
				HTML_installer::showInstallMessage( $_LANG->_( 'Directory does not exist, cannot remove files' ), $_LANG->_( 'Uninstall - error' ),
					$this->returnTo( $option, 'template', $client ) );
			}
		} else {
			HTML_installer::showInstallMessage( $_LANG->_( 'Template id is empty, cannot remove files' ), $_LANG->_( 'Uninstall - error' ),
				$this->returnTo( $option, 'template', $client ) );
			exit();
		}
	}
	/**
	* return to method
	*/
	function returnTo( $option, $element, $client ) {
		return "index2.php?option=com_templates&client=$client";
	}
}
?>