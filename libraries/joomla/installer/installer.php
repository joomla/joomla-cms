<?php
/**
* @version $Id: installer.php 1478 2005-12-20 02:36:15Z Jinx $
* @package JoomlaFramework
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* Installer class
*
* @package JoomlaFramework
* @subpackage Installer
* @abstract
*/
class JInstaller extends JObject
{
	var $i_installfilename	= "";
	var $i_installarchive	= "";
	var $i_installdir		= '';
	var $i_errno			= 0;
	var $i_error			= '';
	var $i_installtype		= '';
	var $i_unpackdir		= '';
	var $i_docleanup		= true;
	var $msg				= '';

	/**
	 * The directory where the element is to be installed
	 *
	 * @var string
	 */
	var $i_elementdir 		= '';

	/**
	 * The name of the Joomla! element
	 *
	 * @var string
	 */
	var $i_elementname 		= '';

	/**
	 * True if existing files can be overwritten
	 *
	 * @var boolean
	 */
	var $i_allowOverwrite = false;

	/**
	 * The name of a special atttibute in a tag
	 *
	 * @var string
	 */
	var $i_elementspecial 	= '';

	/**
	 * A DOMIT XML document
	 *
	 * @var object
	 */
	var $i_xmldoc			= null;
	var $i_hasinstallfile 	= null;
	var $i_installfile 		= null;
	var $i_stepstack		= null;

	/**
	* Constructor
	* 
	* @access protected
	*/
	function __construct() {
        $this->allowOverwrite( mosGetParam( $_POST, 'overwrite', 0 ) );
	}

	/**
	 * Returns a reference to the global Installer object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param type $type The installer type to instantiate
	 * @return database A database object
	 * @since 1.1
	*/
	function &getInstance( $type=null) {
		static $instances;

		if (!isset( $instances )) {
			$instances = array();
		}

		$signature = serialize(array($type));

		if (empty($instances[$signature])) {
			jimport('joomla.installer.adapters.'.$type);
			$adapter = 'JInstaller'.$type;
			$instances[$signature] = new $adapter();
		}

		return $instances[$signature];
	}

	/**
	* Uploads and unpacks a file
	*
	* @param string The uploaded package filename or install directory
	* @param boolean True if the file is an archive file
	* @return boolean True on success, False on error
	*/
	function upload($p_filename = null, $p_unpack = true) {

		// Set the path to the archive to install
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
	function getFilenameFromURL($url)
	{
		if(is_string($url)) {
			$parts = split('/', $url);
			return $parts[count($parts)-1];
		}
		return 0;
	}

	/**
	* Description: Creates a new template position if it doesn't exist already
	*/
	function createTemplatePosition($position)
	{
		global $database;
		if($position) {
			$database->setQuery("SELECT id FROM #__template_positions WHERE position = '$position'");
			$database->Query();
			if(!$database->getNumRows()) {
				$database->setQuery("INSERT INTO #__template_positions VALUES (0,'$position','')");
				$database->Query();
			}
		}
	}

	/**
	* Downloads a package
	*
	* @param string URL of file to download
	* @param string Download target
	*/
	function downloadPackage($url,$target=false)
	{
		$php_errormsg = 'Error Unknown';
		ini_set('track_errors',true);

		// Open remote server
		$input_handle = @fopen($url, "r"); // or die("Remote server connection failed");
		if (!$input_handle) {
			$this->setError(42, 'Remote Server connection failed: ' . $php_errormsg);
			return false;
		}
		if(!$target) {
			$target = JPATH_SITE . DS .'media'. DS . $this->getFilenameFromURL($url);
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
			if($contents) {
				$write_res = fwrite($output_handle, $contents);
				if($write_res == false) { $this->setError(45,'Cannot write to local target: ' . $php_errormsg); return false; }
			}
		}
		fclose($output_handle);
		fclose($input_handle);
		$this->installArchive( $this->getFilenameFromURL($url) );
		return $target;
	}


	/**
	* Extracts the package archive file
	*
	* @return boolean True on success, False on error
	*/
	function extractArchive() {

		// Initialize variables
		$base_Dir 		= JPath::clean( JPATH_SITE . DS .'media' );

		$archivename 	= $base_Dir . $this->installArchive();
		$tmpdir 		= uniqid( 'install_' );

		$extractdir 	= JPath::clean( $base_Dir . $tmpdir );
		$archivename 	= JPath::clean( $archivename, false );

		$this->unpackDir( $extractdir );
		if (eregi( '.zip$', $archivename )) {
			// Extract functions
			jimport('pcl.pclzip');
			jimport('pcl.pclerror');
			//jimport('pcl.pcltrace');
			//jimport('pcl.pcltar');
			$zipfile = new PclZip( $archivename );
			if(JPATH_ISWIN) {
				define('OS_WINDOWS',1);
			} else {
				define('OS_WINDOWS',0);
			}

			$ret = $zipfile->extract( PCLZIP_OPT_PATH, $extractdir );
			if($ret == 0) {
				$this->setError( 1, JText::_( 'Unrecoverable error' ) .' "'.$zipfile->errorName(true).'"' );
				return false;
			}

			// Free up PCLZIP memory
			unset($zipfile);
		} else {
			jimport('archive.Tar');
			$archive = new Archive_Tar( $archivename );
			$archive->setErrorHandling( PEAR_ERROR_PRINT );

			if (!$archive->extractModify( $extractdir, '' )) {
				$this->setError( 1, JText::_( 'Extract Error' ) );
				return false;
			}
			// Free up PCLTAR memory
			unset($archive);
		}

		$this->installDir( $extractdir );

		/*
		 * Try to find the correct install directory.  In case the package is inside a
		 * subdirectory detect this and set the install directory to the correct path
		 */
		$filesindir = JFolder::folders( $this->installDir(), '' );

		if (count( $filesindir ) == 1) {
			if (is_dir( $extractdir . $filesindir[0] )) {
				$this->installDir( JPath::clean( $extractdir . $filesindir[0] ) );
			}
		}
		return true;
	}

	/**
	* Tries to find the package XML file
	*
	* @return boolean True on success, False on error
	*/
	function findInstallFile()
	{
		$found = false;
		// Search the install dir for an xml file
		$files = JFolder::files( $this->installDir(), '.xml$', true, true );

		if (count( $files ) > 0) {
			foreach ($files as $file) {
				$packagefile = $this->isPackageFile( $file );
				if (!is_null( $packagefile ) && !$found ) {
					$this->xmlDoc( $packagefile );
					return true;
				}
			}
			$this->setError( 1, JText::_( 'ERRORJOSXMLSETUP' ) );
			return false;
		} else {
			$this->setError( 1, JText::_( 'ERRORXMLSETUP' ) );
			return false;
		}
	}

	/**
	* @param string A file path
	* @return object A DOMIT XML document, or null if the file failed to parse
	*/
	function isPackageFile( $p_file )
	{
		$xmlDoc =& JFactory::getXMLParser();
		$xmlDoc->resolveErrors( true );

		if (!$xmlDoc->loadXML( $p_file, false, true )) {
			return null;
		}
		$root = &$xmlDoc->documentElement;

		/*
		 * Check for mosinstall tag for backward compatability, but we are really
		 * looking for jinstall
		 */
		if ($root->getTagName() != 'mosinstall' && $root->getTagName() != 'jinstall') {
			return null;
		}

		if ($root->getAttribute( 'install' ) == 'upgrade' ) {
			$this->allowOverwrite(1);
		}

		// Set the type
		$this->installType( $root->getAttribute( 'type' ) );
		$this->installFilename( $p_file );
		return $xmlDoc;
	}

	/**
	* Loads and parses the XML setup file
	*
	* @return boolean True on success, False on error
	*/
	function readInstallFile()
	{
		if ($this->installFilename() == "") {
			$this->setError( 1, JText::_( 'No filename specified' ) );
			return false;
		}

		$this->i_xmldoc =& JFactory::getXMLParser();
		$this->i_xmldoc->resolveErrors( true );
		if (!$this->i_xmldoc->loadXML( $this->installFilename(), false, true )) {
			return false;
		}
		$root = &$this->i_xmldoc->documentElement;

		/*
		 * Check that document is a Joomla installation file
		 * 'mosinstall' tag deprecated, use jinstall moving forward
		 */
		if ($root->getTagName() != 'mosinstall' && $root->getTagName() != 'jinstall') {
			$this->setError( 1, JText::_( 'File' ) .': "' . $this->installFilename() . '" '. JText::_( 'is not a valid Joomla! installation file' ) );
			return false;
		}

		$this->installType( $root->getAttribute( 'type' ) );
		return true;
	}

	/**
	* Abstract install method
	*/
	function install() {
		die( JText::_( 'Method "install" cannot be called by class' ) .' ' . strtolower(get_class( $this )) );
	}

	/**
	* Abstract uninstall method
	*/
	function uninstall() {
		die( JText::_( 'Method "uninstall" cannot be called by class' ) .' ' . strtolower(get_class( $this )) );
	}

	/**
	* return to method
	*/
	function returnTo( $option, $element ) {
		return "index2.php?option=$option&element=$element";
	}

	/**
	 * Prepare for installation: this method sets the installation directory, finds
	 * and checks the installation file and verifies the installation type
	 *
	 * @access public
	 * @param string Install from directory
	 * @param string The install type
	 * @return boolean True on success
	 * @since 1.0
	 */
	function preInstallCheck( $p_fromdir, $type )
	{
		if (!is_null($p_fromdir)) {
			$this->installDir($p_fromdir);
		}

		if (!$this->installfile()) {
			$this->findInstallFile();
		}

		if (!$this->readInstallFile()) {
			$this->setError( 1, JText::_( 'Installation file not found' ) .':<br />' . $this->installDir() );
			return false;
		}

		if ($this->installType() != $type) {
			$this->setError( 1, JText::_( 'XML setup file is not for a' ) .' "'.$type.'".' );
			return false;
		}

		// In case there where an error doring reading or extracting the archive
		if ($this->errno()) {
			return false;
		}

		return true;
	}

	/**
	 * Method to parse through a files element of the installation file and take appropriate
	 * action.
	 *
	 * @access public
	 * @param string The tag name to parse
	 * @param string An attribute to search for in a filename element
	 * @param string The value of the 'special' element if found
	 * @param boolean True for Administrator components
	 * @return mixed Number of file or False on error
	 * @since 1.0
	 */
	function parseFiles( $tagName='files', $special='', $specialError='', $adminFiles=0 )
	{
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
			$temp = JPath::clean( $this->unpackDir() . $folder );
			if ($temp == $this->installDir()) {
				// this must be only an admin component
				$installFrom = $this->installDir();
			} else {
				$installFrom = JPath::clean( $this->installDir() . $folder );
			}
		} else {
			$installFrom = $this->installDir();
		}

		foreach ($files as $file) {
			if (basename( $file->getText() ) != $file->getText()) {
				$newdir = dirname( $file->getText() );

				if ($adminFiles){
					if (!JFolder::create( $this->componentAdminDir(). $newdir )) {
						$this->setError( 1, JText::_( 'Failed to create directory' ) .' "'. ($this->componentAdminDir()) . $newdir .'"' );
						return false;
					}
				} else {
					if (!JFolder::create( $this->elementDir(). $newdir )) {
						$this->setError( 1, JText::_( 'Failed to create directory' ) .' "'. ($this->elementDir()) . $newdir .'"' );
						return false;
					}
				}
			}

			/*
			 * If the file is a language, we must handle it differently.  Language files
			 * go in a subdirectory based on the language code, ie.
			 *
			 * 		<language tag="en_US">en_US.mycomponent.ini</language>
			 *
			 * would go in the en_US subdirectory of the languages directory.
			 */
			if ($file->getTagName() == 'language' && $file->getAttribute('tag') != '') {
				$copyfiles[] = $file->getAttribute('tag').DS.$file->getText();
			} else {
				$copyfiles[] = $file->getText();
			}

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
			$installTo = JPath::clean( JPATH_SITE . DS .'images'.DS.'stories' );
		} else if ($tagName == 'languages') {
			// languages is a special tag
			$installTo = JPath::clean( JPATH_SITE . DS .'languages' );
		} else if ($tagName == 'administration/languages') {
			// administration/languages is a special tag
			$installTo = JPath::clean( JPATH_ADMINISTRATOR . DS .'languages' );
		} else if ($adminFiles) {
			$installTo = $this->componentAdminDir();
		} else {
			$installTo = $this->elementDir();
		}

		$result = $this->copyFiles( $installFrom, $installTo, $copyfiles );

		return $result;
	}

	/**
	 * Copy files from source directory to the target directory
	 *
	 * @param string Source directory
	 * @param string Destination directory
	 * @param array array with filenames
	 * @param boolean True is existing files can be replaced
	 * @return boolean True on success, False on error
	 */
	function copyFiles( $p_sourcedir, $p_destdir, $p_files, $overwrite=false ) {
		$overwrite = $this->allowOverwrite();

		if (is_array( $p_files ) && count( $p_files ) > 0) {
			foreach($p_files as $_file) {
				$filesource	= JPath::clean( $p_sourcedir ) . $_file;
				$filedest	= JPath::clean( $p_destdir ) . $_file;

				if (!file_exists( $filesource )) {
					$this->setError( 1, sprintf( JText::_( 'File does not exist' ), $filesource  ));
					return false;
				} else if (file_exists( $filedest ) && !$overwrite) {
					$this->setError( 1, sprintf( JText::_( 'WARNSAME' ), $filedest ) );
					return false;
				} else {
					if( !( JFile::copy($filesource,$filedest) ) ) {
						$this->setError( 1, sprintf( JText::_( 'Failed to copy file to' ), $filesource, $filedest ) );
						return false;
					}
					/*
					 * Since we copied a file, we want to add it to the installation step stack so that
					 * in case we have to roll back the installation we can remove the files copied.
					 */
					$step = array('type' => 'file', 'path' => $filedest);
					$this->i_stepstack[] = $step;
				}
			}
		} else {
			return false;
		}
		return count( $p_files );
	}

	/**
	 * Copies the XML setup file to the element Admin directory
	 *
	 * Used by Components/Modules/Mambot Installer Installer
	 * @return boolean True on success, False on error
	 */
	function copySetupFile( $where='admin' )
	{
		if ($where == 'admin') {
			return $this->copyFiles( $this->installDir(), $this->componentAdminDir(), array( basename( $this->installFilename() ) ), true );
		} else if ($where == 'front') {
			return $this->copyFiles( $this->installDir(), $this->elementDir(), array( basename( $this->installFilename() ) ), true );
		}
	}

	/**
	 * Sets the error number and message for the installer
	 *
	 * @param int The error number
	 * @param string The error message
	 */
	function setError( $p_errno, $p_error ) {
		$this->errno( $p_errno );
		$this->error( $p_error );
	}

	/**
	 * Gets the error message for the installer
	 *
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

	function allowOverwrite( $p_allowOverwrite = false ) {
		return $this->setVar( 'allowOverwrite', $p_allowOverwrite );
	}

	function installFilename( $p_filename = null )
	{
		if(!is_null($p_filename)) {
			$this->i_installfilename = JPath::clean($p_filename);
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

/**
 * Installer helper class
 *
 * @static
 * @package JoomlaFramework
 * @subpackage Installer
 * @since 1.1
 */
class JInstallerHelper {

	/**
	 * Search for a valid XML Joomla install file within the given path and get the
	 * installation type from the install file.
	 *
	 * @param string $path Path to search in
	 * @return mixed Install Type or boolean False if no install file is found
	 * @since 1.1
	 */
	function detectType( $path ) {

		// Initialize variables
		$found = false;

		// Search the install dir for an xml file
		$files = JFolder::files( $path, '\.xml$', true, true );

		if (count( $files ) > 0) {

			foreach ($files as $file) {
				$xmlDoc =& JFactory::getXMLParser();
				$xmlDoc->resolveErrors( true );

				if (!$xmlDoc->loadXML( $file, false, true )) {
					// Free up memory from DOMIT parser
					unset($xmlDoc);
					return false;
				}
				$root = &$xmlDoc->documentElement;

				if ($root->getTagName() != "mosinstall" && $root->getTagName() != 'jinstall') {
					continue;
				}
//				echo "<p>Looking at file $file, I consider it to be a valid installer file.</p>";
				$type = $root->getAttribute( 'type' );
				// Free up memory from DOMIT parser
				unset($xmlDoc);
				return $type;

			}
			JError::raiseWarning( 1, JText::_( 'ERRORNOTFINDJOOMLAXMLSETUPFILE' ) );
			// Free up memory from DOMIT parser
			unset($xmlDoc);
			return false;
		} else {
			JError::raiseWarning( 1, JText::_( 'ERRORNOTFINDXMLSETUPFILE' ) );
			return false;
		}
	}

	/**
	 * Clean up temporary uploaded package and unpacked element
 	 *
 	 * @param string $userfile_name Path to the uploaded package file
 	 * @param string $resultdir Path to the unpacked element
 	 * @return boolean True on success
 	 * @since 1.1
 	 */
	function cleanupInstall( $userfile_name, $resultdir) {

		if (file_exists( $resultdir )) {
			JFolder::delete( $resultdir );
			JFile::delete( JPath::clean( JPATH_SITE . DS .'media'. DS . $userfile_name, false ) );
			return true;
		}
	}
}
?>
