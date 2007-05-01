<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport('joomla.filesystem.*');

/**
 * Joomla base installer class
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstaller extends JObject
{
	/**
	 * Array of paths needed by the installer
	 * @var array
	 */
	var $_paths = array();

	/**
	 * The installation manifest XML object
	 * @var object
	 */
	var $_manifest = null;

	/**
	 * True if existing files can be overwritten
	 * @var boolean
	 */
	var $_overwrite = false;

	/**
	 * A database connector object
	 * @var object
	 */
	var $_db = null;

	/**
	 * Associative array of package installer handlers
	 * @var array
	 */
	var $_adapters = array();

	/**
	 * Stack of installation steps
	 * 	- Used for installation rollback
	 * @var array
	 */
	var $_stepStack = array();

	/**
	 * The output from the install/uninstall scripts
	 * @var string
	 */
	var $message = null;

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	function __construct()
	{
		$this->_db =& JFactory::getDBO();
	}

	/**
	 * Returns a reference to the global Installer object, only creating it
	 * if it doesn't already exist.
	 *
	 * @static
	 * @return	object	An installer object
	 * @since 1.5
	 */
	function &getInstance()
	{
		static $instance;

		if (!isset ($instance)) {
			$instance = new JInstaller();
		}
		return $instance;
	}

	/**
	 * Get the allow overwrite switch
	 *
	 * @access	public
	 * @return	boolean	Allow overwrite switch
	 * @since	1.5
	 */
	function getOverwrite()
	{
		return $this->_overwrite;
	}

	/**
	 * Set the allow overwrite switch
	 *
	 * @access	public
	 * @param	boolean	$state	Overwrite switch state
	 * @return	boolean	Previous value
	 * @since	1.5
	 */
	function setOverwrite($state=false)
	{
		$tmp = $this->_overwrite;
		if ($state) {
			$this->_overwrite = true;
		} else {
			$this->_overwrite = false;
		}
		return $tmp;
	}

	/**
	 * Get the database connector object
	 *
	 * @access	public
	 * @return	object	Database connector object
	 * @since	1.5
	 */
	function &getDBO()
	{
		return $this->_db;
	}

	/**
	 * Get the installation manifest object
	 *
	 * @access	public
	 * @return	object	Manifest object
	 * @since	1.5
	 */
	function &getManifest()
	{
		if (!is_object($this->_manifest)) {
			$this->_findManifest();
		}
		return $this->_manifest;
	}

	/**
	 * Get an installer path by name
	 *
	 * @access	public
	 * @param	string	$name		Path name
	 * @param	string	$default	Default value
	 * @return	string	Path
	 * @since	1.5
	 */
	function getPath($name, $default=null)
	{
		return (!empty($this->_paths[$name])) ? $this->_paths[$name] : $default;
	}

	/**
	 * Sets an installer path by name
	 *
	 * @access	public
	 * @param	string	$name	Path name
	 * @param	string	$value	Path
	 * @return	void
	 * @since	1.5
	 */
	function setPath($name, $value)
	{
		$this->_paths[$name] = $value;
	}

	/**
	 * Pushes a step onto the installer stack for rolling back steps
	 *
	 * @access	public
	 * @param	array	$step	Installer step
	 * @return	void
	 * @since	1.5
	 */
	function pushStep($step)
	{
		$this->_stepStack[] = $step;
	}

	/**
	 * Set an installer adapter by name
	 *
	 * @access	public
	 * @param	string	$name		Adapter name
	 * @param	object	$adapter	Installer adapter object
	 * @return	boolean True if successful
	 * @since	1.5
	 */
	function setAdapter($name, $adapter=null)
	{
		if (!is_object($adapter)) {
			// Try to load the adapter object
			jimport('joomla.installer.adapters.'.strtolower($name));
			$class = 'JInstaller'.ucfirst($name);
			if (!class_exists($class)) {
				return false;
			}
			$adapter = new $class($this);
		}
		$this->_adapters[$name] =& $adapter;
		return true;
	}

	/**
	 * Installation abort method
	 *
	 * @access	public
	 * @param	string	$msg	Abort message from the installer
	 * @param	string	$type	Package type if defined
	 * @return	boolean	True if successful
	 * @since	1.5
	 */
	function abort($msg=null, $type=null)
	{
		// Initialize variables
		$retval = true;
		$step = array_pop($this->_stepStack);

		// Raise abort warning
		if ($msg) {
			JError::raiseWarning(100, $msg);
		}

		while ($step != null)
		{
			switch ($step['type'])
			{
				case 'file' :
					// remove the file
					$stepval = JFile::delete($step['path']);
					break;

				case 'folder' :
					// remove the folder
					$stepval = JFolder::delete($step['path']);
					break;

				case 'query' :
					// placeholder in case this is necessary in the future
					break;

				default :
					if ($type && is_object($this->_adapters[$type])) {
						// Build the name of the custom rollback method for the type
						$method = '_rollback_'.$step['type'];
						// Custom rollback method handler
						if (method_exists($this->_adapters[$type], $method)) {
							$stepval = $this->_adapters[$type]->$method($step);
						}
					}
					break;
			}

			// Only set the return value if it is false
			if ($stepval === false) {
				$retval = false;
			}

			// Get the next step and continue
			$step = array_pop($this->_stepStack);
		}

		return $retval;
	}

	/**
	 * Package installation method
	 *
	 * @access	public
	 * @param	string	$path	Path to package source folder
	 * @return	boolean	True if successful
	 * @since	1.5
	 */
	function install($path=null)
	{
		if ($path && JFolder::exists($path)) {
			$this->setPath('source', $path);
		} else {
			return $this->abort(JText::_('Install path does not exist'));
		}

		if (!$this->setupInstall()) {
			return $this->abort(JText::_('Unable to detect manifest file'));
		}

		/*
		 * LEGACY CHECK
		 */
		$root		=& $this->_manifest->document;
		$version	= $root->attributes('version');
		$rootName	= $root->name();
		$config		= &JFactory::getConfig();
		if ((version_compare($version, '1.5', '<') || $rootName == 'mosinstall') && !$config->getValue('config.legacy')) {
			return $this->abort(JText::_('MUSTENABLELEGACY'));
		}

		$type = $root->attributes('type');

		// Needed for legacy reasons ... to be deprecated in next minor release
		if ($type == 'mambot') {
			$type = 'plugin';
		}

		if (is_object($this->_adapters[$type])) {
			return $this->_adapters[$type]->install();
		}
		return false;
	}

	/**
	 * Package update method
	 *
	 * @access	public
	 * @param	string	$path	Path to package source folder
	 * @return	boolean	True if successful
	 * @since	1.5
	 */
	function update($path=null)
	{
		if ($path && JFolder::exists($path)) {
			$this->setPath('source', $path);
		} else {
			$this->abort(JText::_('Update path does not exist'));
		}

		if (!$this->setupInstall()) {
			return $this->abort(JText::_('Unable to detect manifest file'));
		}

		/*
		 * LEGACY CHECK
		 */
		$root		=& $this->_manifest->document;
		$version	= $root->attributes('version');
		$rootName	= $root->name();
		$config		= &JFactory::getConfig();
		if ((version_compare($version, '1.5', '<') || $rootName == 'mosinstall') && !$config->getValue('config.legacy')) {
			return $this->abort(JText::_('MUSTENABLELEGACY'));
		}

		$type = $root->attributes('type');

		// Needed for legacy reasons ... to be deprecated in next minor release
		if ($type == 'mambot') {
			$type = 'plugin';
		}

		if (is_object($this->_adapters[$type])) {
			$this->_adapters[$type]->update();
		}
	}

	/**
	 * Package uninstallation method
	 *
	 * @access	public
	 * @param	string	$type	Package type
	 * @param	mixed	$identifier	Package identifier for adapter
	 * @param	int		$cid	Application ID
	 * @return	boolean	True if successful
	 * @since	1.5
	 */
	function uninstall($type, $identifier, $cid=0)
	{
		if (!isset($this->_adapters[$type]) || !is_object($this->_adapters[$type])) {
			if (!$this->setAdapter($type)) {
				return false;
			}
		}
		if (is_object($this->_adapters[$type])) {
			$this->_adapters[$type]->uninstall($identifier, $cid);
		}
	}

	/**
	 * Prepare for installation: this method sets the installation directory, finds
	 * and checks the installation file and verifies the installation type
	 *
	 * @access public
	 * @return boolean True on success
	 * @since 1.0
	 */
	function setupInstall()
	{
		// We need to find the installation manifest file
		if (!$this->_findManifest()) {
			return false;
		}

		// Load the adapter(s) for the install manifest
		$root =& $this->_manifest->document;
		$type = $root->attributes('type');

		// Needed for legacy reasons ... to be deprecated in next minor release
		if ($type == 'mambot') {
			$type = 'plugin';
		}

		// Lazy load the adapter
		if (!isset($this->_adapters[$type]) || !is_object($this->_adapters[$type])) {
			if (!$this->setAdapter($type)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Backward compatible Method to parse through a queries element of the
	 * installation manifest file and take appropriate action.
	 *
	 * @access	public
	 * @param	object	$element 	The xml node to process
	 * @return	mixed	Number of queries processed or False on error
	 * @since	1.5
	 */
	function parseQueries($element)
	{
		// Get the database connector object
		$db = & $this->_db;

		if (!is_a($element, 'JSimpleXMLElement') || !count($element->children())) {
			// Either the tag does not exist or has no children therefore we return zero files processed.
			return 0;
		}

		// Get the array of query nodes to process
		$queries = $element->children();
		if (count($queries) == 0) {
			// No queries to process
			return 0;
		}

		// Process each query in the $queries array (children of $tagName).
		foreach ($queries as $query)
		{
			$db->setQuery($query->data());
			if (!$db->query()) {
				JError::raiseWarning(1, 'JInstaller::install: '.JText::_('SQL Error')." ".$db->stderr(true));
				return false;
			}
		}
		return (int) count($queries);
	}

	/**
	 * Method to extract the name of a discreet installation sql file from the installation manifest file.
	 *
	 * @access	public
	 * @param	object	$element 	The xml node to process
	 * @param	string	$version	The database connector to use
	 * @return	mixed	Number of queries processed or False on error
	 * @since	1.5
	 */
	function parseSQLFiles($element)
	{
		// Initialize variables
		$queries = array();
		$db = & $this->_db;
		$dbDriver = strtolower($db->get('name'));
		if ($dbDriver == 'mysqli') {
			$dbDriver = 'mysql';
		}
		$dbCharset = ($db->hasUTF()) ? 'utf8' : '';

		if (!is_a($element, 'JSimpleXMLElement')) {
			// The tag does not exist.
			return 0;
		}

		// Get the array of file nodes to process
		$files = $element->children();
		if (count($files) == 0) {
			// No files to process
			return 0;
		}

		// Get the name of the sql file to process
		$sqlfile = '';
		foreach ($files as $file)
		{
			$fCharset = (strtolower($file->attributes('charset')) == 'utf8') ? 'utf8' : '';
			$fDriver  = strtolower($file->attributes('driver'));
			if ($fDriver == 'mysqli') {
				$fDriver = 'mysql';
			}

			if( $fCharset == $dbCharset && $fDriver == $dbDriver) {
				$sqlfile = $file->data();
				// Check that sql files exists before reading. Otherwise raise error for rollback
				if ( !file_exists( $this->getPath('extension_administrator').DS.$sqlfile ) ) {
					return false;
				}
				$buffer = file_get_contents($this->getPath('extension_administrator').DS.$sqlfile);

				// Graceful exit and rollback if read not successful
				if ( $buffer === false ) {
					return false;
				}

				// Create an array of queries from the sql file
				$queries = JInstallerHelper::splitSql($buffer);

				if (count($queries) == 0) {
					// No queries to process
					return 0;
				}

				// Process each query in the $queries array (split out of sql file).
				foreach ($queries as $query)
				{
					$query = trim($query);
					if ($query != '' && $query{0} != '#') {
						$db->setQuery($query);
						if (!$db->query()) {
							JError::raiseWarning(1, 'JInstaller::install: '.JText::_('SQL Error')." ".$db->stderr(true));
							return false;
						}
					}
				}
			}
		}

		return (int) count($queries);
	}

	/**
	 * Method to parse through a files element of the installation manifest and take appropriate
	 * action.
	 *
	 * @access	public
	 * @param	object	$element 	The xml node to process
	 * @param	int		$cid		Application ID of application to install to
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function parseFiles($element, $cid=0)
	{
		// Initialize variables
		$copyfiles = array ();

		// Get the client info
		jimport('joomla.application.helper');
		$client = JApplicationHelper::getClientInfo($cid);

		if (!is_a($element, 'JSimpleXMLElement') || !count($element->children())) {
			// Either the tag does not exist or has no children therefore we return zero files processed.
			return 0;
		}

		// Get the array of file nodes to process
		$files = $element->children();
		if (count($files) == 0) {
			// No files to process
			return 0;
		}

		/*
		 * Here we set the folder we are going to remove the files from.  There are a few
		 * special cases that need to be considered for certain reserved tags.
		 *
		 * 	- 'media' Files are copied to the JPATH_BASE/images/stories/ folder
		 * 	- 'languages' Files are copied to JPATH_BASE/languages/ folder
		 */
		switch ($element->name())
		{
			case 'media':
				if ($element->attributes('destination')) {
					$folder = $element->attributes('destination');
				} else {
					$folder = 'stories';
				}
				$destintion = $client->path.DS.'images'.DS.$folder;
				break;

			case 'languages':
				$destination = $client->path.DS.'language';
				break;

			default:
				if ($client) {
					$pathname = 'extension_'.$client->name;
					$destination = $this->getPath($pathname);
				} else {
					$pathname = 'extension_root';
					$destination = $this->getPath($pathname);
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
		if ($folder = $element->attributes('folder')) {
			$source = $this->getPath('source').DS.$folder;
		} else {
			$source = $this->getPath('source');
		}

		// Process each file in the $files array (children of $tagName).
		foreach ($files as $file) {
			/*
			 * If the file is a language, we must handle it differently.  Language files
			 * go in a subdirectory based on the language code, ie.
			 *
			 * 		<language tag="en-US">en-US.mycomponent.ini</language>
			 *
			 * would go in the en-US subdirectory of the languages directory.
			 *
			 * We will only install language files where a core language pack
			 * already exists.
			 */
			if ($file->name() == 'language' && $file->attributes('tag') != '') {
				$path['src']	= $source.DS.$file->data();
				$path['dest']	= $destination.DS.$file->attributes('tag').DS.basename($file->data());

				// If the language folder is not present, then the core pack hasn't been installed... ignore
				if (!JFolder::exists(dirname($path['dest']))) {
					continue;
				}
			} else {
				$path['src']	= $source.DS.$file->data();
				$path['dest']	= $destination.DS.$file->data();
			}

			// Is this path a file or folder?
			$path['type']	= ( $file->name() == 'folder') ? 'folder' : 'file';

			/*
			 * Before we can add a file to the copyfiles array we need to ensure
			 * that the folder we are copying our file to exits and if it doesn't,
			 * we need to create it.
			 */
			if (basename($path['dest']) != $path['dest']) {
				$newdir = dirname($path['dest']);

				if (!JFolder::create($newdir)) {
					JError::raiseWarning(1, 'JInstaller::install: '.JText::_('Failed to create directory').' "'.$newdir.'"');
					return false;
				}
			}

			// Add the file to the copyfiles array
			$copyfiles[] = $path;
		}

		return $this->copyFiles($copyfiles);
	}

	/**
	 * Method to parse through a languages element of the installation manifest and take appropriate
	 * action.
	 *
	 * @access	public
	 * @param	object	$element 	The xml node to process
	 * @param	int		$cid		Application ID of application to install to
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function parseLanguages($element, $cid=0)
	{
		// Initialize variables
		$copyfiles = array ();

		// Get the client info
		jimport('joomla.application.helper');
		$client = JApplicationHelper::getClientInfo($cid);

		if (!is_a($element, 'JSimpleXMLElement') || !count($element->children())) {
			// Either the tag does not exist or has no children therefore we return zero files processed.
			return 0;
		}

		// Get the array of file nodes to process
		$files = $element->children();
		if (count($files) == 0) {
			// No files to process
			return 0;
		}

		/*
		 * Here we set the folder we are going to copy the files to.
		 *
		 * 'languages' Files are copied to JPATH_BASE/language/ folder
		 */
		$destination = $client->path.DS.'language';

		/*
		 * Here we set the folder we are going to copy the files from.
		 *
		 * Does the element have a folder attribute?
		 *
		 * If so this indicates that the files are in a subdirectory of the source
		 * folder and we should append the folder attribute to the source path when
		 * copying files.
		 */
		if ($folder = $element->attributes('folder')) {
			$source = $this->getPath('source').DS.$folder;
		} else {
			$source = $this->getPath('source');
		}

		// Process each file in the $files array (children of $tagName).
		foreach ($files as $file) {
			/*
			 * Language files go in a subfolder based on the language code, ie.
			 *
			 * 		<language tag="en-US">en-US.mycomponent.ini</language>
			 *
			 * would go in the en-US subdirectory of the language folder.
			 *
			 * We will only install language files where a core language pack
			 * already exists.
			 */
			if ($file->attributes('tag') != '') {
				$path['src']	= $source.DS.$file->data();
				$path['dest']	= $destination.DS.$file->attributes('tag').DS.basename($file->data());

				// If the language folder is not present, then the core pack hasn't been installed... ignore
				if (!JFolder::exists(dirname($path['dest']))) {
					continue;
				}
			} else {
				$path['src']	= $source.DS.$file->data();
				$path['dest']	= $destination.DS.$file->data();
			}

			/*
			 * Before we can add a file to the copyfiles array we need to ensure
			 * that the folder we are copying our file to exits and if it doesn't,
			 * we need to create it.
			 */
			if (basename($path['dest']) != $path['dest']) {
				$newdir = dirname($path['dest']);

				if (!JFolder::create($newdir)) {
					JError::raiseWarning(1, 'JInstaller::install: '.JText::_('Failed to create directory').' "'.$newdir.'"');
					return false;
				}
			}

			// Add the file to the copyfiles array
			$copyfiles[] = $path;
		}

		return $this->copyFiles($copyfiles);
	}

	/**
	 * Method to parse through a media element of the installation manifest and take appropriate
	 * action.
	 *
	 * @access	public
	 * @param	object	$element 	The xml node to process
	 * @param	int		$cid		Application ID of application to install to
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function parseMedia($element, $cid=0)
	{
		// Initialize variables
		$copyfiles = array ();

		// Get the client info
		jimport('joomla.application.helper');
		$client = JApplicationHelper::getClientInfo($cid);

		if (!is_a($element, 'JSimpleXMLElement') || !count($element->children())) {
			// Either the tag does not exist or has no children therefore we return zero files processed.
			return 0;
		}

		// Get the array of file nodes to process
		$files = $element->children();
		if (count($files) == 0) {
			// No files to process
			return 0;
		}

		/*
		 * Here we set the folder we are going to copy the files to.
		 * 	Default 'media' Files are copied to the JPATH_BASE/images folder
		 */
		$folder = ($element->attributes('destination')) ? DS.$element->attributes('destination') : null;
		$destination = JPath::clean($client->path.DS.'images'.$folder);

		/*
		 * Here we set the folder we are going to copy the files from.
		 *
		 * Does the element have a folder attribute?
		 *
		 * If so this indicates that the files are in a subdirectory of the source
		 * folder and we should append the folder attribute to the source path when
		 * copying files.
		 */
		if ($folder = $element->attributes('folder')) {
			$source = $this->getPath('source').DS.$folder;
		} else {
			$source = $this->getPath('source');
		}

		// Process each file in the $files array (children of $tagName).
		foreach ($files as $file)
		{
			$path['src']	= $source.DS.$file->data();
			$path['dest']	= $destination.DS.$file->data();

			/*
			 * Before we can add a file to the copyfiles array we need to ensure
			 * that the folder we are copying our file to exits and if it doesn't,
			 * we need to create it.
			 */
			if (basename($path['dest']) != $path['dest']) {
				$newdir = dirname($path['dest']);

				if (!JFolder::create($newdir)) {
					JError::raiseWarning(1, 'JInstaller::install: '.JText::_('Failed to create directory').' "'.$newdir.'"');
					return false;
				}
			}

			// Add the file to the copyfiles array
			$copyfiles[] = $path;
		}

		return $this->copyFiles($copyfiles);
	}

	/**
	 * Method to parse the parameters of an extension, build the INI
	 * string for it's default parameters, and return the INI string.
	 *
	 * @access	public
	 * @return	string	INI string of parameter values
	 * @since	1.5
	 */
	function getParams()
	{
		// Get the manifest document root element
		$root = & $this->_manifest->document;

		// Get the element of the tag names
		$element =& $root->getElementByPath('params');
		if (!is_a($element, 'JSimpleXMLElement') || !count($element->children())) {
			// Either the tag does not exist or has no children therefore we return zero files processed.
			return null;
		}

		// Get the array of parameter nodes to process
		$params = $element->children();
		if (count($params) == 0) {
			// No params to process
			return null;
		}

		// Process each parameter in the $params array.
		$ini = null;
		foreach ($params as $param) {
			if (!$name = $param->attributes('name')) {
				continue;
			}

			if (!$value = $param->attributes('default')) {
				continue;
			}

			$ini .= $name."=".$value."\n";
		}
		return $ini;
	}

	/**
	 * Copy files from source directory to the target directory
	 *
	 * @access	public
	 * @param	array $files array with filenames
	 * @param	boolean $overwrite True if existing files can be replaced
	 * @return	boolean True on success
	 * @since	1.5
	 */
	function copyFiles($files, $overwrite=null)
	{
		/*
		 * To allow for manual override on the overwriting flag, we check to see if
		 * the $overwrite flag was set and is a boolean value.  If not, use the object
		 * allowOverwrite flag.
		 */
		if (is_null($overwrite) || !is_bool($overwrite)) {
			$overwrite = $this->_overwrite;
		}

		/*
		 * $files must be an array of filenames.  Verify that it is an array with
		 * at least one file to copy.
		 */
		if (is_array($files) && count($files) > 0)
		{
			foreach ($files as $file)
			{
				// Get the source and destination paths
				$filesource	= JPath::clean($file['src']);
				$filedest	= JPath::clean($file['dest']);
				$filetype	= array_key_exists('type', $file) ? $file['type'] : 'file';

				if (!file_exists($filesource)) {
					/*
					 * The source file does not exist.  Nothing to copy so set an error
					 * and return false.
					 */
					JError::raiseWarning(1, 'JInstaller::install: '.JText::sprintf('File does not exist', $filesource));
					return false;
				} elseif (file_exists($filedest) && !$overwrite) {

						/*
						 * It's okay if the manifest already exists
						 */
						if ($this->getPath( 'manifest' ) == $filesource) {
							continue;
						}

						/*
						 * The destination file already exists and the overwrite flag is false.
						 * Set an error and return false.
						 */
						JError::raiseWarning(1, 'JInstaller::install: '.JText::sprintf('WARNSAME', $filedest));
						return false;
				} else {

					// Copy the folder or file to the new location.
					if ( $filetype == 'folder') {

						if (!(JFolder::copy($filesource, $filedest, null, $overwrite))) {
							JError::raiseWarning(1, 'JInstaller::install: '.JText::sprintf('Failed to copy folder to', $filesource, $filedest));
							return false;
						}

						$step = array ('type' => 'folder', 'path' => $filedest);
					} else {

						if (!(JFile::copy($filesource, $filedest))) {
							JError::raiseWarning(1, 'JInstaller::install: '.JText::sprintf('Failed to copy file to', $filesource, $filedest));
							return false;
						}

						$step = array ('type' => 'file', 'path' => $filedest);
					}

					/*
					 * Since we copied a file/folder, we want to add it to the installation step stack so that
					 * in case we have to roll back the installation we can remove the files copied.
					 */
					$this->_stepStack[] = $step;
				}
			}
		} else {

			/*
			 * The $files variable was either not an array or an empty array
			 */
			return false;
		}
		return count($files);
	}

	/**
	 * Method to parse through a files element of the installation manifest and remove
	 * the files that were installed
	 *
	 * @access	public
	 * @param	object	$element 	The xml node to process
	 * @param	int		$cid		Application ID of application to remove from
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function removeFiles($element, $cid=0)
	{
		// Initialize variables
		$removefiles = array ();
		$retval = true;

		// Get the client info
		jimport('joomla.application.helper');
		$client = JApplicationHelper::getClientInfo($cid);

		if (!is_a($element, 'JSimpleXMLElement') || !count($element->children())) {
			// Either the tag does not exist or has no children therefore we return zero files processed.
			return true;
		}

		// Get the array of file nodes to process
		$files = $element->children();
		if (count($files) == 0) {
			// No files to process
			return true;
		}

		/*
		 * Here we set the folder we are going to remove the files from.  There are a few
		 * special cases that need to be considered for certain reserved tags.
		 */
		switch ($element->name())
		{
			case 'media':
				if ($element->attributes('destination')) {
					$folder = $element->attributes('destination');
				} else {
					$folder = 'stories';
				}
				$source = $client->path.DS.'images'.DS.$folder;
				break;

			case 'languages':
				$source = $client->path.DS.'language';
				break;

			default:
				if ($client) {
					$pathname = 'extension_'.$client->name;
					$source = $this->getPath($pathname);
				} else {
					$pathname = 'extension_root';
					$source = $this->getPath($pathname);
				}
				break;
		}

		// Process each file in the $files array (children of $tagName).
		foreach ($files as $file)
		{
			/*
			 * If the file is a language, we must handle it differently.  Language files
			 * go in a subdirectory based on the language code, ie.
			 *
			 * 		<language tag="en_US">en_US.mycomponent.ini</language>
			 *
			 * would go in the en_US subdirectory of the languages directory.
			 */
			if ($file->name() == 'language' && $file->attributes('tag') != '') {
				$path = $source.DS.$file->attributes('tag').DS.basename($file->data());
			} else {
				$path = $source.DS.$file->data();
			}

			/*
			 * Actually delete the files/folders
			 */
			if (is_dir($path)) {
				$val = JFolder::delete($path);
			} else {
				$val = JFile::delete($path);
			}

			if ($val === false) {
				$retval = false;
			}
		}

		return $retval;
	}

	/**
	 * Copies the installation manifest file to the extension folder in the given client
	 *
	 * @access	public
	 * @param	int		$cid	Where to copy the installfile [optional: defaults to 1 (admin)]
	 * @return	boolean	True on success, False on error
	 * @since	1.5
	 */
	function copyManifest($cid=1)
	{
		// Get the client info
		jimport('joomla.application.helper');
		$client = JApplicationHelper::getClientInfo($cid);

		$path['src'] = $this->getPath('manifest');

		if ($client) {
			$pathname = 'extension_'.$client->name;
			$path['dest']  = $this->getPath($pathname).DS.basename($this->getPath('manifest'));
		} else {
			$pathname = 'extension_root';
			$path['dest']  = $this->getPath($pathname).DS.basename($this->getPath('manifest'));
		}
		return $this->copyFiles(array ($path), true);
	}

	/**
	 * Tries to find the package manifest file
	 *
	 * @access private
	 * @return boolean True on success, False on error
	 * @since 1.0
	 */
	function _findManifest()
	{
		// Get an array of all the xml files from teh installation directory
		$xmlfiles = JFolder::files($this->getPath('source'), '.xml$', true, true);
		// If at least one xml file exists
		if (count($xmlfiles) > 0) {
			foreach ($xmlfiles as $file)
			{
				// Is it a valid joomla installation manifest file?
				$manifest = $this->_isManifest($file);
				if (!is_null($manifest)) {

					// If the root method attribute is set to upgrade, allow file overwrite
					$root =& $manifest->document;
					if ($root->attributes('method') == 'upgrade') {
						$this->_overwrite = true;
					}

					// Set the manifest object and path
					$this->_manifest =& $manifest;
					$this->setPath('manifest', $file);

					// Set the installation source path to that of the manifest file
					$this->setPath('source', dirname($file));
					return true;
				}
			}

			// None of the xml files found were valid install files
			JError::raiseWarning(1, 'JInstaller::install: '.JText::_('ERRORJOSXMLSETUP'));
			return false;
		} else {
			// No xml files were found in the install folder
			JError::raiseWarning(1, 'JInstaller::install: '.JText::_('ERRORXMLSETUP'));
			return false;
		}
	}

	/**
	 * Is the xml file a valid Joomla installation manifest file
	 *
	 * @access	private
	 * @param	string	$file	An xmlfile path to check
	 * @return	mixed	A JSimpleXML document, or null if the file failed to parse
	 * @since	1.5
	 */
	function &_isManifest($file)
	{
		// Initialize variables
		$null	= null;
		$xml	=& JFactory::getXMLParser('Simple');

		// If we cannot load the xml file return null
		if (!$xml->loadFile($file)) {
			// Free up xml parser memory and return null
			unset ($xml);
			return $null;
		}

		/*
		 * Check for a valid XML root tag.
		 * @todo: Remove backwards compatability in a future version
		 * Should be 'install', but for backward compatability we will accept 'mosinstall'.
		 */
		$root =& $xml->document;
		if ($root->name() != 'install' && $root->name() != 'mosinstall') {
			// Free up xml parser memory and return null
			unset ($xml);
			return $null;
		}

		// Valid manifest file return the object
		return $xml;
	}
}

/**
 * Installer helper class
 *
 * @static
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstallerHelper
{
	/**
	 * Downloads a package
	 *
	 * @static
	 * @param string URL of file to download
	 * @param string Download target filename [optional]
	 * @return mixed Path to downloaded package or boolean false on failure
	 * @since 1.5
	 */
	function downloadPackage($url, $target = false)
	{
		$config =& JFactory::getConfig();

		// Capture PHP errors
		$php_errormsg = 'Error Unknown';
		ini_set('track_errors', true);

		// Set user agent
		ini_set('user_agent', "Joomla! 1.5 Installer");

		// Open the remote server socket for reading
		$inputHandle = @ fopen($url, "r");
		if (!$inputHandle) {
			JError::raiseWarning(42, 'Remote Server connection failed: '.$php_errormsg);
			return false;
		}

		$meta_data = stream_get_meta_data($inputHandle);
		foreach ($meta_data['wrapper_data'] as $wrapper_data)
		{
			if (substr($wrapper_data, 0, strlen("Content-Disposition")) == "Content-Disposition") {
				$contentfilename = explode ("\"", $wrapper_data);
				$target = $contentfilename[1];
			}
		}

		// Set the target path if not given
		if (!$target) {
			$target = $config->getValue('config.tmp_path').DS.JInstallerHelper::getFilenameFromURL($url);
		} else {
			$target = $config->getValue('config.tmp_path').DS.basename($target);
		}

		// Initialize contents buffer
		$contents = null;

		while (!feof($inputHandle))
		{
			$contents .= fread($inputHandle, 4096);
			if ($contents == false) {
				JError::raiseWarning(44, 'Failed reading network resource: '.$php_errormsg);
				return false;
			}
		}

		// Write buffer to file
		JFile::write($target, $contents);

		// Close file pointer resource
		fclose($inputHandle);

		// Return the name of the downloaded package
		return basename($target);
	}

	/**
	 * Unpacks a file and verifies it as a Joomla element package
	 * Supports .gz .tar .tar.gz and .zip
	 *
	 * @static
	 * @param string $p_filename The uploaded package filename or install directory
	 * @return boolean True on success, False on error
	 * @since 1.5
	 */
	function unpack($p_filename)
	{
		// Path to the archive
		$archivename = $p_filename;

		// Temporary folder to extract the archive into
		$tmpdir = uniqid('install_');

		// Clean the paths to use for archive extraction
		$extractdir = JPath::clean(dirname($p_filename).DS.$tmpdir);
		$archivename = JPath::clean($archivename);

		// do the unpacking of the archive
		$result = JArchive::extract( $archivename, $extractdir);

		if ( $result === false ) {
			return false;
		}


		/*
		 * Lets set the extraction directory and package file in the result array so we can
		 * cleanup everything properly later on.
		 */
		$retval['extractdir'] = $extractdir;
		$retval['packagefile'] = $archivename;

		/*
		 * Try to find the correct install directory.  In case the package is inside a
		 * subdirectory detect this and set the install directory to the correct path.
		 *
		 * List all the items in the installation directory.  If there is only one, and
		 * it is a folder, then we will set that folder to be the installation folder.
		 */
		$dirList = array_merge(JFolder::files($extractdir, ''), JFolder::folders($extractdir, ''));

		if (count($dirList) == 1)
		{
			if (JFolder::exists($extractdir.DS.$dirList[0]))
			{
				$extractdir = JPath::clean($extractdir.DS.$dirList[0]);
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
		if ($retval['type'] = JInstallerHelper::detectType($extractdir))
		{
			return $retval;
		} else
		{
			return false;
		}
	}

	/**
	 * Method to detect the extension type from a package directory
	 *
	 * @static
	 * @param string $p_dir Path to package directory
	 * @return mixed Extension type string or boolean false on fail
	 * @since 1.5
	 */
	function detectType($p_dir)
	{
		// Search the install dir for an xml file
		$files = JFolder::files($p_dir, '\.xml$', true, true);

		if (count($files) > 0)
		{

			foreach ($files as $file)
			{
				$xmlDoc = & JFactory::getXMLParser();
				$xmlDoc->resolveErrors(true);

				if (!$xmlDoc->loadXML($file, false, true))
				{
					// Free up memory from DOMIT parser
					unset ($xmlDoc);
					continue;
				}
				$root = & $xmlDoc->documentElement;

				if ($root->getTagName() != "install" && $root->getTagName() != 'mosinstall')
				{
					continue;
				}

				$type = $root->getAttribute('type');
				// Free up memory from DOMIT parser
				unset ($xmlDoc);
				return $type;
			}

			JError::raiseWarning(1, JText::_('ERRORNOTFINDJOOMLAXMLSETUPFILE'));
			// Free up memory from DOMIT parser
			unset ($xmlDoc);
			return false;
		} else
		{
			JError::raiseWarning(1, JText::_('ERRORNOTFINDXMLSETUPFILE'));
			return false;
		}
	}

	/**
	 * Gets a file name out of a url
	 *
	 * @static
	 * @param string $url URL to get name from
	 * @return mixed String filename or boolean false if failed
	 * @since 1.5
	 */
	function getFilenameFromURL($url)
	{
		if (is_string($url)) {
			$parts = explode('/', $url);
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
	 * @since 1.5
	 */
	function cleanupInstall($package, $resultdir)
	{
		$config =& JFactory::getConfig();

		// Does the unpacked extension directory exist?
		if (is_dir($resultdir)) {
			JFolder::delete($resultdir);
		}

		// Is the package file a valid file?
		if (is_file($package)) {
			JFile::delete($package);
		} elseif (is_file(JPath::clean($config->getValue('config.tmp_path').DS.$package))) {
			// It might also be just a base filename
			JFile::delete(JPath::clean($config->getValue('config.tmp_path').DS.$package));
		}
	}

	/**
	 * Splits contents of a sql file into array of discreet queries
	 * queries need to be delimited with end of statement marker ';'
	 * @param string
	 * @return array
	 */
	function splitSql($sql)
	{
		$sql = trim($sql);
		$sql = preg_replace("/\n\#[^\n]*/", '', "\n".$sql);
		$buffer = array ();
		$ret = array ();
		$in_string = false;

		for ($i = 0; $i < strlen($sql) - 1; $i ++)
		{
			if ($sql[$i] == ";" && !$in_string) {
				$ret[] = substr($sql, 0, $i);
				$sql = substr($sql, $i +1);
				$i = 0;
			}

			if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\") {
				$in_string = false;
			} elseif (!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset ($buffer[0]) || $buffer[0] != "\\")) {
				$in_string = $sql[$i];
			} if (isset ($buffer[1])) {
				$buffer[0] = $buffer[1];
			}
			$buffer[1] = $sql[$i];
		}

		if (!empty ($sql)) {
			$ret[] = $sql;
		}
		return ($ret);
	}
}