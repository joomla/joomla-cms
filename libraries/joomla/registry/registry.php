<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

jimport( 'joomla.common.base.object' );
jimport( 'joomla.registry.format' );

/**
 * JRegistry class
 *
 * @author 		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage 	Registry
 * @since 		1.5
 */
class JRegistry extends JObject
{
	/**
	 * Default NameSpace
	 *
	 * @var string
	 */
	var $_defaultNameSpace = null;

	/**
	 * Registry Object
	 *  - actually an array of namespace objects
	 *
	 * @var array
	 */
	var $_registry = array ();

	/**
	 * Constructor
	 *
	 * @param $defaultNamespace	string 	Default registry namespace
	 */
	function __construct($namespace = 'default')
	{
		$this->_defaultNameSpace = $namespace;
		$this->makeNameSpace($namespace);
	}

	/**
	 * Returns a reference to a global JRegistry object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $registry = &JRegistry::getInstance($id[, $namespace]);</pre>
	 *
	 * @static
	 * @param $id 			string 	An ID for the registry instance
	 * @param $namespace	string 	The default namespace for the registry object [optional]
	 * @return object  		The JRegistry object.
	 * @since 1.5
	 */
	function & getInstance($id, $namespace = 'default')
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$id])) {
			$instances[$id] = & new JRegistry($namespace);
		}

		return $instances[$id];
	}

	/**
	 * Create a namespace
	 *
	 * @access public
	 * @param $namespace 	string 		Name of the namespace to create
	 * @return boolean True on success
	 * @since 1.5
	 */
	function makeNameSpace($namespace)
	{
		$this->_registry[$namespace] = array('data' => new stdClass());
		return true;
	}

	/**
	 * Get a registry value
	 *
	 * @access public
	 * @param 	$regpath	string 	Registry path (e.g. joomla.content.showauthor)
	 * @param 	int    User Id
	 * @return 	mixed Value of entry or boolean false
	 */
	function getValue($regpath)
	{
		// Explode the registry path into an array
		$nodes = explode('.', $regpath);

		// Get the namespace
		$namespace = array_shift($nodes);

		if (!isset($this->_registry[$namespace])) {
			return null;
		}

		$ns = & $this->_registry[$namespace]['data'];
		$pathNodes = count($nodes) - 1;

		for ($i = 0; $i < $pathNodes; $i ++) {
			$ns =& $ns->$nodes[$i];
		}

		if(!isset($ns->$nodes[$i])) {
			return null;
		}

		return $ns->$nodes[$i++];
	}

	/**
	 * Set a registry value
	 *
	 * @access public
	 * @param $regpath	string 	Registry Path (e.g. joomla.content.showauthor)
	 * @param 	mixed Value of entry
	 * @return 	mixed Value of old value or boolean false if operation failed
	 * @since 1.5
	 */
	function setValue($regpath, $value)
	{
		// Explode the registry path into an array
		$nodes = explode('.', $regpath);

		// Get the namespace
		$namespace = array_shift($nodes);
		if (!isset($this->_registry[$namespace])) {
			$this->makeNameSpace($namespace);
		}

		$ns = & $this->_registry[$namespace]['data'];

		$pathNodes = count($nodes) - 1;

		if ($pathNodes < 0)
		{
			$pathNodes = 0;
		}

		for ($i = 0; $i < $pathNodes; $i ++) {

			// If any node along the registry path does not exist, create it
			if (!isset($ns->$nodes[$i])) {
				$ns->$nodes[$i] = new stdClass();
			}
			$ns =& $ns->$nodes[$i];
		}

		// Get the old value if exists so we can return it
		@$retval =& $ns->$nodes[$i];
		$ns->$nodes[$i] =& $value;

		return $retval;
	}

	/**
	 * Load a associative array of values into the default namespace
	 *
	 * @access public
	 * @param &array 		Array 	Associative array of value to load
	 * @param &namepsace 	String	The name of the namespace
	 * @return boolean True on success
	 * @since 1.5
	 */
	function loadArray($array, $namespace = null)
	{
		// If namespace is not set, get the default namespace
		if ($namespace == null) {
			$namespace = $this->_defaultNameSpace;
		}

		if (!isset($this->_registry[$namespace])) {
			// If namespace does not exist, make it and load the data
			$this->makeNameSpace($namespace);
		}

		/*
		 * Here we just load the variables into the registry's default
		 * namespace.
		 */
		foreach ($array as $k => $v) {
			$this->_registry[$this->_defaultNameSpace]['data']->$k = $v;
		}

		return true;
	}

	/**
	 * Load the public variables of the object into the default namespace.
	 *
	 * @access public
	 * @param &object 		stdClass 	The object holding the public vars to load
	 * @param &namespace 	string 		Namespace to load the INI string into [optional]
	 * @return boolean True on success
	 * @since 1.5
	 */
	function loadObject(&$object, $namespace = null)
	{

		// If namespace is not set, get the default namespace
		if ($namespace == null) {
			$namespace = $this->_defaultNameSpace;
		}

		if (!isset($this->_registry[$namespace])) {
			// If namespace does not exist, make it and load the data
			$this->makeNameSpace($namespace);
		}

		/*
		 * We want to leave groups that are already in the namespace and add the
		 * groups loaded into the namespace.  This overwrites any existing group
		 * with the same name
		 */
		foreach (get_object_vars($object) as $k => $v) {
			if (substr($k, 0,1) != '_' || $k == '_name') {
				$this->_registry[$namespace]['data']->$k = $v;
			}
		}

		return true;
	}

	/**
	 * Load the contents of a file into the registry
	 *
	 * @access public
	 * @param $file 		string 		Path to file to load
	 * @param $format		string 		Format of the file [optional: defaults to INI]
	 * @param $namespace	string 		Namespace to load the INI string into [optional]
	 * @return boolean True on success
	 * @since 1.5
	 */
	function loadFile($file, $format = 'INI', $namespace = null)
	{
		// Load a file into the given namespace [or default namespace if not given]
		$handler =& $this->_loadFormat($format);

		// If namespace is not set, get the default namespace
		if ($namespace == null) {
			$namespace = $this->_defaultNameSpace;
		}

		// Get the contents of the file
		jimport('joomla.filesystem.file');
		$data =& JFile::read($file);

		if (!isset($this->_registry[$namespace])) {
			// If namespace does not exist, make it and load the data
			$this->makeNameSpace($namespace);
			$this->_registry[$namespace]['data'] =& $handler->stringToObject($data);
		} else {
			// Get the data in object format
			$ns =& $handler->stringToObject($data);

			/*
			 * We want to leave groups that are already in the namespace and add the
			 * groups loaded into the namespace.  This overwrites any existing group
			 * with the same name
			 */
			foreach (get_object_vars($ns) as $k => $v) {
				$this->_registry[$namespace]['data']->$k = $v;
			}
		}
	}

	/**
	 * Load an XML string into the registry into the given namespace [or default if a namespace is not given]
	 *
	 * @access public
	 * @param $data 		string 		XML formatted string to load into the registry
	 * @param $namespace	string 		Namespace to load the INI string into [optional]
	 * @return boolean True on success
	 * @since 1.5
	 */
	function loadXML($data, $namespace = null)
	{
		// Load a string into the given namespace [or default namespace if not given]
		$handler =& JRegistryFormat::getInstance('XML');

		// If namespace is not set, get the default namespace
		if ($namespace == null) {
			$namespace = $this->_defaultNameSpace;
		}

		if (!isset($this->_registry[$namespace])) {
			// If namespace does not exist, make it and load the data
			$this->makeNameSpace($namespace);
			$this->_registry[$namespace]['data'] =& $handler->stringToObject($data);
		} else {
			// Get the data in object format
			$ns =& $handler->stringToObject($data);

			/*
			 * We want to leave groups that are already in the namespace and add the
			 * groups loaded into the namespace.  This overwrites any existing group
			 * with the same name
			 */
			foreach (get_object_vars($ns) as $k => $v) {
				$this->_registry[$namespace]['data']->$k = $v;
			}
		}
	}

	/**
	 * Load an INI string into the registry into the given namespace [or default if a namespace is not given]
	 *
	 * @access public
	 * @param $data			string 		INI formatted string to load into the registry
	 * @param $namespace	string 		Namespace to load the INI string into [optional]
	 * @return boolean True on success
	 * @since 1.5
	 */
	function loadINI($data, $namespace = null)
	{
		// Load a string into the given namespace [or default namespace if not given]
		$handler =& JRegistryFormat::getInstance('INI');

		// If namespace is not set, get the default namespace
		if ($namespace == null) {
			$namespace = $this->_defaultNameSpace;
		}

		if (!isset($this->_registry[$namespace])) {
			// If namespace does not exist, make it and load the data
			$this->makeNameSpace($namespace);
			$this->_registry[$namespace]['data'] =& $handler->stringToObject($data);
		} else {
			// Get the data in object format
			$ns = $handler->stringToObject($data);

			/*
			 * We want to leave groups that are already in the namespace and add the
			 * groups loaded into the namespace.  This overwrites any existing group
			 * with the same name
			 */
			foreach (get_object_vars($ns) as $k => $v) {
				$this->_registry[$namespace]['data']->$k = $v;
			}
		}
	}

	/**
	 * Get a namespace in a given string format
	 *
	 * @access public
	 * @param $format 		string 	Format to return the string in
	 * @param $namespace	string	Namespace to return [optional: null returns the default namespace]
	 * @param $params		mixed	Parameters used by the formatter, see formatters for more info
	 * @return string Namespace in string format
	 * @since 1.5
	 */
	function toString($format = 'INI', $namespace = null, $params = null) {

		jimport('joomla.registry.format');
		// Return a namespace in a given format
		$handler =& JRegistryFormat::getInstance($format);

		// If namespace is not set, get the default namespace
		if ($namespace == null) {
			$namespace = $this->_defaultNameSpace;
		}

		// Get the namespace
		$ns = & $this->_registry[$namespace]['data'];

		return $handler->objectToString($ns, $params);
	}


	/**
	 * Transforms a namespace to an array
	 *
	 * @access public
	 * @param $namespace	string	Namespace to return [optional: null returns the default namespace]
	 * @return array An associative array holding the namespace data
	 */
	function toArray($namespace = null)
	{
		// If namespace is not set, get the default namespace
		if ($namespace == null) {
			$namespace = $this->_defaultNameSpace;
		}

		// Get the namespace
		$ns = & $this->_registry[$namespace]['data'];

		$array = array();
		foreach (get_object_vars( $ns ) as $k => $v) {
			$array[$k] = $v;
		}

		return $array;
	}

	/**
	 * Transforms a namespace to an object
	 *
	 * @access public
	 * @param $namespace	string	Namespace to return [optional: null returns the default namespace]
	 * @return object An an object holding the namespace data
	 */
	function toObject($namespace = null)
	{
		// If namespace is not set, get the default namespace
		if ($namespace == null) {
			$namespace = $this->_defaultNameSpace;
		}

		// Get the namespace
		$ns = & $this->_registry[$namespace]['data'];

		return $ns;

	}

	/**
	 * Return the relevant object
	 *
	 * @access private
	 * @param $format string The format to return
	 * @return object Formatting object
	 */
	function _loadFormat($format)
	{
		$lformat = strtolower($format);
		if(jimport('joomla.registry.format.'.$lformat)) {
			$return = null;
			eval('$return =& new JRegistryFormat'.$format.'();');
			return $return;
		} else {
			die('Unable to load format');
		}
	}
}
?>