<?php
/**
 * @version $Id: installer.php 1478 2005-12-20 02:36:15Z Jinx $
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

jimport('joomla.common.base.object');

/**
 * Joomla base installer class
 *
 * @package Joomla.Framework
 * @subpackage Installer
 * @abstract
 */
class JInstaller extends JObject {

	/**
	 * The directory that the extension is to be installed from
	 * 
	 * @var string
	 */
	var $i_installDir = null;

	/**
	 * The extension type to install
	 * 
	 * @var string
	 */
	var $i_installType = null;

	/**
	 * The xml install file
	 * 
	 * @var string
	 */
	var $i_installFile = null;

	/**
	 * The does the package have an install script?
	 * 
	 * @var boolean
	 */
	var $i_hasInstallScript = false;

	/**
	 * The package install script
	 * 
	 * @var string
	 */
	var $i_installScript = null;

	/**
	 * The name of the Joomla! extension
	 *
	 * @var string
	 */
	var $i_extensionName = null;

	/**
	 * The site directory where the extension is to be installed
	 *
	 * @var string
	 */
	var $i_extensionDir = null;

	/**
	 * The admin directory where the extension is to be installed
	 *
	 * @var string
	 */
	var $i_extensionAdminDir = null;

	/**
	 * The name of a special atttibute in a tag
	 *
	 * @var string
	 */
	var $i_extensionSpecial = null;

	/**
	 * True if existing files can be overwritten
	 *
	 * @var boolean
	 */
	var $i_allowOverwrite = false;

	/**
	 * A DOMIT XML document
	 *
	 * @var object
	 */
	var $i_xmldoc = null;

	/**
	 * A database connector object
	 *
	 * @var object
	 */
	var $i_db = null;

	/**
	 * Stack of installation steps
	 * 	- Used for installation rollback
	 *
	 * @var array
	 */
	var $i_stepStack = array ();

	/**
	 * The description of the extension
	 *
	 * @var string
	 */
	var $i_description = null;

	/**
	 * The output from the install/uninstall scripts
	 *
	 * @var string
	 */
	var $i_message = null;

	/**
	 * Constructor
	 * 
	 * @access protected
	 */
	function __construct( &$db ) {
		$this->_db =& $db;
	}

	/**
	 * Returns a reference to the global Installer object, only creating it
	 * if it doesn't already exist.
	 *
	 * @static
	 * @param object $db A database connector object
	 * @param string $type The installer type to instantiate [optional]
	 * @return database A database object
	 * @since 1.1
	 */
	function & getInstance( &$db, $type = null) {
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		$signature = serialize(array ($type));

		if (empty ($instances[$signature])) {
			jimport('joomla.installer.adapters.'.$type);
			$adapter = 'JInstaller'.$type;
			$instances[$signature] = new $adapter ($db);
		}

		return $instances[$signature];
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
	function preInstallCheck($p_fromdir, $type) {

		if (!is_null($p_fromdir)) {
			$this->i_installDir = $p_fromdir;
		}

		if (is_null($this->i_installFile)) {
			if (!$this->_findInstallFile()) {
				JError::raiseWarning( 1, 'JInstaller::install: ' . JText :: _('Installation file not found').':<br />'.$this->i_installDir);
				return false;
			}
		}

		if (!$this->_readInstallFile()) {
			JError::raiseWarning( 1, 'JInstaller::install: ' . JText :: _('Installation file not found').':<br />'.$this->i_installDir);
			return false;
		}

		if ($this->i_installType != $type) {
			JError::raiseWarning( 1, 'JInstaller::install: ' . JText :: _('XML setup file is not for a').' "'.$type.'".');
			return false;
		}

		return true;
	}

	/**
	 * Abstract install method
	 * 	- override in child class
	 * 
	 * @abstract
	 * @since 1.0
	 */
	function install() {
		die(JText :: _('Method "install" cannot be called by class').' '.strtolower(get_class($this)));
	}

	/**
	 * Abstract update method
	 * 	- override in child class
	 * 
	 * @abstract
	 * @since 1.1
	 */
	function update() {
		die(JText :: _('Method "update" cannot be called by class').' '.strtolower(get_class($this)));
	}

	/**
	 * Abstract uninstall method
	 * 	- override in child class
	 * 
	 * @abstract
	 * @since 1.0
	 */
	function uninstall() {
		die(JText :: _('Method "uninstall" cannot be called by class').' '.strtolower(get_class($this)));
	}

	/**
	 * Tries to find the package XML file
	 *
	 * @protected
	 * @return boolean True on success, False on error
	 * @since 1.0
	 */
	function _findInstallFile() {

		/*
		 * Get an array of all the xml files from teh installation directory
		 */
		$xmlfiles = JFolder :: files($this->i_installDir, '.xml$', true, true);

		// If at least one xml file exists
		if (count($xmlfiles) > 0) {
			foreach ($xmlfiles as $file) {
				// Is it a valid joomla install file?
				$packagefile = & $this->_isPackageFile($file);
				if (!is_null($packagefile)) {
					$this->i_xmldoc = & $packagefile;
					$this->i_installFile = $file;
					return true;
				}
			}
			/*
			 * None of the xml files found were valid install files
			 */
			JError::raiseWarning( 1, 'JInstaller::install: ' . JText :: _('ERRORJOSXMLSETUP'));
			return false;
		} else {
			/*
			 * No xml files were found in the install directory
			 */
			JError::raiseWarning( 1, 'JInstaller::install: ' . JText :: _('ERRORXMLSETUP'));
			return false;
		}
	}

	/**
	 * Is the xml file a valid Joomla install file
	 * 
	 * @access protected
	 * @param string $p_file An xmlfile path to check
	 * @return mixed A DOMIT XML document, or null if the file failed to parse
	 * @since 1.0
	 */
	function _isPackageFile($p_file) {

		/*
		 * Get an xml parser object
		 */
		$xmlDoc = & JFactory :: getXMLParser();
		$xmlDoc->resolveErrors(true);

		/*
		 * If we cannot load the xml file return null
		 */
		if (!$xmlDoc->loadXML($p_file, false, true)) {
			// Free up xml parser memory and return null
			unset ($xmlDoc);
			return null;
		}

		// Get the root node of the xml document
		$root = & $xmlDoc->documentElement;

		/*
		 * Check for a valid XML root tag.
		 * 
		 * Should be 'install', but for backward compatability we will accept 'mosinstall'.
		 */
		if ($root->getTagName() != 'install' && $root->getTagName() != 'mosinstall') {
			// Free up xml parser memory and return null
			unset ($xmlDoc);
			return null;
		}

		/*
		 * Set the installation type and filename
		 */
		$this->i_installType = $root->getAttribute('type');
		$this->i_installFile = JPath :: clean($p_file);

		return $xmlDoc;
	}

	/**
	 * Loads and parses the XML setup file
	 *
	 * @access private
	 * @return boolean True on success
	 * @since 1.0
	 */
	function _readInstallFile() {

		/*
		 * If the xml installation file has not been found, set an error and return
		 * false.
		 */
		if (empty ($this->i_installFile)) {
			JError::raiseWarning( 1, 'JInstaller::install: ' . JText :: _('No filename specified'));
			return false;
		}

		/*
		 * If the XML document object is not set, try to create it.  If cannot be
		 * created, set an error and return false.
		 */
		if (!is_object($this->i_xmldoc)) {
			$this->i_xmldoc = & JFactory :: getXMLParser();
			$this->i_xmldoc->resolveErrors(true);
			if (!$this->i_xmldoc->loadXML($this->i_installFile, false, true)) {
				JError::raiseWarning( 1, 'JInstaller::install: ' . JText :: _('ERRORJOSXMLSETUP'));
				return false;
			}
		}

		// Get the root node of the XML document
		$root = & $this->i_xmldoc->documentElement;

		/*
		 * Check for a valid XML root tag.
		 * 
		 * Should be 'install', but for backward compatability we will accept 'mosinstall'.
		 */
		if ($root->getTagName() != 'install' && $root->getTagName() != 'mosinstall') {
			JError::raiseWarning( 1, 'JInstaller::install: ' . JText :: _('File').': "'.$this->i_installFile.'" '.JText :: _('is not a valid Joomla! installation file'));
			return false;
		}

		/*
		 * Set the type for the extension to install
		 */
		$this->i_installType = $root->getAttribute('type');

		/*
		 * If the install attribute is set to upgrade, allow file overwrite
		 */
		if ($root->getAttribute('install') == 'upgrade') {
			$this->i_allowOverwrite = true;
		}

		return true;
	}

	/**
	 * Method to parse through a files element of the installation file and take appropriate
	 * action.
	 *
	 * @access private
	 * @param string $tagName The tag name to parse
	 * @param string $special An attribute to search for in a filename element
	 * @param string $specialError The value of the 'special' element if found
	 * @param boolean $admin True for Administrator files
	 * @return mixed Number of files processed or False on error
	 * @since 1.0
	 */
	function _parseFiles($tagName = 'files', $special = null, $specialError = null, $admin = false) {

		/*
		 * Initialize variables
		 */
		$copyfiles = array ();

		/*
		 * Get the install document root element
		 */
		$root = & $this->i_xmldoc->documentElement;

		/*
		 * Get the element from the document
		 */
		$filesElement = & $root->getElementsByPath($tagName, 1);
		if (is_null($filesElement) || !$filesElement->hasChildNodes()) {
			/*
			 * Either the tag does not exist or has no children therefore we return
			 * zero files processed.
			 */
			return 0;
		}

		/*
		 * Get the array of file nodes to process
		 */
		$files = & $filesElement->childNodes;
		if (count($files) == 0) {
			/*
			 * No files to process
			 */
			return 0;
		}

		/*
		 * Here we set the folder we are going to copy the files to.  There are a few
		 * special cases that need to be considered for certain reserved tags.
		 * 
		 * 	- 'media' Files are copied to the JROOT/images/stories/ folder
		 * 	- 'languages' Files are copied to JROOT/languages/ folder
		 * 	- 'administration/languages' Files are copied to JADMIN_ROOT/languages/
		 */
		switch ($tagName) {
			case 'media':
				if ($filesElement->hasAttribute('destination')) {
					$folder = $filesElement->getAttribute('destination');
				} else {
					$folder = 'stories';
				}
				$installTo = JPath :: clean(JPATH_SITE.DS.'images'.DS.$folder);
				break;
			case 'languages':
				$installTo = JPath :: clean(JPATH_SITE.DS.'languages');
				break;
			case 'administration/languages':
				$installTo = JPath :: clean(JPATH_ADMINISTRATOR.DS.'languages');
				break;
			default:
				if ($admin) {
					$installTo = $this->i_extensionAdminDir;
				} else {
					$installTo = $this->i_extensionDir;
				}
				break;			
		}

		/*
		 * Here we set the folder we are going to copy the files from.
		 * 
		 * Does the element have a folder attribute?
		 * 
		 * If so this indicates that the files are in a subdirectory of the source 
		 * folder and we should append the folder attribute to the source path when 
		 * copying files.
		 */
		if ($folder = $filesElement->getAttribute('folder')) {
			$installFrom = JPath :: clean($this->i_installDir.$folder);
		} else {
			$installFrom = $this->i_installDir;
		}

		/*
		 * Process each file in the $files array (children of $tagName).
		 */
		foreach ($files as $file) {

			/*
			 * Check to see if the special attribute is set for the file, and if so set
			 * the class field to its value.
			 */
			if ($file->getAttribute($special)) {
				$this->i_extensionSpecial = $file->getAttribute($special);
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
				$path = $file->getAttribute('tag').DS.$file->getText();
			} else {
				$path = $file->getText();
			}

			/*
			 * Before we can add a file to the copyfiles array we need to ensure 
			 * that the folder we are copying our file to exits and if it doesn't, 
			 * we need to create it.
			 */
			if (basename($path) != $path) {
				$newdir = dirname($path);

				if (!JFolder :: create($installTo.$newdir)) {
					JError::raiseWarning( 1, 'JInstaller::install: ' . JText :: _('Failed to create directory').' "'. ($this->elementDir()).$newdir.'"');
					return false;
				}
			}

			/*
			 * Add the file to the copyfiles array
			 */
			$copyfiles[] = $path;
		}

		/*
		 * If a file with a special tag was expected but not found, set an error and
		 * return false.
		 */
		if ($specialError) {
			if (empty ($this->i_extensionSpecial)) {
				JError::raiseWarning( 1, 'JInstaller::install: ' . $specialError);
				return false;
			}
		}

		return $this->_copyFiles($installFrom, $installTo, $copyfiles);
	}

	/**
	 * Method to parse through a queries element of the installation file and take appropriate
	 * action.
	 *
	 * @access private
	 * @param string $tagName The tag name to parse
	 * @return mixed Number of queries processed or False on error
	 * @since 1.1
	 */
	function _parseQueries($tagName = 'files') {

		/*
		 * Get the database connector object
		 */
		$db = & $this->_db;

		/*
		 * Get the install document root element
		 */
		$root = & $this->i_xmldoc->documentElement;

		/*
		 * Get the element of the tag names
		 */
		$queriesElement = & $root->getElementsByPath($tagName, 1);
		if (is_null($queriesElement) || !$queriesElement->hasChildNodes()) {
			/*
			 * Either the tag does not exist or has no children therefore we return
			 * zero queries processed.
			 */
			return 0;
		}

		/*
		 * Get the array of query nodes to process
		 */
		$queries = & $queriesElement->childNodes;
		if (count($queries) == 0) {
			/*
			 * No queries to process
			 */
			return 0;
		}

		/*
		 * Process each query in the $queries array (children of $tagName).
		 */
		foreach ($queries as $query) {
			$db->setQuery($query->getText());
			if (!$db->query()) {
				JError::raiseWarning( 1, 'JInstaller::install: ' . JText :: _('SQL Error')." ".$db->stderr(true));
				return false;
			}
		}

		return count($queries);
	}

	/**
	 * Method to parse the parameters of an extension, build the INI
	 * string for it's default parameters, and return the INI string.
	 *
	 * @access private
	 * @return string INI string of parameter values
	 * @since 1.1
	 */
	function _getParams() {

		/*
		 * Get the install document root element
		 */
		$root = & $this->i_xmldoc->documentElement;

		/*
		 * Get the element of the tag names
		 */
		$paramsElement = & $root->getElementsByPath('params', 1);
		if (is_null($paramsElement) || !$paramsElement->hasChildNodes()) {
			/*
			 * Either the tag does not exist or has no children therefore we return
			 * zero params processed.
			 */
			return null;
		}

		/*
		 * Get the array of parameter nodes to process
		 */
		$params = & $paramsElement->childNodes;
		if (count($params) == 0) {
			/*
			 * No params to process
			 */
			return null;
		}

		/*
		 * Process each parameter in the $params array.
		 */
		$ini = null;
		foreach ($params as $param) {
			if (!$name = $param->getAttribute('name')) {
				continue;
			}
			
			if (!$value = $param->getAttribute('default')) {
				continue;
			}
			
			$ini .= $name."=".$value."\n";
		}

		return $ini;
	}

	/**
	 * Copy files from source directory to the target directory
	 *
	 * @access private
	 * @param string $p_sourcedir Source directory
	 * @param string $p_destdir Destination directory
	 * @param array $p_files array with filenames
	 * @param boolean $overwrite True if existing files can be replaced
	 * @return boolean True on success
	 * @since 1.0
	 */
	function _copyFiles($p_sourcedir, $p_destdir, $p_files, $overwrite = null) {

		/*
		 * To allow for manual override on the overwriting flag, we check to see if
		 * the $overwrite flag was set and is a boolean value.  If not, use the object
		 * allowOverwrite flag.
		 */
		if (is_null($overwrite) || !is_bool($overwrite)) {
			$overwrite = $this->i_allowOverwrite;
		}

		/*
		 * $p_files must be an array of filenames.  Verify that it is an array with 
		 * at least one file to copy.
		 */
		if (is_array($p_files) && count($p_files) > 0) {
			foreach ($p_files as $_file) {

				// Get the source and destination paths
				$filesource = JPath :: clean($p_sourcedir).$_file;
				$filedest = JPath :: clean($p_destdir).$_file;

				if (!file_exists($filesource)) {
					/*
					 * The source file does not exist.  Nothing to copy so set an error
					 * and return false.
					 */
					JError::raiseWarning( 1, 'JInstaller::install: ' . sprintf(JText :: _('File does not exist'), $filesource));
					return false;
				} else
					if (file_exists($filedest) && !$overwrite) {
						/*
						 * The destination file already exists and the overwrite flag is false.
						 * Set an error and return false.
						 */
						JError::raiseWarning( 1, 'JInstaller::install: ' . sprintf(JText :: _('WARNSAME'), $filedest));
						return false;
					} else {
						if (!(JFile :: copy($filesource, $filedest))) {
							JError::raiseWarning( 1, 'JInstaller::install: ' . sprintf(JText :: _('Failed to copy file to'), $filesource, $filedest));
							return false;
						}
						/*
						 * Since we copied a file, we want to add it to the installation step stack so that
						 * in case we have to roll back the installation we can remove the files copied.
						 */
						$step = array ('type' => 'file', 'path' => $filedest);
						$this->i_stepStack[] = $step;
					}
			}
		} else {
			/*
			 * The $p_files variable was either not an array or an empty array
			 */
			return false;
		}
		return count($p_files);
	}

	/**
	 * Copies the XML install file to the extension folder in the given client
	 *
	 * @access private
	 * @param int $client Where to copy the installfile [optional: defaults to 1 (admin)]
	 * @return boolean True on success, False on error
	 * @since 1.0
	 */
	function _copyInstallFile($client = 1) {
		if ($client == 1) {
			return $this->_copyFiles($this->i_installDir, $this->i_extensionAdminDir, array (basename($this->i_installFile)), true);
		} else {
			if ($client == 0) {
				return $this->_copyFiles($this->i_installDir, $this->i_extensionDir, array (basename($this->i_installFile)), true);
			}
		}
	}

	/**
	 * Method to parse through a files element of the installation file and remove
	 * the files that were installed
	 *
	 * @access private
	 * @param string $tagName The tag name to parse
	 * @param boolean $admin True for Administrator files
	 * @return boolean True on success
	 * @since 1.1
	 */
	function _removeFiles($tagName = 'files', $admin = false) {

		/*
		 * Initialize variables
		 */
		$removefiles = array ();
		$retval = false;

		/*
		 * Get the install document root element
		 */
		$root = & $this->i_xmldoc->documentElement;

		/*
		 * Get the element of the tag names
		 */
		$filesElement = & $root->getElementsByPath($tagName, 1);
		if (is_null($filesElement) || !$filesElement->hasChildNodes()) {
			/*
			 * Either the tag does not exist or has no children therefore we return
			 * zero files processed.
			 */
			return 0;
		}

		/*
		 * Get the array of file nodes to process
		 */
		$files = $filesElement->childNodes;
		if (count($files) == 0) {
			/*
			 * No files to process
			 */
			return 0;
		}

		/*
		 * Here we set the folder we are going to copy the files to.  There are a few
		 * special cases that need to be considered for certain reserved tags.
		 * 
		 * 	- 'media' Files are copied to the JROOT/images/stories/ folder
		 * 	- 'languages' Files are copied to JROOT/languages/ folder
		 * 	- 'administration/languages' Files are copied to JADMIN_ROOT/languages/
		 */
		if ($tagName == 'media') {
			if ($filesElement->hasAttribute('destination')) {
				$folder = $filesElement->getAttribute('destination');
			} else {
				$folder = 'stories';
			}
			$removeFrom = JPath :: clean(JPATH_SITE.DS.'images'.DS.$folder);
		} else
			if ($tagName == 'languages') {
				$removeFrom = JPath :: clean(JPATH_SITE.DS.'languages');
			} else
				if ($tagName == 'administration/languages') {
					$removeFrom = JPath :: clean(JPATH_ADMINISTRATOR.DS.'languages');
				} else
					if ($admin) {
						$removeFrom = $this->i_extensionAdminDir;
					} else {
						$removeFrom = $this->i_extensionDir;
					}

		/*
		 * Process each file in the $files array (children of $tagName).
		 */
		foreach ($files as $file) {

			/*
			 * If the file is a language, we must handle it differently.  Language files
			 * go in a subdirectory based on the language code, ie.
			 *
			 * 		<language tag="en_US">en_US.mycomponent.ini</language>
			 *
			 * would go in the en_US subdirectory of the languages directory.
			 */
			if ($file->getTagName() == 'language' && $file->getAttribute('tag') != '') {
				$path = $removeFrom.$file->getAttribute('tag').DS.$file->getText();
			} else {
				$path = $removeFrom.$file->getText();
			}

			/*
			 * Actually delete the files/folders
			 */
			if (is_dir($path)) {
				$retval |= !JFolder :: delete($path);
			} else {
				$retval |= !JFile :: delete($path);
			}
		}

		return $retval;
	}

	/**
	 * Method to map a client to its base directory.  This can be useful moving forward
	 * if we want to allow extensions to be installed to applications other than the site
	 * and administrator.
	 * 
	 * @access private
	 * @param string $client Client application name
	 * @return mixed Client application array(id,basePath) or boolean false if not found
	 * @since 1.1
	 */
	function _mapClient($client) {
	
		/*
		 * Array of known valid clients (JApplications) mapped to their respective
		 * base directories
		 */	
		$map = array (	'administrator' => array( 'id' => 1, 'path' => JPATH_ADMINISTRATOR),
		 				'site' => array( 'id' => 0, 'path' => JPATH_SITE));
		
		if(isset($map[$client])) {
			return $map[$client];
		} else {
			return false;
		}
	}

	/**
	 * Roll back the extension installation
	 * 	- Called if an installation step fails
	 *
	 * @access private
	 * @return boolean True on success
	 * @since 1.1
	 */
	function _rollback() {

		/*
		 * Initialize variables
		 */
		$retval = true;
		$step = array_pop($this->i_stepStack);

		// Get database connector object
		$db = & $this->_db;

		while ($step != null) {

			switch ($step['type']) {
				case 'file' :
					// remove the file
					$stepval = JFile :: delete($step['path']);
					break;

				case 'folder' :
					// remove the folder
					$stepval = JFolder :: delete($step['path']);
					break;

				case 'query' :
					// placeholder in case this is necessary in the future
					break;

				default :
					/*
					 * Get the name of the custom rollback method for the type
					 */
					$method = '_rollback_'.$step['type'];

					/*
					 * Custom rollback method handler
					 */
					if (method_exists($this, $method)) {
						$stepval = $this-> $method ($step);
					} else {
						// do nothing	
					}
					break;
			}
			
			/*
			 * Only set the return value if it is false
			 */
			if ($stepval === false) {
				$retval = false;
			}
			
			print_r($step);
			// Get the next step and continue
			$step = array_pop($this->i_stepStack);
		}

		return $retval;
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
	 * Downloads a package
	 *
	 * @static
	 * @param string URL of file to download
	 * @param string Download target filename [optional]
	 * @return mixed Path to downloaded package or boolean false on failure
	 * @since 1.1
	 */
	function downloadPackage($url, $target = false) {

		/*
		 * Set the target path if not given
		 */
		if (!$target) {
			$target = JPATH_SITE.DS.'media'.DS.JInstallerHelper :: getFilenameFromURL($url);
		} else {
			$target = JPATH_SITE.DS.'media'.DS.basename($target);
		}

		/*
		 * Capture php errors
		 */
		$php_errormsg = 'Error Unknown';
		ini_set('track_errors', true);

		/*
		 * Open the remote server socket for reading
		 */
		$inputHandle = @ fopen($url, "r");
		if (!$inputHandle) {
			JError::raiseWarning(42, 'Remote Server connection failed: '.$php_errormsg);
			return false;
		}

		// Initialize contents buffer
		$contents = null;

		while (!feof($inputHandle)) {
			$contents .= fread($inputHandle, 4096);
			if ($contents == false) {
				JError::raiseWarning(44, 'Failed reading network resource: '.$php_errormsg);
				return false;
			}
		}
		
		/*
		 * Write buffer to file
		 */
		JFile :: write($target, $contents);
		
		/*
		 * Close file pointer resources
		 */
		fclose($inputHandle);

		/*
		 * Return the name of the downloaded package
		 */
		return basename($target);
	}

	/**
	 * Unpacks a file and verifies it as a Joomla element package
	 *
	 * @static
	 * @param string $p_filename The uploaded package filename or install directory
	 * @return boolean True on success, False on error
	 * @since 1.1
	 */
	function unpack( $p_filename ) {

		/*
		 * Initialize variables
		 */
		// Base installation path
		$base_Dir = JPath :: clean(JPATH_SITE.DS.'media');
		// Path to the archive
		$archivename = $base_Dir.$p_filename;
		// Temporary folder to extract the archive into
		$tmpdir = uniqid('install_');

		// Clean the paths to use for archive extraction
		$extractdir = JPath :: clean($base_Dir.$tmpdir);
		$archivename = JPath :: clean($archivename, false);

		/*
		 * Are we working with a zipfile?
		 */
		if (eregi('.zip$', $archivename)) {

			/*
			 * Import the zipfile libraries
			 */
			jimport('pcl.pclzip');
			jimport('pcl.pclerror');
			//jimport('pcl.pcltrace');

			/*
			 * Create a zipfile object
			 */
			$zipfile = new PclZip($archivename);

			// Constants used by the zip library
			if (JPATH_ISWIN) {
				define('OS_WINDOWS', 1);
			} else {
				define('OS_WINDOWS', 0);
			}

			/*
			 * Now its time to extract the archive
			 */
			if ($zipfile->extract(PCLZIP_OPT_PATH, $extractdir) == 0) {
				// Unable to extract the archive, set an error and fail
				JError::raiseWarning(1, JText :: _('Unrecoverable error').' "'.$zipfile->errorName(true).'"');
				return false;
			}

			// Free up PCLZIP memory
			unset ($zipfile);
		} else {

			/*
			 * Not a zipfile, must be a tarball.  Lets import that library.
			 */
			jimport('archive.Tar');

			/*
			 * Create a tarball object
			 */
			$archive = new Archive_Tar($archivename);

			// Set the tar error handling
			$archive->setErrorHandling(PEAR_ERROR_PRINT);

			/*
			 * Now its time to extract the archive
			 */
			if (!$archive->extractModify($extractdir, '')) {
				// Unable to extract the archive, set an error and fail
				JError::raiseWarning(1, JText :: _('Extract Error'));
				return false;
			}

			// Free up PCLTAR memory
			unset ($archive);
		}
		
		/*
		 * Lets set the extraction directory in the result array so we can
		 * cleanup everything properly later on.
		 */
		$retval['extractdir'] = $extractdir;

		/*
		 * Try to find the correct install directory.  In case the package is inside a
		 * subdirectory detect this and set the install directory to the correct path.
		 * 
		 * List all the items in the installation directory.  If there is only one, and
		 * it is a folder, then we will set that folder to be the installation folder.
		 */
		$dirList = array_merge(JFolder :: files($extractdir, ''), JFolder :: folders($extractdir, ''));

		if (count($dirList) == 1) {
			if (JFolder :: exists($extractdir.$dirList[0])) {
				$extractdir = JPath :: clean($extractdir.$dirList[0]);
			}
		}

		/*
		 * We have found the install directory so lets set it and then move on
		 * to detecting the extension type.
		 */
		$retval['dir'] = $extractdir;
		
		/*
		 * Get the extension type and return the directory/type array on success or
		 * false on fail.
		 */
		if ($retval['type'] = JInstallerHelper :: detectType($extractdir)) {
			return $retval;
		} else {
			return false;
		}
	}

	/**
	 * Method to detect the extension type from a package directory
	 * 
	 * @static
	 * @param string $p_dir Path to package directory
	 * @return mixed Extension type string or boolean false on fail
	 * @since 1.1
	 */
	function detectType($p_dir) {

		// Search the install dir for an xml file
		$files = JFolder :: files($p_dir, '\.xml$', true, true);

		if (count($files) > 0) {

			foreach ($files as $file) {
				$xmlDoc = & JFactory :: getXMLParser();
				$xmlDoc->resolveErrors(true);

				if (!$xmlDoc->loadXML($file, false, true)) {
					// Free up memory from DOMIT parser
					unset ($xmlDoc);
					return false;
				}
				$root = & $xmlDoc->documentElement;

				if ($root->getTagName() != "install" && $root->getTagName() != 'mosinstall') {
					continue;
				}

				$type = $root->getAttribute('type');
				// Free up memory from DOMIT parser
				unset ($xmlDoc);
				return $type;
			}

			JError :: raiseWarning(1, JText :: _('ERRORNOTFINDJOOMLAXMLSETUPFILE'));
			// Free up memory from DOMIT parser
			unset ($xmlDoc);
			return false;
		} else {
			JError :: raiseWarning(1, JText :: _('ERRORNOTFINDXMLSETUPFILE'));
			return false;
		}
	}
	
	/**
	 * Gets a file name out of a url
	 * 
	 * @static
	 * @param string $url URL to get name from
	 * @return mixed String filename or boolean false if failed
	 * @since 1.1
	 */
	function getFilenameFromURL($url) {
		if (is_string($url)) {
			$parts = split('/', $url);
			return $parts[count($parts) - 1];
		}
		return false;
	}

	/**
	 * Clean up temporary uploaded package and unpacked extension
	 *
	 * @static
	 * @param string $p_file Path to the uploaded package file
	 * @param string $resultdir Path to the unpacked extension
	 * @return boolean True on success
	 * @since 1.1
	 */
	function cleanupInstall($p_file, $resultdir) {

		/*
		 * Does the unpacked extension directory exist?
		 */
		if (is_dir($resultdir)) {
			JFolder :: delete($resultdir);
		}
		/*
		 * Is the package file a valid file?
		 */
		if (is_file($p_file)) {
			JFile :: delete($p_file);
		} elseif (is_file(JPath :: clean(JPATH_SITE.DS.'media'.DS.$p_file, false))) {
			/*
			 * It might also be just a base filename
			 */
			JFile :: delete(JPath :: clean(JPATH_SITE.DS.'media'.DS.$p_file, false));
		}
	}
}
?>
