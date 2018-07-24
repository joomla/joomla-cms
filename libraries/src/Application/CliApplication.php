<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application;

defined('JPATH_PLATFORM') or die;

use Joomla\Application\Cli\CliOutput;
use Joomla\CMS\Input\Cli;
use Joomla\CMS\Input\Input;
use Joomla\Registry\Registry;

/**
 * Base class for a Joomla! command line application.
 *
 * @since  11.4
 * @note   As of 4.0 this class will be abstract
 */
class CliApplication extends BaseApplication
{
	/**
	 * @var    CliOutput  The output type.
	 * @since  3.3
	 */
	protected $output;

	/**
	 * @var    CliApplication  The application instance.
	 * @since  11.1
	 */
	protected static $instance;

	/**
	 * Class constructor.
	 *
	 * @param   Cli                $input       An optional argument to provide dependency injection for the application's
	 *                                          input object.  If the argument is a \JInputCli object that object will become
	 *                                          the application's input object, otherwise a default input object is created.
	 * @param   Registry           $config      An optional argument to provide dependency injection for the application's
	 *                                          config object.  If the argument is a Registry object that object will become
	 *                                          the application's config object, otherwise a default config object is created.
	 * @param   \JEventDispatcher  $dispatcher  An optional argument to provide dependency injection for the application's
	 *                                          event dispatcher.  If the argument is a \JEventDispatcher object that object will become
	 *                                          the application's event dispatcher, if it is null then the default event dispatcher
	 *                                          will be created based on the application's loadDispatcher() method.
	 *
	 * @see     BaseApplication::loadDispatcher()
	 * @since   11.1
	 */
	public function __construct(Cli $input = null, Registry $config = null, \JEventDispatcher $dispatcher = null)
	{
		// Close the application if we are not executed from the command line.
		if (!defined('STDOUT') || !defined('STDIN') || !isset($_SERVER['argv']))
		{
			$this->close();
		}

		// If an input object is given use it.
		if ($input instanceof Input)
		{
			$this->input = $input;
		}
		// Create the input based on the application logic.
		else
		{
			if (class_exists('\\Joomla\\CMS\\Input\\Cli'))
			{
				$this->input = new Cli;
			}
		}

		// If a config object is given use it.
		if ($config instanceof Registry)
		{
			$this->config = $config;
		}
		// Instantiate a new configuration object.
		else
		{
			$this->config = new Registry;
		}

		$this->loadDispatcher($dispatcher);

		// Load the configuration object.
		$this->loadConfiguration($this->fetchConfigurationData());

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());

		// Set the current directory.
		$this->set('cwd', getcwd());
	}

	/**
	 * Returns a reference to the global CliApplication object, only creating it if it doesn't already exist.
	 *
	 * This method must be invoked as: $cli = CliApplication::getInstance();
	 *
	 * @param   string  $name  The name (optional) of the JApplicationCli class to instantiate.
	 *
	 * @return  CliApplication
	 *
	 * @since   11.1
	 */
	public static function getInstance($name = null)
	{
		// Only create the object if it doesn't exist.
		if (empty(self::$instance))
		{
			if (class_exists($name) && (is_subclass_of($name, '\\Joomla\\CMS\\Application\\CliApplication')))
			{
				self::$instance = new $name;
			}
			else
			{
				self::$instance = new CliApplication;
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
	 * Load an object or array into the application configuration object.
	 *
	 * @param   mixed  $data  Either an array or object to be loaded into the configuration object.
	 *
	 * @return  CliApplication  Instance of $this to allow chaining.
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
	 * @return  CliApplication  Instance of $this to allow chaining.
	 *
	 * @codeCoverageIgnore
	 * @since   11.1
	 */
	public function out($text = '', $nl = true)
	{
		$output = $this->getOutput();
		$output->out($text, $nl);

		return $this;
	}

	/**
	 * Get an output object.
	 *
	 * @return  CliOutput
	 *
	 * @since   3.3
	 */
	public function getOutput()
	{
		if (!$this->output)
		{
			// In 4.0, this will convert to throwing an exception and you will expected to
			// initialize this in the constructor. Until then set a default.
			$default = new \Joomla\Application\Cli\Output\Xml;
			$this->setOutput($default);
		}

		return $this->output;
	}

	/**
	 * Set an output object.
	 *
	 * @param   CliOutput  $output  CliOutput object
	 *
	 * @return  CliApplication  Instance of $this to allow chaining.
	 *
	 * @since   3.3
	 */
	public function setOutput(CliOutput $output)
	{
		$this->output = $output;

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
	 * Method to load a PHP configuration class file based on convention and return the instantiated data object.  You
	 * will extend this method in child classes to provide configuration data from whatever data source is relevant
	 * for your specific application.
	 *
	 * @param   string  $file   The path and filename of the configuration file. If not provided, configuration.php
	 *                          in JPATH_CONFIGURATION will be used.
	 * @param   string  $class  The class name to instantiate.
	 *
	 * @return  mixed   Either an array or object to be loaded into the configuration object.
	 *
	 * @since   11.1
	 */
	protected function fetchConfigurationData($file = '', $class = '\JConfig')
	{
		// Instantiate variables.
		$config = array();

		if (empty($file))
		{
			$file = JPATH_CONFIGURATION . '/configuration.php';

			// Applications can choose not to have any configuration data by not implementing this method and not having a config file.
			if (!file_exists($file))
			{
				$file = '';
			}
		}

		if (!empty($file))
		{
			\JLoader::register($class, $file);

			if (class_exists($class))
			{
				$config = new $class;
			}
			else
			{
				throw new \RuntimeException('Configuration class does not exist.');
			}
		}

		return $config;
	}
}
