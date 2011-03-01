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
	/**
	 * The logger object for writing logs to various places.
	 *
	 * @var    JLogger
	 * @since  11.1
	 */
	protected $logger;

	/**
	 * Options array for the JLog instance.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $options = array();

	/**
	 * True if the default logger classes have been registered.
	 *
	 * @var    bool
	 * @since  11.1
	 */
	private static $registered = false;

	/**
	 * Container for JLog instances.
	 *
	 * @var    array
	 * @since  11.1
	 */
	private static $_instances = array();

	/**
	 * Constructor.
	 *
	 * @param   array  $options  Log object options.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function __construct(array $options)
	{
		// If the logger classes haven't been registered let's get that done.
		if (!self::$registered) {
			$this->_registerLoggers();
			self::$registered = true;
		}

		// The default format is the W3C logfile format.
		if (empty($options['logger'])) {
			$options['logger'] = 'formattedtext';
		}
		$options['logger'] = strtolower($options['logger']);

		// Set the options for the class.
		$this->options = array_merge($this->options, $options);

		// Attempt to instantiate the logger object.
		try {
			$class = 'JLogger'.ucfirst($options['logger']);
			$this->logger = new $class($this->options);
		}
		catch (Exception $e) {
			jexit(JText::_('Unable to create a JLog instance: ').$e->getMessage());
		}
	}

	/**
	 * Returns a reference to the a JLog object, only creating it if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>$log = JLog::getInstance($options);</pre>
	 *
	 * @param   array       $options  The object configuration array.
	 * @param   deprecated  $arg2     Formerly the object configuration array.
	 * @param   deprecated  $arg3     Formerly the base path for the log file.
	 *
	 * @return	JLog
	 *
	 * @since	11.1
	 */
	public static function getInstance($options = array(), $arg2 = null, $arg3 = null)
	{
		// Get the system configuration object.
		$config = JFactory::getConfig();

		// Determine if we are dealing with a deprecated usage of JLog::getInstance();
		if (is_string($options)) {

			// Deprecation warning.
			$deprecated = JLog::getInstance(array('text_file' => 'deprecated.php'));
			$deprecated->add('JLog::getInstance() now accepts one options array.', 'NOTICE');

			// Fix up arguments.
			$file		= $options;
			$options	= $arg2;
			$path		= $arg3;

			// Set default path if not set and sanitize it.
			if (!$path) {
				$path = $config->get('log_path');
			}

			// Fix up the options so that we use the w3c format.
			$options['text_entry_format'] = $options['format'];
			$options['text_file'] = $file;
			$options['text_file_path'] = $path;
			$options['logger'] = 'w3c';
		}

		// If no options were explicitly set use the default from configuration.
		if (empty ($options)) {
			$options = $config->getValue('log_options');
		}

		// Generate a unique signature for the JLog instance based on its options.
		$signature = md5(serialize($options));

		if (empty (self::$instances[$signature])) {
			// Attempt to instantiate the object.
			try {
				self::$instances[$signature] = new JLog($options);
			}
			catch (Exception $e) {
				jexit(JText::_('Unable to create a JLog instance: ').$e->getMessage());
			}
		}

		return self::$instances[$signature];
	}

	/**
	 * Method to add an entry to the log.
	 *
	 * @param   mixed   $entry     The JLogEntry object to add to the log or the message for a new JLogEntry object.
	 * @param   string  $priority  Message priority based on {$this->_priorities}.
	 * @param   string  $category  Type of entry
	 * @param   string  $date      Date of entry (defaults to now if not specified or blank)
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function add($entry, $priority = 'INFO', $category = '', $date = null)
	{
		// If the entry object isn't a JLogEntry object let's make one.
		if (!($entry instanceof JLogEntry)) {
			$entry = new JLogEntry((string) $entry, $priority, $category, $date);
		}

		return $this->logger->addEntry($entry);
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
		$deprecated = JLog::getInstance(array('text_file' => 'deprecated.php'));
		$deprecated->add('JLog::addEntry() is deprecated, use JLog::add() instead.', 'NOTICE');

		// Easiest case is we already have a JLogEntry object to add.
		if ($entry instanceof JLogEntry) {
			return $this->add($entry);
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

		return $this->add($tmp);
	}

	/**
	 * Method to register all of the logger classes with the system autoloader.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	private function _registerLoggers()
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
