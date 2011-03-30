<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.application.input.cli');
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
	 * @var    JInput  The application input object.
	 * @since  11.1
	 */
	public $input;

	/**
	 * @var    JRegistry  The application configuration object.
	 * @since  11.1
	 */
	protected $config;

	/**
	 * @var    string  The name of the program.
	 * @since  11.1
	 */
	protected $name;

	/**
	 * @var    array  The instantiated CLI objects by name.
	 * @since  11.1
	 */
	protected static $instances;

	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A configuration array.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function __construct($config = array())
	{
		// Close the application if we are not executed from the command line.
		if (!defined(STDOUT) || !defined(STDIN) || !isset($_SERVER['argv'])) {
			$this->close();
		}

		// Set the site object name.
		$this->name = isset($config['name']) ? $config['name'] : 'Joomla CLI';

		// Get the command line options
		$this->input = new JInputCli();

		// Create the registry with a default namespace of config
		$this->config = new JRegistry();

		// Set the configuration file name.
		$config['configFile'] = isset($config['configFile']) ? $config['configFile'] : JPATH_APPLICATION.'/configuration.php';

		// Load the configuration object.
		$this->loadConfiguration($config['configFile']);

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
	 * This method must be invoked as:
	 * 		<pre>$cli = JCli::getInstance();</pre>
	 *
	 * @param   string  $name    The site object name.
	 * @param   array   $config  An optional associative array of configuration settings.
	 *
	 * @return  JCli
	 *
	 * @since   11.1
	 */
	public static function getInstance($name = 'Joomla CLI', $config = array())
	{
		// Initialize the static array.
		static $instances;
		if (!isset($instances)) {
			$instances = array();
		}

		// Only create the object if it doesn't exist.
		if (empty($instances[$name]))
		{
			// If no name is explicitly set, use the instance name.
			$config['name'] = isset($config['name']) ? $config['name'] : $name;
			$instances[$name] = new JCli($config);
		}

		return $instances[$name];
	}

	/**
	 * Execute the application.
	 *
	 * @return  bool  True on success.
	 *
	 * @since   11.1
	 */
	public function execute()
	{
		return true;
	}

	/**
	 * Exit the application.
	 *
	 * @param   integer  $code  Exit code.
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
	 * Write a string to standard output.
	 *
	 * @param   string  $text  The text to display.
	 * @param   bool    $nl    True to append a new line at the end of the output string.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function out($text = '', $nl = true)
	{
		fwrite(STDOUT, $text.($nl ? "\n" : null));
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
	 * @param   array   $args   An array of arguments.
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
	 * @param   string  $key      The name of the property
	 * @param   mixed   $default  The default value if none is set.
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
	 * @param   string  $key    The name of the property
	 * @param   mixed   $value  The value of the property to set
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
	 * Load the configuration file into the site object.
	 *
	 * @param   string   $file  The path to the configuration file.
	 *
	 * @return  bool     True on success.
	 *
	 * @since   11.1
	 */
	protected function loadConfiguration($file)
	{
		// Import the configuration file.
		if (!is_file($file)) {
			return false;
		}
		require_once $file;

		// Instantiate the configuration object.
		if (!class_exists('JConfig')) {
			return false;
		}
		$config = new JConfig();

		// Load the configuration values into the registry
		$this->config->loadObject($config);

		return true;
	}
}
