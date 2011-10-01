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
	 * @var    JInputCli  The application input object.
	 * @since  11.1
	 */
	public $input;

	/**
	 * @var    JRegistry  The application configuration object.
	 * @since  11.1
	 */
	protected $config;

	/**
	 * @var    JDispatcher  The application dispatcher object.
	 * @since  11.1
	 */
	protected $dispatcher;

	/**
	 * @var    JCli  The application instance.
	 * @since  11.1
	 */
	protected static $instance;

	/**
	 * Class constructor.
	 *
	 * @param   mixed  $input       An optional argument to provide dependency injection for the application's
	 *                              input object.  If the argument is a JInputCli object that object will become
	 *                              the application's input object, otherwise a default input object is created.
	 * @param   mixed  $config      An optional argument to provide dependency injection for the application's
	 *                              config object.  If the argument is a JRegistry object that object will become
	 *                              the application's config object, otherwise a default config object is created.
	 * @param   mixed  $dispatcher  An optional argument to provide dependency injection for the application's
	 *                              event dispatcher.  If the argument is a JDispatcher object that object will become
	 *                              the application's event dispatcher, if it is null then the default event dispatcher
	 *                              will be created based on the application's loadDispatcher() method.
	 *
	 * @return  void
	 *
	 * @see     loadDispatcher()
	 * @since   11.1
	 */
	public function __construct(JInputCli $input = null, JRegistry $config = null, JDispatcher $dispatcher = null)
	{
		// Close the application if we are not executed from the command line.
		// @codeCoverageIgnoreStart
		if (!defined('STDOUT') || !defined('STDIN') || !isset($_SERVER['argv']))
		{
			$this->close();
		}
		// @codeCoverageIgnoreEnd

		// If a input object is given use it.
		if ($input instanceof JInput)
		{
			$this->input = $input;
		}
		// Create the input based on the application logic.
		else
		{
			if (class_exists('Jinput'))
			{
				$this->input = new JInputCLI;
			}
		}

		// If a config object is given use it.
		if ($config instanceof JRegistry)
		{
			$this->config = $config;
		}
		// Instantiate a new configuration object.
		else
		{
			$this->config = new JRegistry;
		}

		// If a dispatcher object is given use it.
		if ($dispatcher instanceof JDispatcher)
		{
			$this->dispatcher = $dispatcher;
		}
		// Create the dispatcher based on the application logic.
		else
		{
			$this->loadDispatcher();
		}

		// Load the configuration object.
		$this->loadConfiguration($this->fetchConfigurationData());

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());

		// Set the current directory.
		$this->set('cwd', getcwd());
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $key      The name of the property.
	 * @param   mixed   $default  The default value (optional) if none is set.
	 *
	 * @return  mixed   The value of the configuration.
	 *
	 * @since   11.3
	 */
	public function get($key, $default = null)
	{
		return $this->config->get($key, $default);
	}

	/**
	 * Returns a reference to the global JCli object, only creating it if it doesn't already exist.
	 *
	 * This method must be invoked as: $cli = JCli::getInstance();
	 *
	 * @param   string  $name  The name (optional) of the JCli class to instantiate.
	 *
	 * @return  JCli
	 *
	 * @since   11.1
	 */
	public static function getInstance($name = null)
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
		// Trigger the onBeforeExecute event.
		$this->triggerEvent('onBeforeExecute');

		// Perform application routines.
		$this->doExecute();

		// Trigger the onAfterExecute event.
		$this->triggerEvent('onAfterExecute');
	}

	/**
	 * Method to run the application routines.  Most likely you will want to instantiate a controller
	 * and execute it, or perform some sort of task directly.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 * @since   11.3
	 */
	protected function doExecute()
	{
		// Your application routines go here.
	}

	/**
	 * Exit the application.
	 *
	 * @param   integer  $code  The exit code (optional; default is 0).
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
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
	 * @return  JCli  Instance of $this to allow chaining.
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

		return $this;
	}

	/**
	 * Write a string to standard output.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  JCli  Instance of $this to allow chaining.
	 *
	 * @codeCoverageIgnore
	 * @since   11.1
	 */
	public function out($text = '', $nl = true)
	{
		fwrite(STDOUT, $text . ($nl ? "\n" : null));

		return $this;
	}

	/**
	 * Get a value from standard input.
	 *
	 * @return  string  The input string from standard input.
	 *
	 * @codeCoverageIgnore
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
	 * @return  JCli  Instance of $this to allow chaining.
	 *
	 * @since   11.1
	 */
	public function registerEvent($event, $handler)
	{
		if ($this->dispatcher instanceof JDispatcher)
		{
			$this->dispatcher->register($event, $handler);
		}

		return $this;
	}

	/**
	 * Calls all handlers associated with an event group.
	 *
	 * @param   string  $event  The event name.
	 * @param   array   $args   An array of arguments (optional).
	 *
	 * @return  array   An array of results from each function call, or null if no dispatcher is defined.
	 *
	 * @since   11.1
	 */
	public function triggerEvent($event, $args = null)
	{
		if ($this->dispatcher instanceof JDispatcher)
		{
			return $this->dispatcher->trigger($event, $args);
		}

		return null;
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $key    The name of the property.
	 * @param   mixed   $value  The value of the property to set (optional).
	 *
	 * @return  mixed   Previous value of the property
	 *
	 * @since   11.3
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
	 * @param   string  $fileName  The name of the configuration file (default is 'configuration').
	 *                             Note that .php is appended to this name
	 *
	 * @return  mixed  Either an array or object to be loaded into the configuration object.
	 *
	 * @since   11.1
	 */
	protected function fetchConfigurationData($fileName = 'configuration')
	{
		// Instantiate variables.
		$config = array();

		if (empty($fileName))
		{
			$fileName = 'configuration';
		}

		// Handle the convention-based default case for configuration file.
		if (defined('JPATH_BASE'))
		{
			// Set the configuration file name and check to see if it exists.
			$file = JPATH_BASE . '/' . preg_replace('#[^A-Z0-9-_.]#i', '', $fileName) . '.php';
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

	/**
	 * Method to create an event dispatcher for the application.  The logic and options for creating
	 * this object are adequately generic for default cases but for many applications it will make sense
	 * to override this method and create event dispatchers based on more specific needs.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function loadDispatcher()
	{
		$this->dispatcher = JDispatcher::getInstance();
	}
}
