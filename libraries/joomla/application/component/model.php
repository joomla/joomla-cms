<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* Base class for a Joomla Model
*
* Acts as a Factory class for application specific objects and
* provides many supporting API functions.
*
* @abstract
* @package		Joomla.Framework
* @subpackage	Application
* @since		1.5
*/
class JModel extends JObject
{
	/**
	 * The model (base) name
	 *
	 * @var string
	 * @access protected
	 */
	var $_modelName;

	/**
	 * Database Connector
	 *
	 * @var object
	 * @access protected
	 */
	var $_db;

	/**
	 * An error message
	 *
	 * @var string
	 * @access protected
	 */
	var $_error;

	/**
	 * An state object
	 *
	 * @var string
	 * @access protected
	 */
	var $_state;
	
	/**
	 * The set of search directories for resources (tables)
	 *
	 * @var array
	 * @access protected
	 */
	var $_path = array(
		'table' => array()
	);

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct($options = array())
	{
		global $Itemid;

		$this->_db    = &JFactory::getDBO();
		$this->_state = new JObject();
		
		//set the view name
		if (empty( $this->_name ))
		{
			if (isset($config['name']))  {
				$this->_name = $config['name'];
			}
			else
			{
				$r = null;
				if (!preg_match('/Model(.*)/i', get_class($this), $r)) {
					JError::raiseError (500, "JModel::__construct() : Can't get or parse class name.");
				}
				$this->_name = strtolower( $r[1] );
			}
		}

		// Get menu item information if Itemid exists (wrapping it this way allows for JModel usage outside of Joomla! CMS Scope)
		if (isset($Itemid))
		{
			$menu		= &JMenu::getInstance();
			$item		= &$menu->getItem( $Itemid );
			$params	    = &$item->params;

			// Set Default State Data
			$this->_state->set( 'menu.parameters', $params);
		}
		
		// set the default view search path
		if (isset($config['table_path'])) {
			// user-defined dirs
			$this->_setPath('table', $config['table_path']);
		} else {
			$this->setTablePath(null);
		}
	}

	/**
	 * Method to set model state variables
	 *
	 * @access	public
	 * @param	string	The name of the property
	 * @param	mixed	The value of the property to set
	 * @return	mixed	The previous value of the property
	 * @since	1.5
	 */
	function setState( $property, $value=null )
	{
		return $this->_state->set($property, $value);
	}

	/**
	 * Method to get model state variables
	 *
	 * @access	public
	 * @return	object	The model state object
	 * @since	1.5
	 */
	function getState()
	{
		return $this->_state;
	}

	/**
	 * Method to get the database connector object
	 *
	 * @access	public
	 * @return	object JDatabase connector object
	 * @since 1.5
	 */
	function &getDBO()
	{
		return $this->_db;
	}

	/**
	 * Get the error message
	 *
	 * @return string The error message
	 * @since 1.5
	 */
	function getError() {
		return $this->_error;
	}

	/**
	 * Sets the error message
	 *
	 * @param string The error message
	 * @return string The new error message
	 * @since 1.5
	 */
	function setError( $value ) {
		$this->_error = $value;
		return $this->_error;
	}
	
	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @access	public
	 * @param	string The table name
	 * @param	string The class prefix
	 * @return	object	The table
	 * @since	1.5
	 */
	function &getTable($name='', $prefix='Table')
	{
		if (empty($name)) {
			$name = $this->_name;
		}
		
		$table = &$this->_createTable( $name, $prefix );
		return $table;
	}
	
	/**
     * Adds to the stack of view table paths in LIFO order.
     *
     * @param string|array The directory (-ies) to add.
     * @return void
     */
    function addTablePath($path)
    {
        $this->_addPath('table', $path);
    }

    /**
     * Resets the stack of model table paths.
     *
     * To clear all paths, use JView::setTemplatePath(null).
     *
     * @param string|array The directory (-ies) to set as the path.
     * @return void
     */
    function setTablePath($path)
    {
        $this->_setPath('table', $path);
    }
	
	/**
	 * Returns an object list
	 *
	 * @param string The query
	 * @param int Offset
	 * @param int The number of records
	 * @return array
	 * @access protected
	 * @since 1.5
	 */
	function &_getList( $query, $limitstart=0, $limit=0 )
	{
		$db = JFactory::getDBO();
		$db->setQuery( $query, $limitstart, $limit );
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Returns a record count for the query
	 *
	 * @param string The query
	 * @return int
	 * @access protected
	 * @since 1.5
	 */
	function _getListCount( $query )
	{
		$db = JFactory::getDBO();
		$db->setQuery( $query );
		$db->query();

		return $db->getNumRows();
	}
	
	/**
	 * Method to load and return a model object.
	 *
	 * @access	private
	 * @param	string	$modelName	The name of the view
	 * @return	mixed	Model object or boolean false if failed
	 * @since	1.5
	 */
	function &_createTable( $name, $prefix = '')
	{
		$false = false;

		// Clean the model name
		$tableName   = preg_replace( '#\W#', '', $name );
		$classPrefix = preg_replace( '#\W#', '', $prefix );

		// Build the model class name
		$tableClass = $classPrefix.$tableName;

		if (!class_exists( $tableClass ))
		{
			// If the model file exists include it and try to instantiate the object
			if ($path = $this->_findFile('table', strtolower($tableName).'.php'))
			{
				require( $path );
				if (!class_exists( $tableClass ))
				{
					JError::raiseWarning( 0, 'Table class ' . $tableClass . ' not found in file.' );
					return $false;
				}
			}
			else
			{
				JError::raiseWarning( 0, 'Table ' . $tableName . ' not supported. File not found.' );
				return $false;
			}
		}
			
		$db =& $this->getDBO();
		$table = new $tableClass($db);
		return $table;
	}
	
	 /**
	* Sets an entire array of search paths for resources.
	*
	* @access protected
	* @param string $type The type of path to set, typically 'view' or 'model.
	* @param string|array $path The new set of search paths.  If null or
	* false, resets to the current directory only.
	*/
	function _setPath($type, $path)
	{
		global $mainframe, $option;

		// clear out the prior search dirs
		$this->_path[$type] = array();

		// always add the fallback directories as last resort
		switch (strtolower($type))
		{
			case 'table':
				// the current directory
				$this->_addPath($type, JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
				break;
		}

		// actually add the user-specified directories
		$this->_addPath($type, $path);
	}

   /**
	* Adds to the search path for tables and resources
	*
	* @access protected
	* @param string|array $path The directory or stream to search.
	*/
	function _addPath($type, $path)
	{
		// convert from path string to array of directories
		if (is_string($path) && ! strpos($path, '://'))
		{
			// the path config is a string, and it's not a stream
			// identifier (the "://" piece). add it as a path string.
			$path = explode(PATH_SEPARATOR, $path);

			// typically in path strings, the first one is expected
			// to be searched first. however, JView uses a stack,
			// so the first would be last.  reverse the path string
			// so that it behaves as expected with path strings.
			$path = array_reverse($path);
		}
		else
		{
			// just force to array
			settype($path, 'array');
		}

		// loop through the path directories
		foreach ($path as $dir)
		{
			// no surrounding spaces allowed!
			$dir = trim($dir);

			// add trailing separators as needed
			if (strpos($dir, '://') && substr($dir, -1) != '/') {
				// stream
				$dir .= '/';
			} elseif (substr($dir, -1) != DIRECTORY_SEPARATOR) {
				// directory
				$dir .= DIRECTORY_SEPARATOR;
			}

			// add to the top of the search dirs
			array_unshift($this->_path[$type], $dir);
		}
	}

   /**
	* Searches the directory paths for a given file.
	*
	* @access protected
	* @param array $type The type of path to search (template or resource).
	* @param string $file The file name to look for.
	*
	* @return string|bool The full path and file name for the target file,
	* or boolean false if the file is not found in any of the paths.
	*/
	function _findFile($type, $file)
	{
		// get the set of paths
		$set = $this->_path[$type];

		// start looping through the path set
		foreach ($set as $path)
		{
			// get the path to the file
			$fullname = $path . $file;

			// is the path based on a stream?
			if (strpos($path, '://') === false)
			{
				// not a stream, so do a realpath() to avoid directory
				// traversal attempts on the local file system.
				$path = realpath($path); // needed for substr() later
				$fullname = realpath($fullname);
			}

			// the substr() check added to make sure that the realpath()
			// results in a directory registered with Savant so that
			// non-registered directores are not accessible via directory
			// traversal attempts.
			if (file_exists($fullname) && is_readable($fullname) &&
				substr($fullname, 0, strlen($path)) == $path)
			{
				return $fullname;
			}
		}

		// could not find the file in the set of paths
		return false;
	}
}
?>