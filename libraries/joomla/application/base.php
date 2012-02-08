<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.event.dispatcher');

/**
 * Joomla Platform Base Application Class
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       12.1
 */
abstract class JApplicationBase
{
	/**
	 * The application input object.
	 *
	 * @var    JInput
	 * @since  12.1
	 */
	public $input;

	/**
	 * The application configuration object.
	 *
	 * @var    JRegistry
	 * @since  12.1
	 */
	protected $config;

	/**
	 * The character encoding string.
	 *
	 * @var    string
	 * @since  12.1
	 */
	protected $charSet = 'utf-8';

	/**
	 * The application dispatcher object.
	 *
	 * @var    JDispatcher
	 * @since  12.1
	 */
	protected $dispatcher;

	/**
	 * The application instance.
	 *
	 * @var    JApplicationBase
	 * @since  12.1
	 */
	protected static $instance;

	/**
	 * Method to close the application.
	 *
	 * @param   integer  $code  The exit code (optional; default is 0).
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function close($code = 0)
	{
		exit($code);
	}

	/**
	 * Method to execute the application.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	abstract public function execute();

	/**
	 * Method to get a property of the application or the default value if the property is not set.
	 *
	 * @param   string  $key      The name of the property.
	 * @param   mixed   $default  The default value (optional) if none is set.
	 *
	 * @return  mixed   The value of the configuration.
	 *
	 * @since   12.1
	 */
	public function get($key, $default = null)
	{
		return $this->config->get($key, $default);
	}

	/**
	 * Method to get the application character set.
	 *
	 * @return  string  The character set.
	 *
	 * @since   12.1
	 */
	public function getCharacterSet()
	{
		return $this->charSet;
	}

	/**
	 * Method to load an object or array into the application configuration object.
	 *
	 * @param   mixed  $data  Either an array or object to be loaded into the configuration object.
	 *
	 * @return  JApplicationBase  The application to allow chaining.
	 *
	 * @since   12.1
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
	 * Registers a handler to a particular event group.
	 *
	 * @param   string    $event    The event name.
	 * @param   callback  $handler  The handler, a function or an instance of a event object.
	 *
	 * @return  JApplicationBase  The application to allow chaining.
	 *
	 * @since   12.1
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
	 * Method to set a property of the application, creating it if it does not already exist.
	 *
	 * @param   string  $key    The name of the property.
	 * @param   mixed   $value  The value of the property to set (optional).
	 *
	 * @return  mixed   Previous value of the property
	 *
	 * @since   12.1
	 */
	public function set($key, $value = null)
	{
		$previous = $this->config->get($key);
		$this->config->set($key, $value);

		return $previous;
	}

	/**
	 * Method to set the application character set.
	 *
	 * @param   string  $charset  The character set.
	 *
	 * @return  JApplicationBase  The application to allow chaining.
	 *
	 * @since   12.1
	 */
	public function setCharacterSet($charset)
	{
		$this->charSet = $charset;

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
	 * @since   12.1
	 */
	public function triggerEvent($event, array $args = null)
	{
		if ($this->dispatcher instanceof JDispatcher)
		{
			return $this->dispatcher->trigger($event, $args);
		}

		return null;
	}

	/**
	 * Method to load a PHP configuration class file based on convention and return the instantiated data object.  You
	 * will extend this method in child classes to provide configuration data from whatever data source is relevant
	 * for your specific application.
	 *
	 * @param   string  $file   The path and filename of the configuration file. If not provided, configuration.php
	 *                          in JPATH_BASE will be used.
	 * @param   string  $class  The class name to instantiate.
	 *
	 * @return  mixed   Either an array or object to be loaded into the configuration object.
	 *
	 * @since   12.1
	 */
	protected function fetchConfigurationData($file = '', $class = 'JConfig')
	{
		// Instantiate variables.
		$config = array();

		if (empty($file) && defined('JPATH_BASE'))
		{
			$file = JPATH_BASE . '/configuration.php';

			// Applications can choose not to have any configuration data
			// by not implementing this method and not having a config file.
			if (!file_exists($file))
			{
				$file = '';
			}
		}

		if (!empty($file))
		{
			JLoader::register($class, $file);

			if (class_exists($class))
			{
				$config = new $class;
			}
			else
			{
				throw new RuntimeException('Configuration class does not exist.');
			}
		}

		return $config;
	}

	/**
	 * Method to create an event dispatcher for the application. The logic and options for creating
	 * this object are adequately generic for default cases but for many applications it will make
	 * sense to override this method and create event dispatchers based on more specific needs.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function loadDispatcher()
	{
		$this->dispatcher = JDispatcher::getInstance();
	}
}
