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
		//set the view name
		if (empty( $this->_name ))
		{
			if (array_key_exists('name', $config))  {
				$this->_name = $config['name'];
			} else {
				$this->_name = $this->getName();
			}
		}
		
		//set the model state
		if (array_key_exists('state', $config))  {
			$this->_state = $config['state'];
		} else {
			$this->_state = new JObject();
		}
		
		//set the model dbo
		if (array_key_exists('dbo', $config))  {
			$this->_db = $config['dbo'];
		} else {
			$this->_db = &JFactory::getDBO();
		}

		// set the default view search path
		if (array_key_exists('table_path', $config)) {
			$this->addTablePath($config['table_path']);
		} else if (defined( 'JPATH_COMPONENT_ADMINISTRATOR' )){
			$this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
		}
	}

	/**
	 * Returns a reference to the a Model object, always creating it
	 *
	 * @param	string	The model type to instantiate
	 * @param	string	Prefix for the model class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	mixed	A model object, or false on failure
	 * @since	1.5
	*/
	function &getInstance( $type, $prefix = '', $config = array() )
	{
		$type		= preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$modelClass	= $prefix.ucfirst($type);
		$result		= false;

		if (!class_exists( $modelClass ))
		{
			jimport('joomla.filesystem.path');
			$path = JPath::find(
				JModel::addIncludePath(), 
				JModel::_createFileName( 'model', array( 'name' => $type)) 
			);
			if ($path)
			{
				require_once $path;

				if (!class_exists( $modelClass ))
				{
					JError::raiseWarning( 0, 'Model class ' . $modelClass . ' not found in file.' );
					return $result;
				}
			}
			else return $result;
		}

		$result = new $modelClass($config);
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
	 * Method to set the database connector object
	 *
	 * @param	object	$db	A JDatabase based object
	 * @return	void
	 * @since	1.5
	 */
	function setDBO(&$db)
	{
		$this->_db =& $db;
	}

	/**
	 * Method to get the model name
	 *
	 * The model name by default parsed using the classname, or it can be set
	 * by passing a $config['name�] in the class constructor
	 *
	 * @access	public
	 * @return	string The name of the model
	 * @since	1.5
	 */
	function getName()
	{
		$name = $this->_name;

		if (empty( $name ))
		{
			$r = null;
			if (!preg_match('/Model(.*)/i', get_class($this), $r)) {
				JError::raiseError (500, "JModel::getName() : Can't get or parse class name.");
			}
			$name = strtolower( $r[1] );
		}

		return $name;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @access	public
	 * @param	string The table name. Optional.
	 * @param	string The class prefix. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	object	The table
	 * @since	1.5
	 */
	function &getTable($name='', $prefix='Table', $options = array())
	{
		if (empty($name)) {
			$name = $this->getName();
		}

		if($table = &$this->_createTable( $name, $prefix, $options ))  {
			return $table;
		} 
		
		JError::raiseError( 0, 'Table ' . $name . ' not supported. File not found.' );
		$null = null;
        return $null;
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
			jimport('joomla.filesystem.path');
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
		jimport('joomla.database.table');
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
		$this->_db->setQuery( $query, $limitstart, $limit );
		$result = $this->_db->loadObjectList();

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
		$this->_db->setQuery( $query );
		$this->_db->query();

		return $this->_db->getNumRows();
	}

	/**
	 * Method to load and return a model object.
	 *
	 * @access	private
	 * @param	string	The name of the view
	 * @param   string  The class prefix. Optional.
	 * @return	mixed	Model object or boolean false if failed
	 * @since	1.5
	 */
	function &_createTable( $name, $prefix = 'Table', $config = array())
	{
		$result = null;

		// Clean the model name
		$name	= preg_replace( '/[^A-Z0-9_]/i', '', $name );
		$prefix = preg_replace( '/[^A-Z0-9_]/i', '', $prefix );

		//Make sure we are returning a DBO object
		if (!array_key_exists('dbo', $config))  {
			$config['dbo'] =& $this->getDBO();;
		} 
		
		$instance =& JTable::getInstance($name, $prefix, $config );
		return $instance;
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
			case 'model':
				$filename = strtolower($parts['name']).'.php';
				break;

		}
		return $filename;
	}
}
