<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Application
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
 * @author 		Andrew Eddie
 * @since		1.5
 */
class JModel extends JObject
{
	/**
	 * The model (base) name
	 *
	 * @var string
	 * @access	protected
	 */
	var $_name;

	/**
	 * Database Connector
	 *
	 * @var object
	 * @access	protected
	 */
	var $_db;

	/**
	 * An state object
	 *
	 * @var string
	 * @access	protected
	 */
	var $_state;

	/**
	 * Constructor
	 *
	 * @since	1.5
	 */
	function __construct($config = array())
	{
		$this->_db	= &JFactory::getDBO();
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

		// set the default view search path
		if (isset($config['table_path'])) {
			// user-defined dirs
			$this->addTablePath($config['table_path']);
		} else if (defined( 'JPATH_COMPONENT_ADMINISTRATOR' )){
			$this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
		}
	}

	/**
	 * Returns a reference to the a Model object, always creating it
	 *
	 * @param	string	The model type to instantiate
	 * @param	string	Prefix for the model class name
	 * @return	mixed	A model object, or false on failure
	 * @since	1.5
	*/
	function &getInstance( $type, $prefix='' )
	{
		$type		= preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$modelClass	= $prefix.ucfirst($type);
		$result		= false;

		if (!class_exists( $modelClass ))
		{
			jimport('joomla.filesystem.path');
			if ($path = JPath::find(JModel::addIncludePath(), strtolower($type).'.php'))
			{
				require_once $path;

				if (!class_exists( $modelClass ))
				{
					JError::raiseWarning( 0, 'Model class ' . $modelClass . ' not found in file.' );
					return $result;
				}
			}
			else
			{
				JError::raiseWarning( 0, 'Model ' . $type . ' not supported. File not found.' );
				return $result;
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
	 * @param	string	Optional parameter name
	 * @return	object	The property where specified, the state object where omitted
	 * @since	1.5
	 */
	function getState($property = null)
	{
		return $property === null ? $this->_state : $this->_state->get($property);
	}

	/**
	 * Method to get the database connector object
	 *
	 * @access	public
	 * @return	object JDatabase connector object
	 * @since	1.5
	 */
	function &getDBO()
	{
		return $this->_db;
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

		if($table = &$this->_createTable( $name, $prefix )) {
			return $table;
		} else {
			JError::raiseError( 0, 'Table ' . $name . ' not supported. File not found.' );
			return null;
		}
	}

	/**
	 * Add a directory where JModel should search for models. You may
	 * either pass a string or an array of directories.
	 *
	 * @access	public
	 * @param	string	A path to search.
	 * @return	array	An array with directory elements
	 * @since	1.5
	 */
	function addIncludePath( $path='' )
	{
		static $paths;

		if (!isset($paths)) {
			$paths = array();
		}
		if (!empty( $path ) && !in_array( $path, $paths )) {
			array_unshift($paths, JPath::clean( $path ));
		}
		return $paths;
	}

	/**
	 * Adds to the stack of model table paths in LIFO order.
	 *
	 * @static
	 * @param	string|array The directory (-ies) to add.
	 * @return	void
	 */
	function addTablePath($path)
	{
		JTable::addIncludePath($path);
	}

	/**
	 * Returns an object list
	 *
	 * @param	string The query
	 * @param	int Offset
	 * @param	int The number of records
	 * @return	array
	 * @access	protected
	 * @since	1.5
	 */
	function &_getList( $query, $limitstart=0, $limit=0 )
	{
		$db =& JFactory::getDBO();
		$db->setQuery( $query, $limitstart, $limit );
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Returns a record count for the query
	 *
	 * @param	string The query
	 * @return	int
	 * @access	protected
	 * @since	1.5
	 */
	function _getListCount( $query )
	{
		$db =& JFactory::getDBO();
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
		$result = null;

		// Clean the model name
		$tableName		= preg_replace( '/[^A-Z0-9_]/i', '', $name );
		$classPrefix	= preg_replace( '/[^A-Z0-9_]/i', '', $prefix );

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
					$result = JError::raiseError( 500, 'Table class ' . $tableClass . ' not found in file.' );
					return $result;
				}
			}
			else {
				return $result;
			}
		}

		$db =& $this->getDBO();
		$result = new $tableClass($db);

		return $result;
	}

	/**
	 * Create the filename for a resource
	 *
	 * @access	private
	 * @param	string 	$type  The resource type to create the filename for
	 * @param	array 	$parts An associative array of filename information
	 * @return	string The filename
	 * @since	1.5
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