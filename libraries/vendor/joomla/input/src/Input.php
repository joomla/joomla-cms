<?php
/**
 * Part of the Joomla Framework Input Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input;

use Joomla\Filter;

/**
 * Joomla! Input Base Class
 *
 * This is an abstracted input class used to manage retrieving data from the application environment.
 *
 * @since  1.0
 *
 * @property-read    Input   $get
 * @property-read    Input   $post
 * @property-read    Input   $request
 * @property-read    Input   $server
 * @property-read    Input   $env
 * @property-read    Files   $files
 * @property-read    Cookie  $cookie
 *
 * @method      integer  getInt($name, $default = null)       Get a signed integer.
 * @method      integer  getUint($name, $default = null)      Get an unsigned integer.
 * @method      float    getFloat($name, $default = null)     Get a floating-point number.
 * @method      boolean  getBool($name, $default = null)      Get a boolean value.
 * @method      string   getWord($name, $default = null)      Get a word.
 * @method      string   getAlnum($name, $default = null)     Get an alphanumeric string.
 * @method      string   getCmd($name, $default = null)       Get a CMD filtered string.
 * @method      string   getBase64($name, $default = null)    Get a base64 encoded string.
 * @method      string   getString($name, $default = null)    Get a string.
 * @method      string   getHtml($name, $default = null)      Get a HTML string.
 * @method      string   getPath($name, $default = null)      Get a file path.
 * @method      string   getUsername($name, $default = null)  Get a username.
 * @method      mixed    getRaw($name, $default = null)       Get an unfiltered value.
 */
class Input implements \Countable
{
	/**
	 * Container with allowed superglobals
	 *
	 * @var    array
	 * @since  1.3.0
	 */
	private const ALLOWED_GLOBALS = ['REQUEST', 'GET', 'POST', 'FILES', 'SERVER', 'ENV'];

	/**
	 * Options array for the Input instance.
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $options = [];

	/**
	 * Filter object to use.
	 *
	 * @var    Filter\InputFilter
	 * @since  1.0
	 */
	protected $filter;

	/**
	 * Input data.
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $data = [];

	/**
	 * Input objects
	 *
	 * @var    Input[]
	 * @since  1.0
	 */
	protected $inputs = [];

	/**
	 * Constructor.
	 *
	 * @param   array  $source   Optional source data. If omitted, a copy of the server variable '_REQUEST' is used.
	 * @param   array  $options  An optional associative array of configuration parameters:
	 *                           filter: An instance of Filter\Input. If omitted, a default filter is initialised.
	 *
	 * @since   1.0
	 */
	public function __construct($source = null, array $options = [])
	{
		$this->data    = empty($source) ? $_REQUEST : $source;
		$this->filter  = $options['filter'] ?? new Filter\InputFilter;
		$this->options = $options;
	}

	/**
	 * Magic method to get an input object
	 *
	 * @param   mixed  $name  Name of the input object to retrieve.
	 *
	 * @return  Input  The request input object
	 *
	 * @since   1.0
	 */
	public function __get($name)
	{
		if (isset($this->inputs[$name]))
		{
			return $this->inputs[$name];
		}

		$className = __NAMESPACE__ . '\\' . ucfirst($name);

		if (class_exists($className))
		{
			$this->inputs[$name] = new $className(null, $this->options);

			return $this->inputs[$name];
		}

		$superGlobal = '_' . strtoupper($name);

		if (\in_array(strtoupper($name), self::ALLOWED_GLOBALS, true) && isset($GLOBALS[$superGlobal]))
		{
			$this->inputs[$name] = new self($GLOBALS[$superGlobal], $this->options);

			return $this->inputs[$name];
		}

		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'],
			E_USER_NOTICE
		);
	}

	/**
	 * Get the number of variables.
	 *
	 * @return  integer  The number of variables in the input.
	 *
	 * @since   1.0
	 * @see     Countable::count()
	 */
	public function count()
	{
		return \count($this->data);
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
	 * @see     \Joomla\Filter\InputFilter::clean()
	 * @since   1.0
	 */
	public function get($name, $default = null, $filter = 'cmd')
	{
		if ($this->exists($name))
		{
			return $this->filter->clean($this->data[$name], $filter);
		}

		return $default;
	}

	/**
	 * Gets an array of values from the request.
	 *
	 * @param   array  $vars        Associative array of keys and filter types to apply.
	 *                              If empty and datasource is null, all the input data will be returned
	 *                              but filtered using the default case in JFilterInput::clean.
	 * @param   mixed  $datasource  Array to retrieve data from, or null
	 *
	 * @return  mixed  The filtered input data.
	 *
	 * @since   1.0
	 */
	public function getArray(array $vars = [], $datasource = null)
	{
		if (empty($vars) && $datasource === null)
		{
			$vars = $this->data;
		}

		$results = [];

		foreach ($vars as $k => $v)
		{
			if (\is_array($v))
			{
				if ($datasource === null)
				{
					$results[$k] = $this->getArray($v, $this->get($k, null, 'array'));
				}
				else
				{
					$results[$k] = $this->getArray($v, $datasource[$k]);
				}
			}
			else
			{
				if ($datasource === null)
				{
					$results[$k] = $this->get($k, null, $v);
				}
				elseif (isset($datasource[$k]))
				{
					$results[$k] = $this->filter->clean($datasource[$k], $v);
				}
				else
				{
					$results[$k] = $this->filter->clean(null, $v);
				}
			}
		}

		return $results;
	}

	/**
	 * Get the Input instance holding the data for the current request method
	 *
	 * @return  Input
	 *
	 * @since   1.3.0
	 */
	public function getInputForRequestMethod()
	{
		switch (strtoupper($this->getMethod()))
		{
			case 'GET':
				return $this->get;

			case 'POST':
				return $this->post;

			default:
				// PUT, PATCH, etc. don't have superglobals
				return $this;
		}
	}

	/**
	 * Sets a value
	 *
	 * @param   string  $name   Name of the value to set.
	 * @param   mixed   $value  Value to assign to the input.
	 *
	 * @return  void
	 *
	 * @since   1.0
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
	 * @since   1.0
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
	 * Check if a value name exists.
	 *
	 * @param   string  $name  Value name
	 *
	 * @return  boolean
	 *
	 * @since   1.2.0
	 */
	public function exists($name)
	{
		return isset($this->data[$name]);
	}

	/**
	 * Magic method to get filtered input data.
	 *
	 * @param   string  $name       Name of the filter type prefixed with 'get'.
	 * @param   array   $arguments  [0] The name of the variable [1] The default value.
	 *
	 * @return  mixed   The filtered input value.
	 *
	 * @since   1.0
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

		$trace = debug_backtrace();
		trigger_error(
			'Call to undefined method via call(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'],
			E_USER_ERROR
		);
	}

	/**
	 * Gets the request method.
	 *
	 * @return  string   The request method.
	 *
	 * @since   1.0
	 */
	public function getMethod()
	{
		return strtoupper($this->server->getCmd('REQUEST_METHOD'));
	}
}
