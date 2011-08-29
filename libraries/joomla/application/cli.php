<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

jimport('joomla.application.applicationexception');
jimport('joomla.application.input');
jimport('joomla.event.dispatcher');
jimport('joomla.log.log');
jimport('joomla.registry.registry');

/**
 * Base class for a Joomla! command line application.
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       11.1
 */
class JCli
{
	/**
	 * The application input object.
	 *
	 * @var    JInputCli
	 * @since  11.1
	 */
	public $input;

	/**
	 * The application configuration object.
	 *
	 * @var    JRegistry
	 * @since  11.1
	 */
	protected $config;

	/**
	 * The application instance.
	 *
	 * @var    JCli
	 * @since  11.1
	 */
	protected static $instance;

	/**
	 * Class constructor.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function __construct()
	{
		// Close the application if we are not executed from the command line.
		if (!defined('STDOUT') || !defined('STDIN') || !isset($_SERVER['argv']))
		{
			$this->close();
		}

		// Get the command line options
		if (class_exists('JInput'))
		{
			$this->input = new JInputCli;
		}

		// Create the registry with a default namespace of config
		$this->config = new JRegistry;

		// Load the configuration object.
		$this->loadConfiguration($this->fetchConfigurationData());

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());

		// Set the current directory.
		$this->set('cwd', getcwd());
	}

	/**
	 * Returns a reference to the global JCli object, only creating it if it
	 * doesn't already exist.
	 *
	 * This method must be invoked as: $cli = JCli::getInstance();
	 *
	 * @param   string  $name  The name (optional) of the JCli class to instantiate.
	 *
	 * @return  JCli  A JCli object
	 *
	 * @since   11.1
	 */
	public static function &getInstance($name = null)
	{
		// Only create the object if it doesn't exist.
		if (empty(self::$instance))
		{
			if (class_exists($name) && (is_subclass_of($name, 'JCli')))
			{
				self::$instance = new $name;
			}
			else
			{
				self::$instance = new JCli;
			}
		}

		return self::$instance;
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function execute()
	{
		$this->close();
	}

	/**
	 * Exit the application.
	 *
	 * @param   integer  $code  The exit code (optional; default is 0).
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function close($code = 0)
	{
		exit($code);
	}

	/**
	 * Load an object or array into the application configuration object.
	 *
	 * @param   mixed  $data  Either an array or object to be loaded into the configuration object.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function loadConfiguration($data)
	{
		// Load the data into the configuration object.
		if (is_array($data))
		{
			$this->config->loadArray($data);
		}
		elseif (is_object($data))
		{
			$this->config->loadObject($data);
		}
	}

	/**
	 * Write a string to standard output.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function out($text = '', $nl = true)
	{
		fwrite(STDOUT, $text . ($nl ? "\n" : null));
	}

	/**
	 * Get a value from standard input.
	 *
	 * @return  string  The input string from standard input.
	 *
	 * @since   11.1
	 */
	public function in()
	{
		return rtrim(fread(STDIN, 8192), "\n");
	}

	/**
	 * Registers a handler to a particular event group.
	 *
	 * @param   string    $event    The event name.
	 * @param   callback  $handler  The handler, a function or an instance of a event object.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	function registerEvent($event, $handler)
	{
		JDispatcher::getInstance()->register($event, $handler);
	}

	/**
	 * Calls all handlers associated with an event group.
	 *
	 * @param   string  $event  The event name.
	 * @param   array   $args   An array of arguments (optional).
	 *
	 * @return  array   An array of results from each function call.
	 *
	 * @since   11.1
	 */
	function triggerEvent($event, $args = null)
	{
		return JDispatcher::getInstance()->trigger($event, $args);
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $key      The name of the property.
	 * @param   mixed   $default  The default value (optional) if none is set.
	 *
	 * @return  mixed   The value of the configuration.
	 *
	 * @since   11.1
	 */
	public function get($key, $default = null)
	{
		return $this->config->get($key, $default);
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $key    The name of the property.
	 * @param   mixed   $value  The value of the property to set (optional).
	 *
	 * @return  mixed   Previous value of the property
	 *
	 * @since   11.1
	 */
	public function set($key, $value = null)
	{
		$previous = $this->config->get($key);
		$this->config->set($key, $value);
		return $previous;
	}

	/**
	 * Method to load a PHP configuration class file based on convention and return the instantiated data object.  You
	 * will extend this method in child classes to provide configuration data from whatever data source is relevant
	 * for your specific application.
	 *
	 * @return  mixed  Either an array or object to be loaded into the configuration object.
	 *
	 * @since   11.1
	 */
	protected function fetchConfigurationData()
	{
		// Instantiate variables.
		$config = array();

		// Handle the convention-based default case for configuration file.
		if (defined('JPATH_BASE'))
		{
			// Set the configuration file name and check to see if it exists.
			$file = JPATH_BASE . '/configuration.php';
			if (is_file($file))
			{
				// Import the configuration file.
				include_once $file;

				// Instantiate the configuration object if it exists.
				if (class_exists('JConfig'))
				{
					$config = new JConfig;
				}
			}
		}

		return $config;
	}
}