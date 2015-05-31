<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Input
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! Input Base Class
 *
 * This is an abstracted input class used to manage retrieving data from the application environment.
 *
 * @since  11.1
 *
 * @property-read    JInput        $get
 * @property-read    JInput        $post
 * @property-read    JInput        $request
 * @property-read    JInput        $server
 * @property-read    JInputFiles   $files
 * @property-read    JInputCookie  $cookie
 *
 * @method      integer  getInt()       getInt($name, $default = null)    Get a signed integer.
 * @method      integer  getUint()      getUint($name, $default = null)   Get an unsigned integer.
 * @method      float    getFloat()     getFloat($name, $default = null)  Get a floating-point number.
 * @method      boolean  getBool()      getBool($name, $default = null)   Get a boolean.
 * @method      string   getWord()      getWord($name, $default = null)
 * @method      string   getAlnum()     getAlnum($name, $default = null)
 * @method      string   getCmd()       getCmd($name, $default = null)
 * @method      string   getBase64()    getBase64($name, $default = null)
 * @method      string   getString()    getString($name, $default = null)
 * @method      string   getHtml()      getHtml($name, $default = null)
 * @method      string   getPath()      getPath($name, $default = null)
 * @method      string   getUsername()  getUsername($name, $default = null)
 */
class JInput implements Serializable, Countable
{
	/**
	 * Options array for the JInput instance.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $options = array();

	/**
	 * Filter object to use.
	 *
	 * @var    JFilterInput
	 * @since  11.1
	 */
	protected $filter = null;

	/**
	 * Input data.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $data = array();

	/**
	 * Input objects
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $inputs = array();

	/**
	 * Constructor.
	 *
	 * @param   array  $source   Source data (Optional, default is $_REQUEST)
	 * @param   array  $options  Array of configuration parameters (Optional)
	 *
	 * @since   11.1
	 */
	public function __construct($source = null, array $options = array())
	{
		if (isset($options['filter']))
		{
			$this->filter = $options['filter'];
		}
		else
		{
			$this->filter = JFilterInput::getInstance();
		}

		if (is_null($source))
		{
			$this->data = &$_REQUEST;
		}
		else
		{
			$this->data = $source;
		}

		// Set the options for the class.
		$this->options = $options;
	}

	/**
	 * Magic method to get an input object
	 *
	 * @param   mixed  $name  Name of the input object to retrieve.
	 *
	 * @return  JInput  The request input object
	 *
	 * @since   11.1
	 */
	public function __get($name)
	{
		if (isset($this->inputs[$name]))
		{
			return $this->inputs[$name];
		}

		$className = 'JInput' . ucfirst($name);

		if (class_exists($className))
		{
			$this->inputs[$name] = new $className(null, $this->options);

			return $this->inputs[$name];
		}

		$superGlobal = '_' . strtoupper($name);

		if (isset($GLOBALS[$superGlobal]))
		{
			$this->inputs[$name] = new JInput($GLOBALS[$superGlobal], $this->options);

			return $this->inputs[$name];
		}

		// TODO throw an exception
	}

	/**
	 * Get the number of variables.
	 *
	 * @return  integer  The number of variables in the input.
	 *
	 * @since   12.2
	 * @see     Countable::count()
	 */
	public function count()
	{
		return count($this->data);
	}

	/**
	 * Gets a value from the input data.
	 *
	 * @param   string  $name     Name of the value to get.
	 * @param   mixed   $default  Default value to return if variable does not exist.
	 * @param   string  $filter   Filter to apply to the value.
	 *
	 * @return  mixed  The filtered input value.
	 *
	 * @since   11.1
	 */
	public function get($name, $default = null, $filter = 'cmd')
	{
		if (isset($this->data[$name]))
		{
			return $this->filter->clean($this->data[$name], $filter);
		}

		return $default;
	}

	/**
	 * Gets an array of values from the request.
	 *
	 * @param   array   $vars           Associative array of keys and filter types to apply.
	 *                                  If empty and datasource is null, all the input data will be returned
	 *                                  but filtered using the filter given by the parameter defaultFilter in
	 *                                  JFilterInput::clean.
	 * @param   mixed   $datasource     Array to retrieve data from, or null.
	 * @param   string  $defaultFilter  Default filter used in JFilterInput::clean if vars is empty and
	 *                                  datasource is null. If 'unknown', the default case is used in
	 *                                  JFilterInput::clean.
	 *
	 * @return  mixed  The filtered input data.
	 *
	 * @since   11.1
	 */
	public function getArray(array $vars = array(), $datasource = null, $defaultFilter = 'unknown')
	{
		return $this->getArrayRecursive($vars, $datasource, $defaultFilter, false);
	}

	/**
	 * Gets an array of values from the request.
	 *
	 * @param   array   $vars           Associative array of keys and filter types to apply.
	 *                                  If empty and datasource is null, all the input data will be returned
	 *                                  but filtered using the filter given by the parameter defaultFilter in
	 *                                  JFilterInput::clean.
	 * @param   mixed   $datasource     Array to retrieve data from, or null.
	 * @param   string  $defaultFilter  Default filter used in JFilterInput::clean if vars is empty and
	 *                                  datasource is null. If 'unknown', the default case is used in
	 *                                  JFilterInput::clean.
	 * @param   bool    $recursion      Flag to indicate a recursive function call.
	 *
	 * @return  mixed  The filtered input data.
	 *
	 * @since   3.4.2
	 */
	protected function getArrayRecursive(array $vars = array(), $datasource = null, $defaultFilter = 'unknown', $recursion = false)
	{
		if (empty($vars) && is_null($datasource))
		{
			$vars = $this->data;
		}
		else
		{
			if (!$recursion)
			{
				$defaultFilter = null;
			}
		}

		$results = array();

		foreach ($vars as $k => $v)
		{
			if (is_array($v))
			{
				if (is_null($datasource))
				{
					$results[$k] = $this->getArrayRecursive($v, $this->get($k, null, 'array'), $defaultFilter, true);
				}
				else
				{
					$results[$k] = $this->getArrayRecursive($v, $datasource[$k], $defaultFilter, true);
				}
			}
			else
			{
				$filter = isset($defaultFilter) ? $defaultFilter : $v;

				if (is_null($datasource))
				{
					$results[$k] = $this->get($k, null, $filter);
				}
				elseif (isset($datasource[$k]))
				{
					$results[$k] = $this->filter->clean($datasource[$k], $filter);
				}
				else
				{
					$results[$k] = $this->filter->clean(null, $filter);
				}
			}
		}

		return $results;
	}

	/**
	 * Sets a value
	 *
	 * @param   string  $name   Name of the value to set.
	 * @param   mixed   $value  Value to assign to the input.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function set($name, $value)
	{
		$this->data[$name] = $value;
	}

	/**
	 * Define a value. The value will only be set if there's no value for the name or if it is null.
	 *
	 * @param   string  $name   Name of the value to define.
	 * @param   mixed   $value  Value to assign to the input.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function def($name, $value)
	{
		if (isset($this->data[$name]))
		{
			return;
		}

		$this->data[$name] = $value;
	}

	/**
	 * Magic method to get filtered input data.
	 *
	 * @param   string  $name       Name of the filter type prefixed with 'get'.
	 * @param   array   $arguments  [0] The name of the variable [1] The default value.
	 *
	 * @return  mixed   The filtered input value.
	 *
	 * @since   11.1
	 */
	public function __call($name, $arguments)
	{
		if (substr($name, 0, 3) == 'get')
		{
			$filter = substr($name, 3);

			$default = null;

			if (isset($arguments[1]))
			{
				$default = $arguments[1];
			}

			return $this->get($arguments[0], $default, $filter);
		}
	}

	/**
	 * Gets the request method.
	 *
	 * @return  string   The request method.
	 *
	 * @since   11.1
	 */
	public function getMethod()
	{
		// Get method if exist
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
		$method = strtoupper($method);

		return $method;
	}

	/**
	 * Method to serialize the input.
	 *
	 * @return  string  The serialized input.
	 *
	 * @since   12.1
	 */
	public function serialize()
	{
		// Load all of the inputs.
		$this->loadAllInputs();

		// Remove $_ENV and $_SERVER from the inputs.
		$inputs = $this->inputs;
		unset($inputs['env']);
		unset($inputs['server']);

		// Serialize the options, data, and inputs.
		return serialize(array($this->options, $this->data, $inputs));
	}

	/**
	 * Method to unserialize the input.
	 *
	 * @param   string  $input  The serialized input.
	 *
	 * @return  JInput  The input object.
	 *
	 * @since   12.1
	 */
	public function unserialize($input)
	{
		// Unserialize the options, data, and inputs.
		list($this->options, $this->data, $this->inputs) = unserialize($input);

		// Load the filter.
		if (isset($this->options['filter']))
		{
			$this->filter = $this->options['filter'];
		}
		else
		{
			$this->filter = JFilterInput::getInstance();
		}
	}

	/**
	 * Method to load all of the global inputs.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function loadAllInputs()
	{
		static $loaded = false;

		if (!$loaded)
		{
			// Load up all the globals.
			foreach ($GLOBALS as $global => $data)
			{
				// Check if the global starts with an underscore.
				if (strpos($global, '_') === 0)
				{
					// Convert global name to input name.
					$global = strtolower($global);
					$global = substr($global, 1);

					// Get the input.
					$this->$global;
				}
			}

			$loaded = true;
		}
	}
}
