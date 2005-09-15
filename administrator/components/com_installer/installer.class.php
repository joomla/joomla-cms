<?php
/**
* @version $Id: installer.class.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Installer
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
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
class mosInstaller extends mosAbstractLog {
	// name of the XML file with installation information
	var $i_installXML	= '';
	var $i_installarchive	= '';
	var $i_installdir		= '';
	var $i_errno			= 0;
	var $i_error			= '';
	var $i_unpackdir		= '';
	var $i_docleanup		= true;

	/** @var object Database connector */
	var $_db = null;

	/** @var string The directory where the element is to be installed */
	var $i_elementdir = '';
	/** @var string The name of the Joomla! element */
	var $i_elementname = '';
	/** @var string The name of a special atttibute in a tag */
	var $i_elementspecial = '';
	/** @var object A DOMIT XML document */
	var $i_xmldoc = null;
	/** @var string The element type */
	var $elementType = null;
	/** @var string The client for the element */
	var $elementClient = null;
	/** @var boolean True if existing files can be overwritten */
	var $allowOverwrite = false;
	/** @var boolean True if files are to be backed up */
	var $backupFiles = false;
	/** @var string Backup suffix for files */
	var $backupSuffix = '';

	var $i_hasinstallfile = null;
	var $i_installfile = null;

	var $rootTag = 'mosinstall';

	/**
	 * Constructor
	 */
	function mosInstaller() {
		parent::__constructor();
		$this->__constructor();
	}

	/**
	 * Generic constructor
	 */
	function __constructor() {
		$this->_db = $GLOBALS['database'];
		$this->_initFormFields();
	}

	/**
	 * @return string The base folder for the element
	 */
	function getBasePath() {
		die( 'Error: getBasePath must be derived for class' . $this->get_class() );
	}

	/**
	 * Initialise settings from required form fields
	 */
	function _initFormFields() {
		$suffix = mosGetParam( $_POST, 'backup_suffix', 'bak' );
		$this->backupSuffix( mosFS::makeSafe( $suffix ) );
		$this->allowOverwrite( mosGetParam( $_POST, 'overwrite', 0 ) );
		$this->backupFiles( mosGetParam( $_POST, 'backup', 0 ) );
	}

	/**
	 * Moves an uploaded file to a holding directory
	 * @param array An uploaded file array
	 */
	function uploadArchive( $userfile ) {
		global $_LANG, $mainframe;

		// Check if file uploads are enabled
		if (!(bool) ini_get( 'file_uploads' )) {
			$this->error( $_LANG->_( 'errorUploadsNotAvailable' ) );
			return false;
		}

		if (empty( $userfile )) {
			$this->error( $_LANG->_( 'No file selected' ) );
			return false;
		}

		// Check that the zlib is available
		if (mosFS::getExt( $userfile['name'] ) != 'tar' && !extension_loaded( 'zlib' )) {
			$this->error( $_LANG->_( 'errorZlibNotAvailable' ) );
			return false;
		}

		$srcFile = $userfile['tmp_name'];
		$destFile = $mainframe->getTempDirectory() . $userfile['name'];
		if (!mosFS::uploadFile( $userfile['tmp_name'], $destFile, $msg )) {
			$this->error( $msg );
			return false;
		}

		$this->installArchive( $destFile );
		return true;
	}

	/**
	* Extracts the package archive file
	* @return boolean True on success, False on error
	*/
	function extractArchive() {
		global $_LANG;

		$basePath = dirname( $this->installArchive() ) . '/';

		$extractPath = mosFS::getNativePath( $basePath . uniqid( 'install_' ) );
		$srcFile = $this->installArchive();

		$this->unpackDir( $extractPath );
		$this->installDir( $extractPath );

		mosFS::load( '/includes/mambo.files.archive.php' );
		mosArchiveFS::extract( $srcFile, $extractPath );

		return true;
	}

	/**
	 * Cleans up temporary install files
	 */
	function cleanupInstall() {
		if (file_exists( $this->unpackDir() )) {
			mosFS::deleteFolder( $this->unpackDir() );
		}
		if (file_exists( $this->installArchive() )) {
			mosFS::deleteFile( $this->installArchive() );
		}
	}

	/**
	 * @param string A file path
	 * @param object An XML object
	 * @return object A DOMIT XML document, or null if the file failed to parse
	 */
	function isPackageFile( $p_file, &$xmlDoc ) {
		$xmlDoc = new DOMIT_Lite_Document();
		$xmlDoc->resolveErrors( true );

		if (!$xmlDoc->loadXML( $p_file, false, true )) {
			return false;
		}
		$root = &$xmlDoc->documentElement;

		if ($root->getTagName() != $this->rootTag) {
			return false;
		}

		if ($root->getAttribute( 'type' ) != $this->elementType()) {
			return false;
		}

		return true;
	}

	/**
	* Tries to find the package XML file
	* @private
	* @return boolean True on success, False on error
	*/
	function _installPrepare() {
		global $_LANG;

		$found = false;
		// Search the install dir for an xml file
		$files = mosFS::listFiles( $this->installDir(), '\.xml$', true, true );

		if (count( $files ) > 0) {
			mosFS::load( '@domit' );

			foreach ($files as $file) {
				$xmlDoc = null;
				if ($this->isPackageFile( $file, $xmlDoc )) {
					$this->installDir( mosFS::getNativePath( dirname( $file ) ) );
					$this->installXML( $file );
					$this->xmlDoc( $xmlDoc );

					$root =& $xmlDoc->documentElement;
					$client = $root->getAttribute( 'client' );
					$client_id	= mosMainFrame::getClientID( $client );
					$this->elementClient( $client_id );

					$e = &$root->getElementsByPath( 'name', 1 );
					$this->elementName( $e->getText() );

					return true;
				}
			}
			$this->setError( 1, $_LANG->_( 'ERRORNOTFINDMAMBOXMLSETUPFILE' ) );
			return false;
		} else {
			$this->setError( 1, $_LANG->_( 'ERRORNOTFINDXMLSETUPFILE' ) );
			return false;
		}
	}

	/**
	 * Basic install method
	 * @public
	 * @param string The install directory (if not already set)
	 * @return boolean True if successful, false otherwise and an error is set
	 */
		function install( $installDirectory='' ) {
			global $_LANG, $_MAMBOTS;
			$result = false;
			$error = 0;
			$error_msg = '';
			$_MAMBOTS->loadBotGroup('system');
			$checks = $_MAMBOTS->trigger( 'onBeforeInstall',$installDirectory );

			foreach ($checks as $check) {
				if (is_a( $check, 'patError' )) {
					$error = 1;
					$error_msg .= $check->getMessage();
				}
			}
			if ($error) {
				$this->error( $error_msg );
				return false;
			} else {
				if ($this->_installPrepare()) {
					if ($this->_installCheck()) {
						if ($this->_installFiles()) {
							$_MAMBOTS->trigger( 'onAfterFileInstall' );
							if ($this->_installPreData()) {
								if ($this->_installData()) {
									$result = $this->_installPostData();
								}
							}
						}
					}
				}
			}
			$_MAMBOTS->trigger( 'onAfterInstall' );
			return $result;
	}

	/*
	 * Checks before installing
	 * @return boolean
	 */
	function _installCheck() {
		return true;
	}

	/**
	 * Installs the files for the element
	 * @protected
	 * @return boolean
	 */
	function _installFiles() {
		// no files to install
		return true;
	}

	/**
	 * Routines before data processing
	 * @protected
	 * @return boolean
	 */
	function _installPreData() {
		// no data to install
		return true;
	}

	/**
	 * Installs the data for the element
	 * @protected
	 * @return boolean
	 */
	function _installData() {
		// no data to install
		return true;
	}

	/**
	 * Routines after data processing
	 * @protected
	 * @return boolean
	 */
	function _installPostData() {
		// no data to install
		return true;
	}

	/**
	 * Basic install method
	 * @public
	 * @param string The name of the element
	 * @param int The client id
	 * @return boolean True if successful, false otherwise and an error is set
	 */
	function uninstall( $name, $client=0 ) {
		global $_LANG, $_MAMBOTS;

		if (empty( $name )) {
			$this->error( $_LANG->_( 'errorEmptyElementName' ) );
			return false;
		}
		$this->elementName( $name );
		$this->elementClient( $client );

		$error = 0;
		$error_msg = '';
		$_MAMBOTS->loadBotGroup('system');
		$checks = $_MAMBOTS->trigger( 'onBeforeUninstall', $name );
		foreach ($checks as $check) {
			if (is_a( $check, 'patError' )) {
				$error = 1;
				$error_msg .= $check->getMessage();
			}
		}
		if ($error) {
			$this->error( $error_msg );
			return false;
		} else {
			$result = false;
			if ($this->_uninstallCheck()) {
				if ($this->_uninstallFiles()) {
					$_MAMBOTS->trigger( 'onAfterFileUninstall' );
					$result = $this->_uninstallData();
				}
			}
		}
		$_MAMBOTS->trigger( 'onAfterUninstall' );

		return $result;
	}

	/**
	 * Checks before uninstalling
	 * @return boolean
	 */
	function _uninstallCheck() {
		return true;
	}

	/**
	 * Uninstalls the files for the element
	 * @protected
	 * @return boolean
	 */
	function _uninstallFiles() {
		// no files to install
		return true;
	}

	/**
	 * Uninstalls the data for the element
	 * @protected
	 * @abstract
	 * @return boolean
	 */
	function _uninstallData() {
		// no data to uninstall
		return true;
	}

	/**
	 * Deletes a folder
	 * @param string The folder path
	 * @return boolean True if successful
	 */
	 function _deleteFolder( $path ) {
		global $_LANG;
		if (is_dir( $path )) {
			if (is_writable( $path )) {
				if (mosFS::deleteFolder( $path )) {
					return true;
				} else {
					$this->error( $_LANG->_( 'errorDeletingDirectory' ) );
				}
			} else {
				$this->error( $_LANG->_( 'errorDirectoryWritable' ) );
			}
		} else {
			$this->error( $_LANG->_( 'errorDirectoryNotFound' ) );
		}
		return false;
	 }

	/**
	* @param array Array of src/dest pairs
	* @param boolean True is existing files can be replaced
	* @return boolean True on success, False on error
	*/
	function copyFiles( $p_files, $overwrite=null ) {
		global $_LANG;

		if (is_null( $overwrite )) {
			$overwrite = $this->allowOverwrite();
		}

		if (is_array( $p_files ) && count( $p_files ) > 0) {
			foreach($p_files as $_file) {
				$filesource	= mosFS::getNativePath( $_file[0], false );
				$filedest	= mosFS::getNativePath( $_file[1], false );
				$this->log( $filesource . ' -&gt; ' . $filedest );

				if (!mosFS::autocreatePath( dirname( $filedest ) )) {
					$this->setError( 1, $_LANG->sprintf( 'Failed to create directory "%"', $filedest ) );
					return false;
				}

				$destExists = file_exists( $filedest );
				if (!file_exists( $filesource )) {
					$this->setError( 1, $_LANG->_( 'File' ) ." ". $filesource ." ". $_LANG->_( 'does not exist!' ) );
					return false;
				} else if ($destExists && !$overwrite) {
					$this->setError( 1, $_LANG->_( 'There is already a file called' ) ." ". $filedest ." - ". $_LANG->_( 'ERRORTRYINSTALLCMTTWICE' ) );
					return false;
				} else {
					if ($destExists && $this->backupFiles() && $this->backupSuffix()) {
						// need to backup the destination file
						copy( $filedest, $filedest . '.' . $this->backupSuffix() );
					}
					if (!(copy($filesource,$filedest) && mosFS::CHMOD( $filedest ))) {
						$this->setError( 1, $_LANG->_( 'Failed to copy file' ) .": ". $filesource ." ". $_LANG->_( 'to' ) ." ". $filedest );
						return false;
					}
				}
			}
		}

		return count( $p_files );
	}

	/**
	 * Copies the XML setup file to the element Admin directory
	 * Used by Components/Modules/Mambot Installer Installer
	 * @return boolean True on success, False on error
	 */
	function copySetupFile() {
		$srcFile = $this->installDir() . basename( $this->installXML() );
		$destFile = $this->elementDir() . basename( $this->installXML() );

		return $this->copyFiles( array( array( $srcFile, $destFile ) ), true );
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
	function setVar( $name, $value=null ) {
		if (!is_null( $value )) {
			$this->$name = $value;
		}
		return $this->$name;
	}

	/**
	 * Sets and creates the install directory
	 * @param string The path name
	 */
	function setElementDir( $path ) {
		$this->elementDir( mosFS::getNativePath( $path ) );

		if (file_exists( $path )) {
			return true;
		} else if (mosFS::autocreatePath( $path )) {
			return true;
		} else {
			$this->setError( 1, $_LANG->sprintf( 'Failed to create directory "%"' ), $path );
			return false;
		}
	}

	function installXML( $p_filename = null ) {
		if(!is_null($p_filename)) {
			$this->i_installXML = mosFS::getNativePath( $p_filename, false );
		}
		return $this->i_installXML;
	}

	function elementType( $elementType = null ) {
		return $this->setVar( 'elementType', $elementType );
	}

	function elementClient( $elementType = null ) {
		return $this->setVar( 'elementClient', $elementType );
	}

	function error( $p_error = null ) {
		return $this->setVar( 'i_error', $p_error );
	}

	function xmlDoc( $p_xmldoc = null ) {
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

	function errno( $p_errno = null ) {
		return $this->setVar( 'i_errno', $p_errno );
	}

	function elementDir( $p_dirname = null )	{
		if (!is_null( $p_dirname )) {
			$this->i_elementdir = mosFS::getNativePath( $p_dirname );
		}
		return $this->i_elementdir;
	}

	function elementName( $p_name = null )	{
		return $this->setVar( 'i_elementname', $p_name );
	}
	function elementSpecial( $p_name = null )	{
		return $this->setVar( 'i_elementspecial', $p_name );
	}
	function allowOverwrite( $value=null )	{
		return $this->setVar( 'allowOverwrite', $value );
	}
	function backupFiles( $value=null )	{
		return $this->setVar( 'backupFiles', $value );
	}
	function backupSuffix( $value=null )	{
		return $this->setVar( 'backupSuffix', $value );
	}
}
?>
