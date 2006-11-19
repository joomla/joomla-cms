<?php
/**
* @version $Id$
* @package Joomla.Framework
* @subpackage Application
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
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @author		Louis Landry <louis.landry@joomla.org>
 * @auhtor 		Andrew Eddie
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
	var $_name;

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
			$this->addTablePath($config['table_path']);
		} else {
			$this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
		}
	}

	/**
	 * Returns a reference to the a Model object, always creating it
	 *
	 * @param	string	The model type to instantiate
	 * @param	string	Prefix for the model class name
	 * @return	object
	 * @since 1.5
	*/
	function &getInstance( $type, $prefix='' )
	{
		$modelClass = $prefix.ucfirst($type);
		$result = false;

		if (!class_exists( $modelClass ))
		{
			jimport('joomla.filesystem.path');
			if($path = JPath::find(JModel::addIncludePath(), strtolower($type).'.php'))
			{
				require_once $path;
				
				if (!class_exists( $modelClass ))
				{
					JError::raiseWarning( 0, 'Model class ' . $modelClass . ' not found in file.' );
					$result = false;
				}
			}
			else
			{
				JError::raiseWarning( 0, 'Model ' . $type . ' not supported. File not found.' );
				$result = false;
			}
		}
		
		$result = new $modelClass();
		return $result;
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
	 * Add a directory where JModel should search for models. You may
	 * either pass a string or an array of directories.
	 *
	 * @access	public
	 * @param	string	A path to search.
	 * @return	array	An array with directory elements
	 * @since 1.5
	 */
	function addIncludePath( $path='' )
	{
		static $paths;

		if (!isset($paths)) {
			$paths = array();
		}
		if (!empty( $path ) && !in_array( $path, $paths )) {
			$paths[] = $path;
		}
		return $paths;
	}
	
	/**
     * Adds to the stack of model table paths in LIFO order.
     *
     * @static
     * @param string|array The directory (-ies) to add.
     * @return void
     */
    function addTablePath($path)
    {
        JTable::addIncludePath($path);
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
	function &_createTable( $name, $prefix = 'Table')
	{
		$false = false;

		// Clean the model name
		$tableName   = preg_replace( '#\W#', '', $name );
		$classPrefix = preg_replace( '#\W#', '', $prefix );

		// Build the model class name
		$tableClass = $classPrefix.$tableName;
			
		if (!class_exists( $tableClass ))
		{
			jimport('joomla.filesystem.path');
			if($path = JPath::find(JTable::addIncludePath(), $this->_createFileName('table', array('name' => $tableName))))
			{
				require_once $path;
				
				if (!class_exists( $tableClass ))
				{
					JError::raiseWarning( 0, 'Table class ' . $tableClass . ' not found in file.' );
					return $false;
				}
			}
			else
			{
				JError::raiseWarning( 0, 'Table ' . $type . ' not supported. File not found.' );
				return $false;
			}
		}
		
		$db =& $this->getDBO();
		$instance = new $tableClass($db);

		return $instance;
	}
	
	/**
	 * Create the filename for a resource
	 *
	 * @access private
	 * @param string 	$type  The resource type to create the filename for
	 * @param array 	$parts An associative array of filename information
	 * @return string The filename
	 * @since 1.5
	 */
	function _createFileName($type, $parts = array())
	{
		$filename = '';
		
		switch($type)
		{
			case 'table' :
				 $filename = strtolower($parts['name']).'.php';
				break;
			 
		}
		return $filename;
	}
}
?>
