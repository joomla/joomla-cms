<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Log
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.log.logentry');
jimport('joomla.log.logger');

// @deprecated  11.2
jimport('joomla.filesystem.path');

/**
 * Joomla! Log Class
 *
 * This class hooks into the global log configuration settings to allow for user configured
 * logging events to be sent to where the user wishes it to be sent. On high load sites
 * SysLog is probably the best (pure PHP function), then the text file based loggers (CSV, W3C
 * or plain FormattedText) and finally MySQL offers the most features (e.g. rapid searching)
 * but will incur a performance hit due to INSERT being issued.
 *
 * @package     Joomla.Platform
 * @subpackage  Log
 * @since       11.1
 */
class JLog
{
	const ALL = 1;
	const EMERGENCY = 2;
	const ALERT = 4;
	const CRITICAL = 8;
	const ERROR = 16;
	const WARNING = 32;
	const NOTICE = 64;
	const INFO = 128;
	const DEBUG = 256;

	/**
	 * The global JLog instance.
	 *
	 * @var    JLog
	 * @since  11.1
	 */
	protected static $instance;

	/**
	 * True if the default logger classes have been registered.
	 *
	 * @var    bool
	 * @since  11.1
	 */
	protected static $registered = false;

	/**
	 * Container for JLogger configurations.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $configurations = array();

	/**
	 * Container for JLogger objects.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $loggers = array();

	/**
	 * Lookup array for loggers.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $lookup = array();

	/**
	 * Constructor.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function __construct()
	{
		// If the logger classes haven't been registered let's get that done.
		if (!self::$registered) {
			$this->registerLoggers();
			self::$registered = true;
		}
	}

	/**
	 * Method to add an entry to the log.
	 *
	 * @param   mixed    $entry     The JLogEntry object to add to the log or the message for a new JLogEntry object.
	 * @param   integer  $priority  Message priority.
	 * @param   string   $category  Type of entry
	 * @param   string   $date      Date of entry (defaults to now if not specified or blank)
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public static function add($entry, $priority = JLog::INFO, $category = '', $date = null)
	{
		// Automatically instantiate the singleton object if not already done.
		if (empty(self::$instance)) {
			self::setInstance(new JLog());
		}

		// If the entry object isn't a JLogEntry object let's make one.
		if (!($entry instanceof JLogEntry)) {
			$entry = new JLogEntry((string) $entry, $priority, $category, $date);
		}

		return self::$instance->addLogEntry($entry);
	}

	/**
	 * Method to set the way the JError will handle different error levels. Use this if you want to override the default settings.
	 *
	 * @param   array    $options     The object configuration array.
	 * @param   integer  $priorities  ...
	 * @param   array    $categories  ...

	 * @return  void
	 *
	 * @since   11.1
	 */
	public static function addLogger(array $options, $priorities = JLog::ALL, $categories = array())
	{
		// Automatically instantiate the singleton object if not already done.
		if (empty(self::$instance)) {
			self::setInstance(new JLog());
		}

		// The default logger is the formatted text log file.
		if (empty($options['logger'])) {
			$options['logger'] = 'formattedtext';
		}
		$options['logger'] = strtolower($options['logger']);

		// Generate a unique signature for the JLog instance based on its options.
		$signature = md5(serialize($options));

		// Register the configuration if it doesn't exist.
		if (empty(self::$instance->configurations[$signature])) {
			self::$instance->configurations[$signature] = $options;
		}

		self::$instance->lookup[$signature] = (object) array('priorities' => $priorities, 'categories' => array_map('strtolower', $categories));
	}

	/**
	 * Returns a reference to the JLog object for a given log file path, only creating it if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>$log = JLog::getInstance($file, $options, $path);</pre>
	 *
	 * @param  string  $file     The filename of the log file.
	 * @param  array   $options  The object configuration array.
	 * @param  string  $path     The base path for the log file.
	 *
	 * @return	    JLog
	 *
	 * @deprecated  11.2
	 * @since	    11.1
	 */
	public static function getInstance($file = 'error.php', $options = null, $path = null)
	{
		// Deprecation warning.
		JLog::add('JLog::getInstance() is deprecated.  See JLog::addLogger().', JLog::WARNING, 'deprecated');

		// Initialize static instances array.
		static $instances;
		if (!isset($instances)) {
			$instances = array();
		}

		// Get the system configuration object.
		$config = JFactory::getConfig();

		// Set default path if not set and sanitize it.
		if (!$path) {
			$path = $config->get('log_path');
		}

		// Only create the object if not already created.
		if (empty($instances[$path])) {
			$instances[$path] = new JLog();

			// Fix up the options so that we use the w3c format.
			$options['text_entry_format'] = $options['format'];
			$options['text_file'] = $file;
			$options['text_file_path'] = $path;
			$options['logger'] = 'w3c';

			// If no options were explicitly set use the default from configuration.
			if (empty($options)) {
				$options = $config->getValue('log_options');
			}

			// Generate a unique signature for the JLog instance based on its options.
			$signature = md5(serialize($options));

			// Register the configuration.
			$instances[$path]->configurations[$signature] = $options;

			// Setup the lookup to catch all.
			$instances[$path]->lookup[$signature] = (object) array('priorities' => JLog::ALL, 'categories' => array());
		}

		return $instances[$path];
	}

	/**
	 * Returns a reference to the a JLog object, only creating it if it doesn't already exist.
	 *
	 * @param   JLog  $instance  The logging object instance to be used by the static methods.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public static function setInstance(JLog $instance)
	{
		self::$instance = & $instance;
	}

	/**
	 * Method to add an entry to the log file.
	 *
	 * @param       array    Array of values to map to the format string for the log file.
	 *
	 * @return      boolean  True on success.
	 *
	 * @deprecated  11.2
	 * @since       11.1
	 */
	public function addEntry($entry)
	{
		// Deprecation warning.
		JLog::add('JLog::addEntry() is deprecated, use JLog::add() instead.', JLog::WARNING, 'deprecated');

		// Easiest case is we already have a JLogEntry object to add.
		if ($entry instanceof JLogEntry) {
			return $this->addLogEntry($entry);
		}
		// We have either an object or array that needs to be converted to a JLogEntry.
		elseif (is_array($entry) || is_object($entry)) {
			$tmp = new JLogEntry('');
			foreach ((array) $entry as $k => $v)
			{
				switch ($k)
				{
					case 'c-ip':
						$tmp->clientIP = $v;
						break;
					case 'status':
						$tmp->category = $v;
						break;
					case 'level':
						$tmp->priority = $v;
						break;
					case 'comment':
						$tmp->message = $v;
						break;
					default:
						$tmp->$k = $v;
						break;
				}
			}
		}
		// Unrecognized type.
		else {
			return false;
		}

		return $this->addLogEntry($tmp);
	}

	/**
	 * Method to add an entry to the appropriate loggers.
	 *
	 * @param   JLogEntry  $entry  The JLogEntry object to send to the loggers.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function addLogEntry(JLogEntry $entry)
	{
		// Find all the appropriate loggers based on priority and category for the entry.
		$loggers = $this->findLoggers($entry->priority, $entry->category);

		foreach ($loggers as $signature)
		{
			// Attempt to instantiate the logger object if it doesn't already exist.
			if (empty($this->loggers[$signature])) {

				try {
					$class = 'JLogger'.ucfirst($this->configurations[$signature]['logger']);
					$this->loggers[$signature] = new $class($this->configurations[$signature]);
				}
				catch (Exception $e) {
					jexit(JText::_('Unable to create a JLogger instance: ').$e->getMessage());
				}
			}

			// Add the entry to the logger.
			$this->loggers[$signature]->addEntry($entry);
		}
	}

	/**
	 * Method to find the loggers to use based on priority and category values.
	 *
	 * @param   integer  $priority  Message priority.
	 * @param   string   $category  Type of entry
	 *
	 * @return  array  The array of loggers to use for the given priority and category values.
	 *
	 * @since   11.1
	 */
	protected function findLoggers($priority, $category)
	{
		// Initialize variables.
		$loggers = array();

		// Sanitize inputs.
		$priority = (int) $priority;
		$category = strtolower($category);

		// Let's go iterate over the loggers and get all the ones we need.
		foreach ($this->lookup as $signature => $rules)
		{
			// Check to make sure the priority matches the logger.
			if ($priority & $rules->priorities) {

				// If either there are no set categories (meaning all) or the specific category is set, add this logger.
				if (empty($category) || empty($rules->categories) || in_array($category, $rules->categories)) {
					$loggers[] = $signature;
				}

			}
		}

		return $loggers;
	}

	/**
	 * Method to register all of the logger classes with the system autoloader.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function registerLoggers()
	{
		// Define the expected folder in which to find logger classes.
		$loggersFolder = dirname(__FILE__).'/loggers';

		// Ignore the operation if the loggers folder doesn't exist.
		if (is_dir($loggersFolder)) {

			// Open the loggers folder.
			$d = dir($loggersFolder);

			// Iterate through the folder contents to search for logger classes.
			while (false !== ($entry = $d->read()))
			{
				// Only load for php files.
				if (is_file($entry) && (substr($entry, strrpos($entry, '.') + 1) == 'php')) {

					// Get the name and full path for each file.
					$name = preg_replace('#\.[^.]*$#', '', $entry);
					$path = $loggersFolder.'/'.$entry;

					// Register the class with the autoloader.
					JLoader::register('JLogger'.ucfirst($name), $path);
				}
			}

			// Close the loggers folder.
			$d->close();
		}
	}
}
