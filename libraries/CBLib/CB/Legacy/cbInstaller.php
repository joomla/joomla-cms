<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 1:24 AM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;
use CBLib\Xml\SimpleXMLElement;

defined('CBLIB') or die();

/**
 * cbInstaller Class implementation
 * Installer class
 */
abstract class cbInstaller
{
	/*
	 * Name of the XML file with installation information
	 * @var string
	 */
	var $i_installfilename	= "";
	var $i_installarchive	= "";
	var $i_installdir		= "";
	var $i_iswin			= false;
	var $i_errno			= 0;
	var $i_error			= "";
	var $i_installtype		= "";
	var $i_unpackdir		= "";
	var $i_docleanup		= true;
	var $i_installmethod	= "";
	/**
	 * Directory where the element is to be installed
	 * @var string
	 */
	var $i_elementdir = '';
	/**
	 * Name of the Mambo element
	 * @var string
	 */
	var $i_elementname = '';
	/**
	 * Name of a special atttibute in a tag
	 * @var string The
	 */
	var $i_elementspecial = '';
	/**
	 * XML Document
	 * @var SimpleXMLElement
	 */
	var $i_xmldocument		= null;

	var $i_hasinstallfile = null;
	var $i_installfile = null;

	/**
	 * Constructor
	 */
	function __construct()
	{
		cbimport( 'cb.adminfilesystem' );
		$this->i_iswin = (substr(PHP_OS, 0, 3) == 'WIN');
	}

	/**
	 * Uploads and unpacks a file
	 *
	 * @param  string   $pluginFilename     The uploaded package filename or install directory
	 * @param  boolean  $doUnpack           True if the file is an archive file and be extracted
	 * @param  boolean  $doFindInstallFile  True if should try to find install-file (after extraction)
	 * @return boolean                      True on success, False on error
	 */
	function upload( $pluginFilename = null, $doUnpack = true, $doFindInstallFile = true )
	{
		$this->i_iswin = (substr(PHP_OS, 0, 3) == 'WIN');

		$this->installArchive( $pluginFilename );

		if ( ! $doUnpack ) {
			return true;
		}

		if ( ! $this->extractArchive() ) {
			return false;
		}

		if ( ! $doFindInstallFile ) {
			return true;
		}

		return $this->findInstallFile();
	}

	/**
	 * Extracts the package archive file
	 *
	 * @return boolean  True on success, False on error
	 */
	function extractArchive( )
	{
		global $_CB_framework;

		$base_Dir			=	_cbPathName( $_CB_framework->getCfg('tmp_path') );

		$archivename		=	$this->installArchive();
		$tmpdir				=	uniqid( 'install_' );

		$extractdir			=	_cbPathName( $base_Dir . $tmpdir );
		$archivename		=	_cbPathName( $archivename, false );

		$this->unpackDir( $extractdir );

		if ( preg_match( "/\\.zip\$/i", $archivename ) ) {
			// Extract functions
			$zipFile		=	new PclZip( $archivename );

			$ret			=	$zipFile->extract( PCLZIP_OPT_PATH, $extractdir );

			if( $ret == 0 ) {
				$this->setError( 1, 'Unrecoverable error "'.$zipFile->errorName(true).'"' );
				return false;
			}
		} else {
			$archive		=	new Archive_Tar( $archivename );

			$archive->setErrorHandling( PEAR_ERROR_PRINT );

			if ( ! $archive->extractModify( $extractdir, '' ) ) {
				$this->setError( 1, 'Extract Error' );
				return false;
			}
		}

		$this->installDir( $extractdir );

		// Try to find the correct install dir. in case that the package have subdirs
		// Save the install dir for later cleanup
		$filesindir			=	cbReadDirectory( $this->installDir(), '' );

		if ( ( count( $filesindir ) == 1 ) && is_dir( $extractdir . $filesindir[0] ) ) {
			$this->installDir( _cbPathName( $extractdir . $filesindir[0] ) );
		}

		return true;
	}

	/**
	 * Tries to find the package XML file
	 *
	 * @return boolean  True on success, False on error
	 */
	function findInstallFile( )
	{
		$found							=	false;

		// Search the install dir for an xml file
		$files							=	cbReadDirectory( $this->installDir(), '.xml$', true, false );

		if ( count( $files ) == 0 ) {
			$this->setError( 1, 'ERROR: Could not find an XML setup file in the package.' );

			return false;
		}

		foreach ( $files as $file ) {
			$packagefile				=	$this->isPackageFile( $this->installDir() . $file );

			if ( ! is_null( $packagefile ) && ! $found ) {
				$this->i_xmldocument	=	$packagefile;

				return true;
			}
		}
		$this->setError( 1, 'ERROR: Could not find a CB XML setup file in the package.' );

		return false;
	}

	/**
	 * Checks if $xmlFilename is a package file
	 *
	 * @param  string  $xmlFilename  A file path
	 * @return object A DOMIT XML document, or null if the file failed to parse
	 */
	function isPackageFile( $xmlFilename )
	{
		if ( ! file_exists( $xmlFilename ) ) {
			return null;
		}

		$xmlString	=	trim( file_get_contents( $xmlFilename ) );

		$element	=	new SimpleXMLElement( $xmlString );

		if ( count( $element->children() ) == 0 ) {
			return null;
		}

		if ( $element->getName() != 'cbinstall' ) {
			//echo "didn't find cbinstall";
			return null;
		}
		// Set the type
		$this->installType( $element->attributes( 'type' ) );

		$this->installFilename( $xmlFilename );

		return $element;
	}

	/**
	 * Loads and parses the XML setup file
	 *
	 * @return boolean  True on success, False on error
	 */
	function readInstallFile( )
	{
		if ( $this->installFilename() == '' ) {
			$this->setError( 1, 'No filename specified' );
			return false;
		}

		if ( file_exists( $this->installFilename() ) ) {
			$xmlString = trim( file_get_contents( $this->installFilename() ) );

			$this->i_xmldocument	=	new SimpleXMLElement( $xmlString );

			if ( count( $this->i_xmldocument->children() ) == 0 ) {
				return false;
			}
		}

		$main_element				=	$this->i_xmldocument;

		// Check that it's am installation file
		if ($main_element->getName() != 'cbinstall') {
			$this->setError( 1, 'File :"' . $this->installFilename() . '" is not a valid Joomla installation file' );

			return false;
		}

		$this->installType( $main_element->attributes( 'type' ) );

		return true;
	}

	/**
	 * Abstract install method
	 *
	 * @param  null|string  $fromDirectory            Directory of plugin to install
	 * @param  boolean      $InstallIntoDatabaseOnly  Install plugin database only
	 * @return boolean
	 */
	abstract function install( $fromDirectory = null, $InstallIntoDatabaseOnly = false );

	/**
	 * Plugin un-installer with best effort depending on what it finds.
	 *
	 * @param  int      $pluginId  Plugin id to uninstall
	 * @param  string   $option    Option request of component
	 * @return boolean             Success
	 */
	abstract function uninstall( $pluginId, $option );

	/**
	 * Compute return-to url
	 *
	 * @param  string  $option
	 * @param  string  $task
	 * @return string
	 */
	function returnTo( $option, $task )
	{
		global $_CB_framework;

		return $_CB_framework->backendUrl( 'index.php?option=' . $option . '&view=' . $task );
	}

	/**
	 * @param  string  $installationDirectory  Install from directory
	 * @param  string  $extensionType          Extension type
	 * @return boolean
	 */
	function preInstallCheck( $installationDirectory, $extensionType='plugin' )
	{

		if ( ! is_null( $installationDirectory ) ) {
			$this->installDir( $installationDirectory );
		}

		if ( ! $this->installFile() ) {
			$this->findInstallFile();
		}

		if ( ! $this->readInstallFile() ) {
			$this->setError( 1, CBTxt::Th( 'INSTALL_FILE_NOT_FOUND_IN_DIRECTORY', 'Installation file not found in [DIRECTORY].',
				array( '[DIRECTORY]' => htmlspecialchars( $this->installDir() ) ) ) );
			return false;
		}

		if ( trim( $this->installType() ) != trim( $extensionType ) ) {
			$this->setError( 1,CBTxt::Th( 'INSTALL_XML_SETUP_FILE_IS_NOT_FOR_AN_EXTENSION_TYPE', 'XML setup file is not for a "[EXTENTION_TYPE]".',
				array( '[EXTENTION_TYPE]' => htmlspecialchars( $extensionType ) ) ) );
			return false;
		}

		// In case there where an error during reading or extracting the archive:
		if ( $this->errorNumber() ) {
			return false;
		}

		return true;
	}

	/**
	 * Parse files
	 *
	 * @param  string       $tagName       The tag name to parse
	 * @param  string       $special       The value of the 'special' element if found
	 * @param  string       $specialError  Error html text (translated in case of errors)
	 * @return int|boolean                 Number of files (0...n) or False on error
	 */
	function parseFiles( $tagName = 'files', $special = '', $specialError = '' )
	{
		global $_CB_framework;

		// Find files to copy
		$cbInstallXML			=	$this->i_xmldocument;

		$filesElement			=	$cbInstallXML->getElementByPath( $tagName );

		if ( $filesElement === false ) {
			// no files-element, return 0 files found
			return 0;
		}

		if ( count( $filesElement->children() ) == 0 ) {
			// no files, thus return 0 files found
			return 0;
		}

		$copyFolders			=	array();
		$copyFiles				=	array();

		$folder					=	$filesElement->attributes( 'folder' );
		if ( $folder ) {
			$temp 				= _cbPathName( $this->unpackDir() . $folder );
			if ($temp == $this->installDir()) {
				// this must be only an admin component
				$installFrom	=	$this->installDir();
			} else {
				$installFrom	=	_cbPathName( $this->installDir() . $folder );
			}
		} else {
			$installFrom		=	$this->installDir();
		}

		foreach ( $filesElement->children() as $file ) {
			if ( basename( $file->data() ) != $file->data() ) {
				$newDirectory	=	dirname( $file->data() );

				if ( ! $this->createDirectoriesForPath( $this->elementDir(), $newDirectory ) ) {
					$this->setError( 1, CBTxt::Th( 'INSTALL_FAILED_TO_CREATE_DIRECTORY_DIR', 'Failed to create directory "[DIRECTORY_NAME]"',
						array( '[DIRECTORY_NAME]' => $this->elementDir() . $newDirectory ) ) );
					return false;
				}
			}
			if ( $file->getName() == 'foldername' ) {
				$copyFolders[]	=	$file->data();
			} else {
				$copyFiles[]	=	$file->data();
			}

			// check special for attribute
			if ( $special && $file->attributes( $special ) ) {
				$this->elementSpecial( $file->attributes( $special ) );
			}
		}

		if ( $specialError ) {
			if ( $this->elementSpecial() == '' ) {
				$this->setError( 1, $specialError );
				return false;
			}
		}

		if ( $tagName == 'media' ) {
			// media is a special tag
			$installTo			=	_cbPathName( $_CB_framework->getCfg('absolute_path') . '/images/stories' );		//TODO should that become /media ?
		} else {
			$installTo			=	$this->elementDir();
		}
		$result					=	$this->copyFiles( $installFrom, $installTo, $copyFolders, $copyFiles, $this->installMethod() );

		return $result;
	}

	/**
	 * @param  string    $sourceDirectory       Source directory
	 * @param  string    $destinationDirectory  Destination directory
	 * @param  string[]  $foldersToCopy         Folder names
	 * @param  string[]  $filesToCopy           File names
	 * @param  boolean   $overwrite             True is existing files can be replaced
	 * @return int|boolean                      int number of copied files on success, False on error
	 */
	function copyFiles( $sourceDirectory, $destinationDirectory, array $foldersToCopy, array $filesToCopy, $overwrite = false )
	{
		global $_CB_framework;

		$hasFolders				=	( count( $foldersToCopy ) > 0 );
		$hasFiles				=	( count( $filesToCopy ) > 0 );

		if ( ! ( $hasFolders || $hasFiles ) ) {
			return false;
		}

		$adminFS				=	cbAdminFileSystem::getInstance();
		$filePerms				=	$_CB_framework->getCfg( 'fileperms' );
		$dirPerms				=	$_CB_framework->getCfg( 'dirperms' );

		if ( $filePerms ) {
			$filePerms			=	octdec( $filePerms );
		} else {
			$filePerms			=	null;
		}

		if ( $dirPerms ) {
			$dirPerms			=	octdec( $dirPerms );
		} else {
			$dirPerms			=	null;
		}

		foreach( $foldersToCopy as $folder ) {
			$sourcePathDir		=	_cbPathName( _cbPathName( $sourceDirectory ) . $folder, false );
			$destinationPathDir	=	_cbPathName( _cbPathName( $destinationDirectory ) . $folder, false );

			if ( ! $adminFS->file_exists( $sourcePathDir ) ) {
				$this->setError( 1, CBTxt::Th( 'INSTALL_SOURCE_FOLDER_FOLDER_DOES_NOT_EXIST', 'Folder "[FOLDER]" does not exist!',
					array( '[FOLDER]' => htmlspecialchars( $sourcePathDir ) ) ) );
				return false;
			}

			if ( $adminFS->file_exists( $destinationPathDir ) && ( ! $overwrite ) ) {
				$this->setError( 1, CBTxt::Th( 'INSTALL_THERE_IS_ALREADY_FOLDER_FOLDER_TRYING_TO_INSTALL_TWICE', 'There is already a folder called "[FOLDER]" - Are you trying to install the same Plugin twice?',
					array( '[FOLDER]' => htmlspecialchars( $destinationPathDir ) ) ) );
				return false;
			}

			if ( ! $adminFS->copydir( $sourcePathDir, $destinationPathDir, $overwrite ) ) {
				$this->setError( 1, CBTxt::Th( 'INSTALL_FAILED_TO_COPY_FOLDER_SOURCE_DESTINATION', 'Failed to copy folder "[SOURCE_FOLDER]" to "[DESTINATION_FOLDER]"',
					array( '[SOURCE_FOLDER]' => htmlspecialchars( $sourcePathDir ), '[DESTINATION_FOLDER]' => htmlspecialchars( $destinationPathDir ) ) ) );
				return false;
			}

			$changePerms		=	( $adminFS->is_dir( $sourcePathDir ) && $dirPerms )
				|| ( $adminFS->is_file( $sourcePathDir ) && $filePerms );

			if ( $changePerms && ( ! $adminFS->chmoddir( $destinationPathDir, $dirPerms, $filePerms ) ) ) {
				$this->setError( 1, CBTxt::Th( 'INSTALL_FAILED_TO_SET_PERMISSIONS_ON_FOLDER_FOLDER', 'Failed to set permissions on (chmod) folder: [FOLDER]',
					array( '[FOLDER]' => htmlspecialchars( $destinationPathDir ) ) ) );
				return false;
			}
		}

		foreach( $filesToCopy as $_file ) {
			$sourceFile			=	_cbPathName( _cbPathName( $sourceDirectory ) . $_file, false );
			$destinationFile	=	_cbPathName( _cbPathName( $destinationDirectory ) . $_file, false );

			if ( ! $adminFS->file_exists( $sourceFile ) ) {
				$this->setError( 1, CBTxt::Th( 'INSTALL_FILE_FILENAME_DOES_NOT_EXIST', 'File "[FILENAME]" does not exist!',
					array( '[FILENAME]' => htmlspecialchars( $sourceFile ) ) ) );
				return false;
			}

			if ( $adminFS->file_exists( $destinationFile ) && ( ! $overwrite ) ) {
				$this->setError( 1, CBTxt::Th( 'INSTALL_THERE_IS_ALREADY_A_FILE_FILE_TRYING_TO_INSTALL_TWICE', 'There is already a file called "[FILE]" - Are you trying to install the same Plugin twice?',
					array( '[FILE]' => htmlspecialchars( $destinationFile ) ) ) );
				return false;
			}

			if ( ! $adminFS->copy( $sourceFile, $destinationFile ) ) {
				$this->setError( 1, CBTxt::Th( 'INSTALL_FAILED_TO_COPY_FILE_SOURCE_DESTINATION', 'Failed to copy file "[SOURCE_FILE]" to "[DESTINATION_FILE]"',
					array( '[SOURCE_FILE]' => htmlspecialchars( $sourceFile ), '[DESTINATION_FILE]' => htmlspecialchars( $destinationFile ) ) ) );
				return false;
			}

			if ( $adminFS->is_dir( $sourceFile ) && $dirPerms ) {
				$perms			=	$dirPerms;
			} elseif ( $adminFS->is_file( $sourceFile ) && $filePerms ) {
				$perms			=	$filePerms;
			} else {
				$perms			=	null;
			}

			if ( $perms && ( ! $adminFS->chmod( $destinationFile, $perms ) ) ) {
				$this->setError( 1, CBTxt::Th( 'INSTALL_FAILED_TO_SET_PERMISSIONS_ON_FILE_FILENAME', 'Failed to set permissions on (chmod) file: [FILENAME]',
					array( '[FILENAME]' => htmlspecialchars( $destinationFile ) ) ) );
				return false;
			}
		}

		return ( count( $foldersToCopy ) + count( $filesToCopy ) );
	}

	/**
	 * Copies the XML setup file to the element Admin directory
	 * Used by Plugin Installer
	 * @return boolean True on success, False on error
	 */
	function copySetupFile( )
	{
		return $this->copyFiles( $this->installDir(), $this->elementDir(), array(), array( basename( $this->installFilename() ) ), true );
	}

	/**
	 * Sets error number and message
	 *
	 * @param  int     $errorNumber       Error number
	 * @param  string  $htmlErrorMessage  Error message
	 * @return void
	 */
	function setError( $errorNumber, $htmlErrorMessage )
	{
		$this->errorNumber( $errorNumber );
		$this->errorMessage( $htmlErrorMessage );
	}

	/**
	 * Gets error message
	 *
	 * @return string
	 */
	function getError()
	{
		return $this->errorMessage();
	}

	/**
	 * Sets a non-null $value, or gets the set value if $value is null
	 *
	 * @param  string  $name   Name of the property to set/get
	 * @param  mixed   $value  Value of the property to set
	 * @return mixed           Value of the property
	 */
	protected function setVar( $name, $value = null )
	{
		if ( ! is_null( $value ) ) {
			$this->$name = $value;
		}

		return $this->$name;
	}

	/**
	 * Sets/gets install filename
	 *
	 * @param  string|null  $filename  Install file name
	 * @return string|null             Install file name
	 */
	protected function installFilename( $filename = null )
	{
		if ( ! is_null( $filename ) ) {
			if ( $this->isWindows() ) {
				$this->i_installfilename	=	str_replace( '/', '\\', $filename );
			} else {
				$this->i_installfilename	=	str_replace( '\\', '/', $filename );
			}
		}

		return $this->i_installfilename;
	}

	/**
	 * Sets/gets installed extension type
	 *
	 * @param  string|null  $installType  Extension type
	 * @return string|null                Extension type
	 */
	protected function installType( $installType = null )
	{
		return $this->setVar( 'i_installtype', $installType );
	}

	/**
	 * Sets/gets error message
	 *
	 * @param  string|null  $error  [optional] Error message to set
	 * @return string|null                     Error message
	 */
	protected function errorMessage( $error = null )
	{
		return $this->setVar( 'i_error', $error );
	}

	/**
	 * Sets/gets install archive location
	 *
	 * @param  string|null  $filename  Install archive location
	 * @return string|null             Install archive location
	 */
	protected function installArchive( $filename = null )
	{
		return $this->setVar( 'i_installarchive', $filename );
	}

	/**
	 * Sets/gets installation directory name
	 *
	 * @param  string|null  $directoryName  Installation directory name
	 * @return string|null                  Installation directory name
	 */
	public function installDir( $directoryName = null )
	{
		return $this->setVar( 'i_installdir', $directoryName );
	}

	/**
	 * Sets/gets unpacking directory name
	 *
	 * @param  string|null  $directoryName  Unpacking directory name
	 * @return string|null                  Unpacking directory name
	 */
	public function unpackDir( $directoryName = null )
	{
		return $this->setVar( 'i_unpackdir', $directoryName );
	}

	/**
	 * Sets/gets if cleanup should be done
	 *
	 * @param  string|null  $doCleanup
	 * @return string|null
	 */
	protected function doCleanup( $doCleanup = null )
	{
		return $this->setVar( 'i_docleanup', $doCleanup );
	}

	/**
	 * Sets/gets installation method (install or upgrade)
	 *
	 * @param  string|null  $method
	 * @return string|null
	 */
	protected function installMethod( $method = null )
	{
		return ( $this->setVar( 'i_installmethod', $method ) != 'install' );
	}

	/**
	 * Are we running on Windows ?
	 *
	 * @return boolean
	 */
	protected function isWindows()
	{
		return $this->i_iswin;
	}

	/**
	 * Sets/gets error number
	 *
	 * @param  string|null  $errorNumber
	 * @return string|null
	 */
	protected function errorNumber( $errorNumber = null )
	{
		return $this->setVar( 'i_errno', $errorNumber );
	}

	/**
	 * Sets/gets if we have an installation file
	 *
	 * @param  string|null  $hasInstallFile
	 * @return string|null
	 */
	protected function hasInstallFile( $hasInstallFile = null )
	{
		return $this->setVar( 'i_hasinstallfile', $hasInstallFile );
	}

	/**
	 * Sets/gets the install filename
	 *
	 * @param  string|null  $installFile
	 * @return string|null
	 */
	protected function installFile( $installFile = null )
	{
		return $this->setVar( 'i_installfile', $installFile );
	}

	/**
	 * Sets/gets the directory name where to install
	 *
	 * @param  string|null  $directoryName
	 * @return string|null
	 */
	protected function elementDir( $directoryName = null )
	{
		return $this->setVar( 'i_elementdir', $directoryName );
	}

	/**
	 * Sets/gets the name of the extension element being installed
	 *
	 * @param  string|null  $name
	 * @return string|null
	 */
	protected function elementName( $name = null )
	{
		return $this->setVar( 'i_elementname', $name );
	}

	/**
	 * Sets/gets the special installation manifest XML filename of the element being installed
	 *
	 * @param  string|null  $name
	 * @return string|null
	 */
	protected function elementSpecial( $name = null )
	{
		return $this->setVar( 'i_elementspecial', $name );
	}

	/**
	 * Warning: needs cbAdminFileSystem  File-system loaded to use
	 *
	 * @param  string  $base  An existing base path
	 * @param  string  $path  A path to create from the base path
	 * @param  int     $mode  Directory permissions
	 * @return boolean         True if successful
	 */
	function createDirectoriesForPath( $base, $path='', $mode = null )
	{
		global $_CB_framework;

		// convert windows paths
		$path					=	preg_replace( "/(\\/){2,}|(\\\\){1,}/",'/', $path );

		// check if dir exists
		if ( file_exists( $base . $path ) ) {
			return true;
		}

		// set mode
		$origmask				=	null;
		if ( isset( $mode ) ) {
			$origmask			=	@umask(0);
		} else {
			if ( $_CB_framework->getCfg( 'dirperms' ) == '' ) {
				// rely on umask
				$mode			=	0755;		// 0777;
			} else {
				$origmask		=	@umask( 0 );
				$mode			=	octdec( $_CB_framework->getCfg( 'dirperms' ) );
			}
		}

		$ret					=	true;
		if ( $path == '' ) {
			while ( substr( $base, -1, 1 ) == '/' ) {
				$base			=	substr( $base, 0, -1 );
			}
			$adminFS			=	cbAdminFileSystem::getInstance();
			$ret				=	$adminFS->mkdir( $base, $mode );
		} else {
			$parts				=	explode( '/', $path );
			$n					=	count( $parts );

			$path				=	$base;
			for ( $i = 0 ; $i < $n ; $i++ ) {
				$path			.=	$parts[$i];
				if ( ! file_exists( $path ) ) {
					$adminFS	=	cbAdminFileSystem::getInstance();
					if ( ! $adminFS->mkdir( $path, $mode ) ) {
						$ret	=	false;
						break;
					}
				}
				$path			.=	'/';
			}
		}
		if ( isset( $origmask ) ) {
			@umask( $origmask );
		}
		return $ret;
	}

	/**
	 * Cleans up (deletes) the installation directory if cleanup is needed
	 *
	 * @param  string  $manifestFilename  Name of the special manifest file
	 * @param  string  $directoryName     Name and path of the directory to clean up
	 * @return void
	 */
	function cleanupInstall( $manifestFilename, $directoryName )
	{
		if ( $this->doCleanup() && file_exists( $directoryName ) ) {
			$adminFS		=	cbAdminFileSystem::getInstance();
			$adminFS->deldir( $directoryName );
			if ( $manifestFilename ) {
				$adminFS->unlink( _cbPathName( $manifestFilename, false ) );
			}
		}
	}

	/**
	 * Remove all compiled files from APC cache and from PHP 5.5 OpCache
	 * @since 2.0.0
	 *
	 * @return void
	 */
	protected static function cleanOpcodeCaches( )
	{
		if ( function_exists( 'apc_clear_cache' ) ) {
			@apc_clear_cache();
		}
		if ( function_exists( 'opcache_reset' ) ) {
			@opcache_reset();
		}
	}

	/**
	 * Shows an installation message
	 *
	 * @param  string   $message
	 * @param  string   $title
	 * @param  boolean  $success
	 * @return void
	 */
	public static function showInstallMessage( $message, $title = null, $success = true )
	{
		global $_CB_framework;

		if ( ! $message ) {
			return;
		}

		$msg	=	'<div class="cbInstallMessage">'
				.		( $title ? '<div class="cbInstallMessageTitle"><strong>' . $title . '</strong></div>' : null )
				.		'<div class="cbInstallMessageMessage">' . $message . '</div>'
				.	'</div>';

		$_CB_framework->enqueueMessage( $msg, ( $success ? 'message' : 'error' ) );
	}

	/**
	 * Shows an installation message
	 *
	 * @param  string   $message
	 * @param  string   $title
	 * @param  string   $url
	 * @return void
	 */
	public static function renderInstallMessage( $message, $title = null, $url = null )
	{
		if ( ! $message ) {
			return;
		}
		?>
		<table class="table cbInstallMessage">
			<?php if ( $title ) { ?>
			<thead>
				<tr>
					<th class="cbInstallMessageTitle">
						<?php echo $title; ?>
					</th>
				</tr>
			</thead>
			<?php } ?>
			<tbody>
				<tr>
					<td class="cbInstallMessageMessage">
						<?php echo $message; ?>
						<?php if ( $url ) { ?>
						<div class="cbInstallMessageReturn" style="margin-top: 10px;">
							<strong><a href="<?php echo $url;?>"><?php echo CBTxt::T( 'Continue ...' ); ?></a></strong>
						</div>
						<?php } ?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}
}
