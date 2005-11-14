<?php
/**
* @version $Id: installer.class.php 386 2005-10-08 01:01:26Z Levis $
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

/**
* Installer class
* @package Joomla
* @subpackage Installer
* @abstract
*/
class mosInstaller {
	// name of the XML file with installation information
	var $i_installfilename	= "";
	var $i_installarchive	= "";
	var $i_installdir		= '';
	var $i_iswin			= false;
	var $i_errno			= 0;
	var $i_error			= '';
	var $i_installtype		= '';
	var $i_unpackdir		= '';
	var $i_docleanup		= true;
	var $msg			= '';

	/** @var string The directory where the element is to be installed */
	var $i_elementdir 		= '';
	/** @var string The name of the Joomla! element */
	var $i_elementname 		= '';
	/** @var string The name of a special atttibute in a tag */
	var $i_elementspecial 	= '';
	/** @var object A DOMIT XML document */
	var $i_xmldoc			= null;
	var $i_hasinstallfile 	= null;
	var $i_installfile 		= null;

	/**
	* Constructor
	*/
	function mosInstaller() {
		$this->i_iswin = (substr(PHP_OS, 0, 3) == 'WIN');
	}
	/**
	* Uploads and unpacks a file
	* @param string The uploaded package filename or install directory
	* @param boolean True if the file is an archive file
	* @return boolean True on success, False on error
	*/
	function upload($p_filename = null, $p_unpack = true) {
		$this->i_iswin = (substr(PHP_OS, 0, 3) == 'WIN');
		$this->installArchive( $p_filename );

		if ($p_unpack) {
			if ($this->extractArchive()) {
				return $this->findInstallFile();
			} else {
				return false;
			}
		}
	}

	/**
	* Description: Gets a file name out of a url
	*/
	function getFilenameFromURL($url) {
		if(is_string($url)) {
			$parts = split('/', $url);
			return $parts[count($parts)-1];
		}
		return 0;
	}	
	
	/**
	* Downloads a package
	* @param string URL of file to download
	* @param string Download target
	*/
	function downloadPackage($url,$target=false) {
		global $mosConfig_absolute_path,$_LANG,$mainframe;
		$php_errormsg = 'Error Unknown';
		ini_set('track_errors',true);
		
		// Open remote server
		$input_handle = @fopen($url, "r"); // or die("Remote server connection failed");
		if (!$input_handle) { 
			$this->setError(42, 'Remote Server connection failed: ' . $php_errormsg);
			return false; 
		}
		if(!$target) {
			$target = $mosConfig_absolute_path . '/media/' . $this->getFilenameFromURL($url);
		}	
		$output_handle = fopen($target, "wb"); // or die("Local output opening failed");
		if (!$output_handle) { 
			$this->setError(43, 'Local output opening failed: ' . $php_errormsg);
			return false; 
		}
		$contents = '';
		
		while (!feof($input_handle)) {
  			$contents = fread($input_handle, 4096);
			if($contents == false) { $this->setError(44,'Failed reading network resource: ' . $php_errormsg); return false; }
			$write_res = fwrite($output_handle, $contents);
			if($write_res == false) { $this->setError(45,'Cannot write to local target: ' . $php_errormsg); return false; }
		}
		fclose($output_handle);
		fclose($input_handle);	
		$this->installArchive( $this->getFilenameFromURL($url) );
		return $target;	
	}


	/**
	* Extracts the package archive file
	* @return boolean True on success, False on error
	*/
	function extractArchive() {
		global $mosConfig_absolute_path;
		global $_LANG;

		$base_Dir 		= mosPathName( $mosConfig_absolute_path . '/media' );

		$archivename 	= $base_Dir . $this->installArchive();
		$tmpdir 		= uniqid( 'install_' );

		$extractdir 	= mosPathName( $base_Dir . $tmpdir );
		$archivename 	= mosPathName( $archivename, false );

		$this->unpackDir( $extractdir );
		if (eregi( '.zip$', $archivename )) {
			// Extract functions
			jimport('pcl.pclzip');
			jimport('pcl.pclerror');
			//jimport('pcl.pcltrace');
			//jimport('pcl.pcltar');
			$zipfile = new PclZip( $archivename );
			if($this->isWindows()) {
				define('OS_WINDOWS',1);
			} else {
				define('OS_WINDOWS',0);
			}

			$ret = $zipfile->extract( PCLZIP_OPT_PATH, $extractdir );
			if($ret == 0) {
				$this->setError( 1, $_LANG->_( 'Unrecoverable error' ) .' "'.$zipfile->errorName(true).'"' );
				return false;
			}
		} else {
			jimport('archive.Tar');
			$archive = new Archive_Tar( $archivename );
			$archive->setErrorHandling( PEAR_ERROR_PRINT );

			if (!$archive->extractModify( $extractdir, '' )) {
				$this->setError( 1, $_LANG->_( 'Extract Error' ) );
				return false;
			}
		}
	
		$this->installDir( $extractdir );
		
		// Try to find the correct install dir. in case that the package have subdirs
		// Save the install dir for later cleanup
		$filesindir = mosReadDirectory( $this->installDir(), '' );

		if (count( $filesindir ) == 1) {
			if (is_dir( $extractdir . $filesindir[0] )) {
				$this->installDir( mosPathName( $extractdir . $filesindir[0] ) );
			}
		}
		return true;
	}
	/**
	* Tries to find the package XML file
	* @return boolean True on success, False on error
	*/
	function findInstallFile() {
		global $_LANG;

		$found = false;
		// Search the install dir for an xml file
		$files = mosReadDirectory( $this->installDir(), '.xml$', true, true );

		if (count( $files ) > 0) {
			foreach ($files as $file) {
				$packagefile = $this->isPackageFile( $file );
				if (!is_null( $packagefile ) && !$found ) {
					$this->xmlDoc( $packagefile );
					return true;
				}
			}
			$this->setError( 1, $_LANG->_( 'ERRORJOSXMLSETUP' ) );
			return false;
		} else {
			$this->setError( 1, $_LANG->_( 'ERRORXMLSETUP' ) );
			return false;
		}
	}
	/**
	* @param string A file path
	* @return object A DOMIT XML document, or null if the file failed to parse
	*/
	function isPackageFile( $p_file ) {
		$xmlDoc =& JFactory::getXMLParser();
		$xmlDoc->resolveErrors( true );

		if (!$xmlDoc->loadXML( $p_file, false, true )) {
			return null;
		}
		$root = &$xmlDoc->documentElement;

		if ($root->getTagName() != 'mosinstall') {
			return null;
		}
		// Set the type
		$this->installType( $root->getAttribute( 'type' ) );
		$this->installFilename( $p_file );
		return $xmlDoc;
	}
	/**
	* Loads and parses the XML setup file
	* @return boolean True on success, False on error
	*/
	function readInstallFile() {
		global $_LANG;

		if ($this->installFilename() == "") {
			$this->setError( 1, $_LANG->_( 'No filename specified' ) );
			return false;
		}

		$this->i_xmldoc =& JFactory::getXMLParser();
		$this->i_xmldoc->resolveErrors( true );
		if (!$this->i_xmldoc->loadXML( $this->installFilename(), false, true )) {
			return false;
		}
		$root = &$this->i_xmldoc->documentElement;

		// Check that it's am installation file
		if ($root->getTagName() != 'mosinstall') {
			$this->setError( 1, $_LANG->_( 'File' ) .': "' . $this->installFilename() . '" '. $_LANG->_( 'is not a valid Joomla! installation file' ) );
			return false;
		}

		$this->installType( $root->getAttribute( 'type' ) );
		return true;
	}
	/**
	* Abstract install method
	*/
	function install() {
		global $_LANG;
		die( $_LANG->_( 'Method "install" cannot be called by class' ) .' ' . strtolower(get_class( $this )) );
	}
	/**
	* Abstract uninstall method
	*/
	function uninstall() {
		global $_LANG;
		die( $_LANG->_( 'Method "uninstall" cannot be called by class' ) .' ' . strtolower(get_class( $this )) );
	}
	/**
	* return to method
	*/
	function returnTo( $option, $element ) {
		return "index2.php?option=$option&element=$element";
	}
	/**
	* @param string Install from directory
	* @param string The install type
	* @return boolean
	*/
	function preInstallCheck( $p_fromdir, $type ) {
		global $_LANG;

		if (!is_null($p_fromdir)) {
			$this->installDir($p_fromdir);
		}

		if (!$this->installfile()) {
			$this->findInstallFile();
		}

		if (!$this->readInstallFile()) {
			$this->setError( 1, $_LANG->_( 'Installation file not found' ) .':<br />' . $this->installDir() );
			return false;
		}

		if ($this->installType() != $type) {
			$this->setError( 1, $_LANG->_( 'XML setup file is not for a' ) .' "'.$type.'".' );
			return false;
		}

		// In case there where an error doring reading or extracting the archive
		if ($this->errno()) {
			return false;
		}

		return true;
	}
	/**
	* @param string The tag name to parse
	* @param string An attribute to search for in a filename element
	* @param string The value of the 'special' element if found
	* @param boolean True for Administrator components
	* @return mixed Number of file or False on error
	*/
	function parseFiles( $tagName='files', $special='', $specialError='', $adminFiles=0 ) {
		global $mosConfig_absolute_path;
		global $_LANG;

		// Find files to copy
		$xmlDoc =& $this->xmlDoc();
		$root =& $xmlDoc->documentElement;

		$files_element =& $root->getElementsByPath( $tagName, 1 );
		if (is_null( $files_element )) {
			return 0;
		}

		if (!$files_element->hasChildNodes()) {
			// no files
			return 0;
		}
		$files = $files_element->childNodes;
		$copyfiles = array();
		if (count( $files ) == 0) {
			// nothing more to do
			return 0;
		}

		if ($folder = $files_element->getAttribute( 'folder' )) {
			$temp = mosPathName( $this->unpackDir() . $folder );
			if ($temp == $this->installDir()) {
				// this must be only an admin component
				$installFrom = $this->installDir();
			} else {
				$installFrom = mosPathName( $this->installDir() . $folder );
			}
		} else {
			$installFrom = $this->installDir();
		}

		foreach ($files as $file) {
			if (basename( $file->getText() ) != $file->getText()) {
				$newdir = dirname( $file->getText() );

				if ($adminFiles){
					if (!mosMakePath( $this->componentAdminDir(), $newdir )) {
						$this->setError( 1, $_LANG->_( 'Failed to create directory' ) .' "'. ($this->componentAdminDir()) . $newdir .'"' );
						return false;
					}
				} else {
					if (!mosMakePath( $this->elementDir(), $newdir )) {
						$this->setError( 1, $_LANG->_( 'Failed to create directory' ) .' "'. ($this->elementDir()) . $newdir .'"' );
						return false;
					}
				}
			}
			$copyfiles[] = $file->getText();

			// check special for attribute
			if ($file->getAttribute( $special )) {
				$this->elementSpecial( $file->getAttribute( $special ) );
			}
		}

		if ($specialError) {
			if ($this->elementSpecial() == '') {
				$this->setError( 1, $specialError );
				return false;
			}
		}

		if ($tagName == 'media') {
			// media is a special tag
			$installTo = mosPathName( $mosConfig_absolute_path . '/images/stories' );
		} else if ($adminFiles) {
			$installTo = $this->componentAdminDir();
		} else {
			$installTo = $this->elementDir();
		}
		$result = $this->copyFiles( $installFrom, $installTo, $copyfiles );

		return $result;
	}
	/**
	* @param string Source directory
	* @param string Destination directory
	* @param array array with filenames
	* @param boolean True is existing files can be replaced
	* @return boolean True on success, False on error
	*/
	function copyFiles( $p_sourcedir, $p_destdir, $p_files, $overwrite=false ) {
		global $_LANG;

		if (is_array( $p_files ) && count( $p_files ) > 0) {
			foreach($p_files as $_file) {
				$filesource	= mosPathName( mosPathName( $p_sourcedir ) . $_file, false );
				$filedest	= mosPathName( mosPathName( $p_destdir ) . $_file, false );

				if (!file_exists( $filesource )) {
					$this->setError( 1, $_LANG->_( 'File' ) .' '. $filesource .' '. $_LANG->_( 'does not exist!' ) );
					return false;
				} else if (file_exists( $filedest ) && !$overwrite) {
					$this->setError( 1, $_LANG->_( 'There is already a file called' ) .' '. $filedest .' '. $_LANG->_( 'WARNSAME' ) );
					return false;
				} else {
					if( !( copy($filesource,$filedest) && mosChmod($filedest) ) ) {
						$this->setError( 1, $_LANG->_( 'Failed to copy file' ) .': '. $filesource .' '. $_LANG->_( 'to' ) .' '. $filedest );
						return false;
					}
				}
			}
		} else {
			return false;
		}
		return count( $p_files );
	}
	/**
	* Copies the XML setup file to the element Admin directory
	* Used by Components/Modules/Mambot Installer Installer
	* @return boolean True on success, False on error
	*/
	function copySetupFile( $where='admin' ) {
		if ($where == 'admin') {
			return $this->copyFiles( $this->installDir(), $this->componentAdminDir(), array( basename( $this->installFilename() ) ), true );
		} else if ($where == 'front') {
			return $this->copyFiles( $this->installDir(), $this->elementDir(), array( basename( $this->installFilename() ) ), true );
		}
	}

	/**
	* @param int The error number
	* @param string The error message
	*/
	function setError( $p_errno, $p_error ) {
		$this->errno( $p_errno );
		$this->error( $p_error );
	}
	/**
	* @param boolean True to display both number and message
	* @param string The error message
	* @return string
	*/
	function getError($p_full = false) {
		if ($p_full) {
			return $this->errno() . " " . $this->error();
		} else {
			return $this->error();
		}
	}
	/**
	* @param string The name of the property to set/get
	* @param mixed The value of the property to set
	* @return The value of the property
	*/
	function &setVar( $name, $value=null ) {
		if (!is_null( $value )) {
			$this->$name = $value;
		}
		return $this->$name;
	}

	function installFilename( $p_filename = null ) {
		if(!is_null($p_filename)) {
			if($this->isWindows()) {
				$this->i_installfilename = str_replace('/','\\',$p_filename);
			} else {
				$this->i_installfilename = str_replace('\\','/',$p_filename);
			}
		}
		return $this->i_installfilename;
	}

	function installType( $p_installtype = null ) {
		return $this->setVar( 'i_installtype', $p_installtype );
	}

	function error( $p_error = null ) {
		return $this->setVar( 'i_error', $p_error );
	}

	function &xmlDoc( $p_xmldoc = null ) {
		return $this->setVar( 'i_xmldoc', $p_xmldoc );
	}

	function installArchive( $p_filename = null ) {
		return $this->setVar( 'i_installarchive', $p_filename );
	}

	function installDir( $p_dirname = null ) {
		return $this->setVar( 'i_installdir', $p_dirname );
	}

	function unpackDir( $p_dirname = null ) {
		return $this->setVar( 'i_unpackdir', $p_dirname );
	}

	function isWindows() {
		return $this->i_iswin;
	}

	function errno( $p_errno = null ) {
		return $this->setVar( 'i_errno', $p_errno );
	}

	function hasInstallfile( $p_hasinstallfile = null ) {
		return $this->setVar( 'i_hasinstallfile', $p_hasinstallfile );
	}

	function installfile( $p_installfile = null ) {
		return $this->setVar( 'i_installfile', $p_installfile );
	}

	function elementDir( $p_dirname = null )	{
		return $this->setVar( 'i_elementdir', $p_dirname );
	}

	function elementName( $p_name = null )	{
		return $this->setVar( 'i_elementname', $p_name );
	}
	function elementSpecial( $p_name = null )	{
		return $this->setVar( 'i_elementspecial', $p_name );
	}
}

function cleanupInstall( $userfile_name, $resultdir) {
	global $mosConfig_absolute_path;

	if (file_exists( $resultdir )) {
		deldir( $resultdir );
		unlink( mosPathName( $mosConfig_absolute_path . '/media/' . $userfile_name, false ) );
	}
}

function deldir( $dir ) {
	$current_dir = opendir( $dir );
	$old_umask = umask(0);
	while ($entryname = readdir( $current_dir )) {
		if ($entryname != '.' and $entryname != '..') {
			if (is_dir( $dir . $entryname )) {
				deldir( mosPathName( $dir . $entryname ) );
			} else {
                @chmod($dir . $entryname, 0777);
				unlink( $dir . $entryname );
			}
		}
	}
	umask($old_umask);
	closedir( $current_dir );
	return rmdir( $dir );
}
?>
