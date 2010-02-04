<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Registry
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

// Register the session storage class with the loader.
JLoader::register('JRegistryFormat', dirname(__FILE__).DS.'format.php');

/**
 * JRegistry class
 *
 * @package		Joomla.Framework
 * @subpackage	Registry
 * @since		1.5
 */
class JRegistry extends JObject
{
	/**
	 * Default NameSpace
	 *
	 * @var string
	 */
	protected $_defaultNameSpace = null;

	/**
	 * Registry Object
	 *  - actually an array of namespace objects
	 *
	 * @var array
	 */
	protected $_registry = array();

	/**
	 * Constructor
	 *
	 * @param	string	Default registry namespace
	 * @return	void
	 */
	public function __construct($namespace = '_default')
	{
		$this->_defaultNameSpace = $namespace;
		$this->makeNameSpace($namespace);
	}

	/**
	 * Returns a reference to a global JRegistry object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 *		<pre>$registry = JRegistry::getInstance($id);</pre>
	 *
	 * @param	string	An ID for the registry instance
	 * @param	string	The default namespace for the registry object [optional]
	 * @return	object	The JRegistry object.
	 */
	public static function getInstance($id, $namespace = '_default')
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$id])) {
			$instances[$id] = new JRegistry($namespace);
		}

		return $instances[$id];
	}

	/**
	 * Load a associative array of values into the default namespace
	 *
	 * @param	array	Associative array of value to load
	 * @param	string	The name of the namespace
	 * @return	boolean	True on success
	 */
	public function loadArray($array, $namespace = null)
	{
		// If namespace is not set, get the default namespace
		if ($namespace == null) {
			$namespace = $this->_defaultNameSpace;
		}

		if (!isset($this->_registry[$namespace])) {
			// If namespace does not exist, make it and load the data
			$this->makeNameSpace($namespace);
		}

		// Load the variables into the registry's default namespace.
		foreach ($array as $k => $v) {
			$this->_registry[$namespace]['data']->$k = $v;
		}

		return true;
	}

	/**
	 * Load the public variables of the object into the default namespace.
	 *
	 * @param	object	The object holding the public vars to load
	 * @param	string	Namespace to load the INI string into [optional]
	 * @return	boolean	True on success
	 */
	public function loadObject(&$object, $namespace = null)
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
		if (is_object($object)) {
			foreach (get_object_vars($object) as $k => $v) {
				if (substr($k, 0,1) != '_' || $k == '_name') {
					$this->_registry[$namespace]['data']->$k = $v;
				}
			}
		}

		return true;
	}

	/**
	 * Load the contents of a file into the registry
	 *
	 * @param	string	Path to file to load
	 * @param	string	Format of the file [optional: defaults to JSON]
	 * @param	string	Namespace to load the JSON string into [optional]
	 * @return	boolean	True on success
	 */
	public function loadFile($file, $format = 'JSON', $namespace = null)
	{
		// Load a file into the given namespace [or default namespace if not given]
		$handler = &JRegistryFormat::getInstance($format);

		// If namespace is not set, get the default namespace
		if ($namespace == null) {
			$namespace = $this->_defaultNameSpace;
		}

		// Get the contents of the file
		jimport('joomla.filesystem.file');
		$data = JFile::read($file);

		if (!isset($this->_registry[$namespace])) {
			// If namespace does not exist, make it and load the data
			$this->makeNameSpace($namespace);
			$this->_registry[$namespace]['data'] = $handler->stringToObject($data);
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

		return true;
	}

	/**
	 * Load an XML string into the registry into the given namespace [or default if a namespace is not given]
	 *
	 * @param	string	XML formatted string to load into the registry
	 * @param	string	Namespace to load the XML string into [optional]
	 * @return	boolean	True on success
	 */
	public function loadXML($data, $namespace = null)
	{
		// Load a string into the given namespace [or default namespace if not given]
		$handler = &JRegistryFormat::getInstance('XML');

		// If namespace is not set, get the default namespace
		if ($namespace == null) {
			$namespace = $this->_defaultNameSpace;
		}

		if (!isset($this->_registry[$namespace])) {
			// If namespace does not exist, make it and load the data
			$this->makeNameSpace($namespace);
			$this->_registry[$namespace]['data'] = &$handler->stringToObject($data);
		} else {
			// Get the data in object format
			$ns = &$handler->stringToObject($data);

			/*
			 * We want to leave groups that are already in the namespace and add the
			 * groups loaded into the namespace.  This overwrites any existing group
			 * with the same name
			 */
			foreach (get_object_vars($ns) as $k => $v) {
				$this->_registry[$namespace]['data']->$k = $v;
			}
		}

		return true;
	}

	/**
	 * Load an INI string into the registry into the given namespace [or default if a namespace is not given]
	 *
	 * @param	string	INI formatted string to load into the registry
	 * @param	string	Namespace to load the INI string into [optional]
	 * @return	boolean True on success
	 */
	public function loadINI($data, $namespace = null)
	{
		// Load a string into the given namespace [or default namespace if not given]
		$handler = &JRegistryFormat::getInstance('INI');

		// If namespace is not set, get the default namespace
		if ($namespace == null) {
			$namespace = $this->_defaultNameSpace;
		}

		if (!isset($this->_registry[$namespace])) {
			// If namespace does not exist, make it and load the data
			$this->makeNameSpace($namespace);
			$this->_registry[$namespace]['data'] = &$handler->stringToObject($data);
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

		return true;
	}

	/**
	 * Load an JSON string into the registry into the given namespace [or default if a namespace is not given]
	 *
	 * @param	string	JSON formatted string to load into the registry
	 * @param	string	Namespace to load the INI string into [optional]
	 * @return	boolean True on success
	 */
	public function loadJSON($data, $namespace = null)
	{
		// Load a string into the given namespace [or default namespace if not given]
		$handler = &JRegistryFormat::getInstance('JSON');

		// If namespace is not set, get the default namespace
		if ($namespace == null) {
			$namespace = $this->_defaultNameSpace;
		}

		if (!isset($this->_registry[$namespace])) {
			// If namespace does not exist, make it and load the data
			$this->makeNameSpace($namespace);
			$this->_registry[$namespace]['data'] = &$handler->stringToObject($data);
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

		return true;
	}

	/**
	 * Merge a JRegistry object into this one
	 *
	 * @param	object	Source JRegistry object ot merge
	 * @return	boolean	True on success
	 */
	public function merge(&$source)
	{
		if ($source instanceof JRegistry) {
			$sns = $source->getNameSpaces();
			foreach ($sns as $ns) {
				if (!isset($this->_registry[$ns])) {
					// If namespace does not exist, make it and load the data
					$this->makeNameSpace($ns);
				}

				// Load the variables into the registry's default namespace.
				foreach ($source->toArray($ns) as $k => $v) {
					if ($v != null) {
						$this->_registry[$ns]['data']->$k = $v;
					}
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Get a namespace in a given string format
	 *
	 * @param	string	Format to return the string in
	 * @param	string	Namespace to return [optional: null returns the default namespace]
	 * @param	mixed	Parameters used by the formatter, see formatters for more info
	 * @return	string	Namespace in string format
	 */
	public function toString($format = 'JSON', $namespace = null, $params = null)
	{
		// Return a namespace in a given format
		$handler = JRegistryFormat::getInstance($format);

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
	 * @param	string	Namespace to return [optional: null returns the default namespace]
	 * @return	array	An associative array holding the namespace data
	 */
	public function toArray($namespace = null)
	{
		// If namespace is not set, get the default namespace
		if ($namespace == null) {
			$namespace = $this->_defaultNameSpace;
		}

		// Get the namespace
		$ns = & $this->_registry[$namespace]['data'];

		$array = array();
		foreach (get_object_vars($ns) as $k => $v) {
			$array[$k] = $v;
		}

		return $array;
	}

	/**
	 * Transforms a namespace to an object
	 *
	 * @param	string	Namespace to return [optional: null returns the default namespace]
	 * @return	object	An an object holding the namespace data
	 */
	public function toObject($namespace = null)
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
	 * Magic function to render this object as a string using default args of toString method.
	 */
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * Magic function to clone the registry object.
	 */
	public function __clone()
	{
		$this->_registry = unserialize(serialize($this->_registry));
	}

	//
	// Following methods are deprecated
	//

	/**
	 * Create a namespace
	 *
	 * @param	string	Name of the namespace to create
	 * @return	boolean	True on success
	 * @since	1.5
	 * @deprecated 1.6 - Jan 19, 2010
	 */
	public function makeNameSpace($namespace)
	{
		$this->_registry[$namespace] = array('data' => new stdClass());
		return true;
	}

	/**
	 * Get the list of namespaces
	 *
	 * @return	array	List of namespaces
	 * @deprecated 1.6 - Jan 19, 2010
	 */
	public function getNameSpaces()
	{
		return array_keys($this->_registry);
	}

	/**
	 * Get a registry value
	 *
	 * @param	string	Registry path (e.g. joomla.content.showauthor)
	 * @param	mixed	Optional default value
	 * @return	mixed	Value of entry or null
	 * @deprecated 1.6 - Jan 19, 2010
	 */
	public function getValue($regpath, $default=null)
	{
		$result = $default;

		// Explode the registry path into an array
		if ($nodes = explode('.', $regpath)) {
			// Get the namespace
			//$namespace = array_shift($nodes);
			$count = count($nodes);
			if ($count < 2) {
				$namespace	= $this->_defaultNameSpace;
				$nodes[1]	= $nodes[0];
			} else {
				$namespace = $nodes[0];
			}

			if (isset($this->_registry[$namespace])) {
				$ns = & $this->_registry[$namespace]['data'];
				$pathNodes = $count - 1;

				//for ($i = 0; $i < $pathNodes; $i ++) {
				for ($i = 1; $i < $pathNodes; $i ++) {
					if ((isset($ns->$nodes[$i]))) $ns = &$ns->$nodes[$i];
				}

				if (isset($ns->$nodes[$i])) {
					$result = $ns->$nodes[$i];
				}
			}
		}
		return $result;
	}

	/**
	 * Set a registry value
	 *
	 * @param	string	Registry Path (e.g. joomla.content.showauthor)
	 * @param	mixed	Value of entry
	 * @return	mixed	The old value prior to setting, or null if no previous value is available.
	 * @deprecated 1.6 - Jan 19, 2010
	 */
	public function setValue($regpath, $value)
	{
		// Explode the registry path into an array
		$nodes = explode('.', $regpath);

		// Get the namespace
		$count = count($nodes);

		if ($count < 2) {
			$namespace = $this->_defaultNameSpace;
		} else {
			$namespace = array_shift($nodes);
			$count--;
		}

		if (!isset($this->_registry[$namespace])) {
			$this->makeNameSpace($namespace);
		}

		$ns = & $this->_registry[$namespace]['data'];

		$pathNodes = $count - 1;

		if ($pathNodes < 0) {
			$pathNodes = 0;
		}

		for ($i = 0; $i < $pathNodes; $i ++) {
			// If any node along the registry path does not exist, create it
			if (!isset($ns->$nodes[$i])) {
				$ns->$nodes[$i] = new stdClass();
			}
			$ns = &$ns->$nodes[$i];
		}

		// Get the old value if exists so we can return it.
		$oldValue = isset($ns->$nodes[$i]) ? $ns->$nodes[$i] : null;

		// Set the new value.
		$ns->$nodes[$i] =& $value;

		return $oldValue;
	}
}