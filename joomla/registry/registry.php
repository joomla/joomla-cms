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

JLoader::register('JRegistryFormat', dirname(__FILE__).'/format.php');

/**
 * JRegistry class
 *
 * @package		Joomla.Framework
 * @subpackage	Registry
 * @since		1.5
 */
class JRegistry
{
	/**
	 * Registry Object
	 *
	 * @var object
	 */
	protected $data;

	/**
	 * Constructor
	 *
	 * @return	void
	 * @since	1.5
	 */
	public function __construct($data = null)
	{
		// Instantiate the internal data object.
		$this->data = new stdClass();

		// Optionally load supplied data.
		if (is_array($data)) {
			$this->loadArray($data);
		}
		elseif (is_object($data)) {
			$this->loadObject($data);
		}
		elseif (!empty($data) && is_string($data)) {
			$this->loadJSON($data);
		}
	}

	/**
	 * Magic function to clone the registry object.
	 */
	public function __clone()
	{
		$this->data = unserialize(serialize($this->data));
	}

	/**
	 * Magic function to render this object as a string using default args of toString method.
	 */
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * Sets a default value if not alreay assigned.
	 *
	 * @param	string	The name of the parameter.
	 * @param	string	An optional value for the parameter.
	 * @param	string	An optional group for the parameter.
	 * @return	string	The value set, or the default if the value was not previously set (or null).
	 * @since	1.6
	 */
	public function def($key, $default = '')
	{
		$value = $this->get($key, (string) $default);
		$this->set($key, $value);
		return $value;
	}

	/**
	 * Check if a registry path exists.
	 *
	 * @param	string	Registry path (e.g. joomla.content.showauthor)
	 * @param	mixed	Optional default value, returned if the internal value is null.
	 * @return	boolean
	 * @since	1.6
	 */
	public function exists($path, $default = null)
	{
		// Explode the registry path into an array
		if ($nodes = explode('.', $path)) {
			// Initialize the current node to be the registry root.
			$node = $this->data;

			// Traverse the registry to find the correct node for the result.
			for ($i = 0,$n = count($nodes); $i < $n; $i++) {
				if (isset($node->$nodes[$i])) {
					$node = $node->$nodes[$i];
				} else {
					break;
				}

				if ($i+1 == $n) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get a registry value.
	 *
	 * @param	string	Registry path (e.g. joomla.content.showauthor)
	 * @param	mixed	Optional default value, returned if the internal value is null.
	 * @return	mixed	Value of entry or null
	 * @since	1.6
	 */
	public function get($path, $default = null)
	{
		// Initialise variables.
		$result = $default;

		// Explode the registry path into an array
		if ($nodes = explode('.', $path)) {
			// Initialize the current node to be the registry root.
			$node = $this->data;

			// Traverse the registry to find the correct node for the result.
			for ($i = 0,$n = count($nodes); $i < $n; $i++) {
				if (isset($node->$nodes[$i])) {
					$node = $node->$nodes[$i];
				} else {
					break;
				}

				if ($i+1 == $n) {
					if ($node !== null && $node !== '') {
						$result = $node;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Returns a reference to a global JRegistry object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 *		<pre>$registry = JRegistry::getInstance($id);</pre>
	 *
	 * @param	string	An ID for the registry instance
	 * @return	object	The JRegistry object.
	 * @since	1.5
	 */
	public static function getInstance($id)
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$id])) {
			$instances[$id] = new JRegistry();
		}

		return $instances[$id];
	}

	/**
	 * Load a associative array of values into the default namespace
	 *
	 * @param	array	Associative array of value to load
	 * @param	string	The name of the namespace
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function loadArray($array)
	{
		// Load the variables into the registry's data object.
		foreach ($array as $k => $v) {
			if ((is_array($v) && JArrayHelper::isAssociative($v)) || is_object($v)) {
				$this->data->$k = new stdClass();
				$this->bindData($this->data->$k, $v);
			}
			else {
				$this->data->$k = $v;
			}
		}

		return true;
	}

	/**
	 * Load the public variables of the object into the default namespace.
	 *
	 * @param	object	The object holding the public vars to load
	 * @param	string	Namespace to load the INI string into [optional]
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function loadObject($object)
	{
		if (is_object($object)) {
			foreach (get_object_vars($object) as $k => $v) {
				if (substr($k, 0,1) != '_' || $k == '_name') {
					if ((is_array($v) && JArrayHelper::isAssociative($v)) || is_object($v)) {
						$this->data->$k = new stdClass();
						$this->bindData($this->data->$k, $v);
					}
					else {
						$this->data->$k = $v;
					}
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
	 * @since	1.5
	 */
	public function loadFile($file, $format = 'JSON')
	{
		// Load a file into the given namespace [or default namespace if not given]
		$handler = JRegistryFormat::getInstance($format);

		// Get the contents of the file
		jimport('joomla.filesystem.file');
		$data = JFile::read($file);

		$obj = $handler->stringToObject($data);
		$this->loadObject($obj);

		return true;
	}

	/**
	 * Load an XML string into the registry into the given namespace [or default if a namespace is not given]
	 *
	 * @param	string	XML formatted string to load into the registry
	 * @param	string	Namespace to load the XML string into [optional]
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function loadXML($data, $namespace = null)
	{
		// Load a string into the given namespace [or default namespace if not given]
		$handler = JRegistryFormat::getInstance('XML');

		$obj = $handler->stringToObject($data);
		$this->loadObject($obj);

		return true;
	}

	/**
	 * Load an INI string into the registry into the given namespace [or default if a namespace is not given]
	 *
	 * @param	string	INI formatted string to load into the registry
	 * @param	string	Namespace to load the INI string into [optional]
	 * @param	mixed	An array of options for the formatter, or boolean to process sections.
	 * @return	boolean True on success
	 * @since	1.5
	 */
	public function loadINI($data, $namespace = null, $options = array())
	{
		// Load a string into the given namespace [or default namespace if not given]
		$handler = JRegistryFormat::getInstance('INI');

		$obj = $handler->stringToObject($data, $options);
		$this->loadObject($obj);

		return true;
	}

	/**
	 * Load an JSON string into the registry into the given namespace [or default if a namespace is not given]
	 *
	 * @param	string	JSON formatted string to load into the registry
	 * @return	boolean True on success
	 * @since	1.5
	 */
	public function loadJSON($data)
	{
		// Load a string into the given namespace [or default namespace if not given]
		$handler = JRegistryFormat::getInstance('JSON');

		$obj = $handler->stringToObject($data);
		$this->loadObject($obj);

		return true;
	}

	/**
	 * Merge a JRegistry object into this one
	 *
	 * @param	object	Source JRegistry object ot merge
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function merge(&$source)
	{
		if ($source instanceof JRegistry) {
			// Load the variables into the registry's default namespace.
			foreach ($source->toArray() as $k => $v) {
				if ($v !== null) {
					$this->data->$k = $v;
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Set a registry value.
	 *
	 * @param	string	Registry Path (e.g. joomla.content.showauthor)
	 * @param 	mixed	Value of entry
	 * @return 	mixed	The value of the that has been set.
	 * @since	1.6
	 */
	public function set($path, $value)
	{
		$result = null;

		// Explode the registry path into an array
		if ($nodes = explode('.', $path)) {
			// Initialize the current node to be the registry root.
			$node = $this->data;

			// Traverse the registry to find the correct node for the result.
			for ($i = 0, $n = count($nodes) - 1; $i < $n; $i++) {
				if (!isset($node->$nodes[$i]) && ($i != $n)) {
					$node->$nodes[$i] = new stdClass();
				}
				$node = $node->$nodes[$i];
			}

			// Get the old value if exists so we can return it
			$result = $node->$nodes[$i] = $value;
		}

		return $result;
	}

	/**
	 * Transforms a namespace to an array
	 *
	 * @param	string	Namespace to return [optional: null returns the default namespace]
	 * @return	array	An associative array holding the namespace data
	 * @since	1.5
	 */
	public function toArray()
	{
		return (array) $this->asArray($this->data);
	}

	/**
	 * Transforms a namespace to an object
	 *
	 * @param	string	Namespace to return [optional: null returns the default namespace]
	 * @return	object	An an object holding the namespace data
	 * @since	1.5
	 */
	public function toObject()
	{
		return $this->data;
	}

	/**
	 * Get a namespace in a given string format
	 *
	 * @param	string	Format to return the string in
	 * @param	string	Namespace to return [optional: null returns the default namespace]
	 * @param	mixed	Parameters used by the formatter, see formatters for more info
	 * @return	string	Namespace in string format
	 * @since	1.5
	 */
	public function toString($format = 'JSON', $namespace = null, $params = null)
	{
		// Return a namespace in a given format
		$handler = JRegistryFormat::getInstance($format);

		return $handler->objectToString($this->data, $params);
	}

	/**
	 * Method to recursively bind data to a parent object.
	 *
	 * @param	object	$parent	The parent object on which to attach the data values.
	 * @param	mixed	$data	An array or object of data to bind to the parent object.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function bindData(& $parent, $data)
	{
		// Ensure the input data is an array.
		$data = (array) $data;

		foreach ($data as $k => $v) {
			if ($k[0] != '_') {
				if ((is_array($v) && JArrayHelper::isAssociative($v)) || is_object($v)) {
					$parent->$k = new stdClass();
					$this->bindData($parent->$k, $v);
				}
				else {
					$parent->$k = $v;
				}
			}
		}
	}

	/**
	 * Method to recursively convert an object of data to an array.
	 *
	 * @param	object	$data	An object of data to return as an array.
	 *
	 * @return	array	Array representation of the input object.
	 * @since	1.6
	 */
	protected function asArray($data)
	{
		$array = array();

		foreach (get_object_vars((object) $data) as $k => $v) {
			if ($k[0] != '_') {
				if (is_object($v)) {
					$array[$k] = $this->asArray($v);
				}
				else {
					$array[$k] = $v;
				}
			}
		}

		return $array;
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
		//$this->_registry[$namespace] = array('data' => new stdClass());
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
		//return array_keys($this->_registry);
		return array();
	}

	/**
	 * Get a registry value
	 *
	 * @param	string	Registry path (e.g. joomla.content.showauthor)
	 * @param	mixed	Optional default value
	 * @return	mixed	Value of entry or null
	 * @deprecated 1.6 - Jan 19, 2010
	 */
	public function getValue($path, $default=null)
	{
		$parts = explode('.', $path);
		if (count($parts) > 1) {
			unset($parts[0]);
			$path = implode('.', $parts);
		}
		return $this->get($path, $default);
	}

	/**
	 * Set a registry value
	 *
	 * @param	string	Registry Path (e.g. joomla.content.showauthor)
	 * @param	mixed	Value of entry
	 * @return	mixed	The value after setting.
	 * @deprecated 1.6 - Jan 19, 2010
	 */
	public function setValue($path, $value)
	{
		$parts = explode('.', $path);
		if (count($parts) > 1) {
			unset($parts[0]);
			$path = implode('.', $parts);
		}
		return $this->set($path, $value);
	}
}