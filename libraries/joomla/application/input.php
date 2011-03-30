<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! Input Base Class
 *
 * This is an abstracted input class used to manage retrieving data from the application environment.
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       11.1
 */
class JInput
{
	/**
	 * @var    array  Options array for the JInput instance.
	 * @since  11.1
	 */
	protected $options = array();

	/**
	 * @var    JFilterInput  Filter object to use.
	 * @since  11.1
	 */
	protected $filter = null;

	/**
	 * @var    array  Input data.
	 * @since  11.1
	 */
	protected $data = array();

	/**
	 * @var    array  Input objects.
	 * @since  11.1
	 */
	protected $inputs = array();

	/**
	 * @var    bool  True if the default input classes have been registered.
	 * @since  11.1
	 */
	protected static $registered = false;

	/**
	 * Constructor.
	 *
	 * @param   array  $source   Source data (Optional, default is $_REQUEST)
	 * @param   array  $options  Array of configuration parameters (Optional)
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function __construct($source = null, $options = array ())
	{
		// If the input classes haven't been registered let's get that done.
		if (!self::$registered) {
			self::register();
			self::$registered = true;
		}

		if (isset ($options['filter'])) {
			$this->filter = $options['filter'];
		} else {
			$this->filter = JFilterInput::getInstance();
		}

		if (is_null($source)) {
			$this->data = & $_REQUEST;
		} else {
			$this->data = & $source;
		}

		// Set the options for the class.
		$this->options = $options;
	}

	/**
	 * Magic method to get an input object
	 *
	 * @param   mixed   $name  Name of the input object to retrieve.
	 *
	 * @return  JInput  The request input object
	 *
	 * @since   11.1
	 */
	public function __get($name)
	{
		// TODO Add handling for 'method'
		if (isset ($this->inputs[$name])) {
			return $this->inputs[$name];
		}

		$className = 'JInput'.$name;
		if (class_exists($className)) {
			$this->inputs[$name] = new $className (null, $this->options);
			return $this->inputs[$name];
		}

		$superGlobal = '_'.strtoupper($name);
		if (isset ($GLOBALS[$superGlobal])) {
			$this->inputs[$name] = new JInput($GLOBALS[$superGlobal], $this->options);
			return $this->inputs[$name];
		}

		// TODO throw an exception
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
		if (isset ($this->data[$name])) {
			return $this->filter->clean($this->data[$name], $filter);
		}

		foreach ((array) $this->inputs as $input)
		{
			$return = $input->get($name, $default, $filter);

			if ($return != null) {
				return $return;
			}
		}

		return $default;
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
	 * Magic method to get filtered input data.
	 *
	 * @param   mixed    $name     Name of the value to get.
	 * @param   string   $default  Default value to return if variable does not exist.
	 *
	 * @return  bool     The filtered boolean input value.
	 *
	 * @since   11.1
	 */
	public function __call($name, $arguments)
	{
		if (substr($name, 0, 3) == 'get') {

			$filter = substr($name, 3);

			$default = null;
			if (isset ($arguments[1])) {
				$default = $arguments[1];
			}

			return $this->get($arguments[0], $default, $filter);
		}
	}

	/**
	 * Method to register all of the extended classes with the system autoloader.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected static function register()
	{
		// Define the expected folder in which to find input classes.
		$folder = dirname(__FILE__).'/input';

		// Ignore the operation if the folder doesn't exist.
		if (is_dir($folder)) {

			// Open the folder.
			$d = dir($folder);

			// Iterate through the folder contents to search for input classes.
			while (false !== ($entry = $d->read()))
			{
				// Only load for php files.
				if (file_exists($folder.'/'.$entry) && (substr($entry, strrpos($entry, '.') + 1) == 'php')) {

					// Get the name and full path for each file.
					$name = preg_replace('#\.[^.]*$#', '', $entry);
					$path = $folder.'/'.$entry;

					// Register the class with the autoloader.
					JLoader::register('JInput'.ucfirst($name), $path);
				}
			}

			// Close the folder.
			$d->close();
		}
	}
}
