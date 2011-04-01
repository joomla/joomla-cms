<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.application.cli');
jimport('joomla.application.exception');

/**
 * Class to turn JCli applications into daemons.  It requires CLI and PCNTL support built into PHP.
 *
 * @package		Joomla.Platform
 * @subpackage	Application
 * @since		11.1
 */
class JDaemon extends JCli
{
	/**
	 * @var    bool  True if the daemon is in the process of exiting.
	 * @since  11.1
	 */
	protected $exiting = false;

	/**
	 * @var    bool  True if the daemon is currently running.
	 * @since  11.1
	 */
	protected $running = false;

	/**
	 * @var    integer  The process id of the daemon.
	 * @since  11.1
	 */
	protected $processId = 0;

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
		// Verify that the process control extension for PHP is available.
		if (!defined('SIGHUP')) {
			JLog::add('The PCNTL extension for PHP is not available.', JLog::ERROR);
			throw new ApplicationException();
		}

		// Verify that POSIX support for PHP is available.
		if (!function_exists('posix_getpid')) {
			JLog::add('The POSIX extension for PHP is not available.', JLog::ERROR);
			throw new ApplicationException();
		}

		// Call the parent constructor.
		parent::__construct($config);

		// Make sure that the application name is UNIX compliant.
		$this->name = (string) preg_replace('/[^A-Z0-9_-]/i', '', $this->name);
	}

	/**
	 * Spawn daemon process.
	 *
	 * @return  bool
	 *
	 * @since   11.1
	 */
	public function start()
	{
		// Enable basic garbage collection.  Only available in PHP 5.3+
		if (function_exists('gc_enable')) {
			gc_enable();
		}

		// Set off the process for becoming a daemon.
		$this->daemonize();
	}

	/**
	 * Restart daemon process.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function restart()
	{

	}

	/**
	 * Stop daemon process.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function stop()
	{

	}

	/**
	 * Method to perform basic garbage collection and memory management in the sense of clearing the
	 * stat cache.  We will probably call this method pretty regularly in our main loop.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function gc()
	{
		// Perform generic garbage collection.  Only available in PHP 5.3+
		if (function_exists('gc_collect_cycles')) {
			gc_collect_cycles();
		}

		// Clear the stat cache so it doesn't blow up memory.
		clearstatcache();
	}

	/**
	 * Method to put the application into the background.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @throws  ApplicationException
	 */
	protected function daemonize()
	{
		JLog::add('Starting '.$this->name.' daemon.', JLog::NOTICE);

		// Is there already an active daemon running?
		if ($this->isActive()) {
			JLog::add($this->name.' daemon is still running. Exiting the application.', JLog::EMERGENCY);
			return false;
		}

		// Reset Process Information
		$this->safeMode = !!@ ini_get('safe_mode');
		$this->processId = 0;
		$this->running = false;

		// Fork process!
		if (!$this->fork()) {
			JLog::add('Unable to fork.', JLog::EMERGENCY);
			return false;
		}
	}

	/**
	 * Check to see if the daemon is active.  This does not assume that $this daemon is active, but
	 * only if an instance of the application is active as a daemon.
	 *
	 * @return  bool
	 *
	 * @since   11.1
	 */
	public function isActive()
	{
		// Get the process id file location for the application.
		$pidFile = $this->config->get('application_pid_file');

		// If the process id file doesn't exist then the daemon is obviously not running.
		if (!file_exists($pidFile)) {
			return false;
		}

		// Read the contents of the process id file as an integer.
		$fp = fopen($pidFile, "r");
		$pid = fread($fp, filesize($pidFile));
		$pid = intval($pid);
		fclose($fp);

		// Check to make sure that the process id exists as a positive integer.
		if (!$pid) {
			return false;
		}

		// Check to make sure the process is active by pinging it and ensure it responds.
		if (!posix_kill($pid, 0)) {
			// No response so remove the process id file and log the situation.
			@ unlink($pidFile);
			JLog::add('The process found based on PID file was unresponsive.', JLog::WARNING);

			return false;
		}

		return true;
	}

	/**
	 * This is truly where the magic happens.  This is where we fork the process and kill the parent
	 * process, which is essentially what turns the application into a daemon.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @throws  ApplicationException
	 */
	protected function fork()
	{
		JLog::add('Forking the '.$this->name.' daemon.', JLog::DEBUG);

		// Attempt to fork the process.
		$pid = pcntl_fork();

		// If we could not fork the process log the error and throw an exception.
		if ($pid === -1) {
			// Error
			JLog::add('Process could not be forked.', JLog::WARNING);
			throw new ApplicationException();
		}
		// If the pid is positive then we successfully forked, and can close this application.
		elseif ($pid) {

			// Add the log entry for debugging purposes and exit gracefully.
			JLog::add('Ending '.$this->name.' parent process', JLog::DEBUG);
			$this->close();
		}
		// We are in the forked child process.
		else {

			// Setup some protected values.
			$this->exiting = false;
			$this->running = true;
			$this->processId = posix_getpid();
		}
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
		// Perform the configuration file load.
		if (!parent::loadConfiguration($file)) {
			return false;
		}

		/*
		 * Make sure some necessary values are loaded into the configuration object.
		 */

		// The application author name.  This string is used in generating startup scripts and has
		// a maximum of 50 characters.
		$tmp = (string) $this->config->get('author_name', 'Joomla Platform');
		$this->config->set('author_name', (strlen($tmp) > 50) ? substr($tmp, 0, 50) : $tmp);

		// The application author email.  This string is used in generating startup scripts.
		$tmp = (string) $this->config->get('author_email', 'admin@joomla.org');
		$this->config->set('author_email', filter_var($tmp, FILTER_VALIDATE_EMAIL));

		// The application description.  This string is used in generating startup scripts.
		$tmp = (string) $this->config->get('application_description', 'A generic Joomla Platform application.');
		$this->config->set('application_description', filter_var($tmp, FILTER_SANITIZE_STRING));

		// The application executable daemon.  This string is used in generating startup scripts.
		$tmp = (string) $this->config->get('application_executable', $this->input->executable);
		preg_match('/^[A-Za-z0-9_-]+[A-Za-z0-9_\.-]*([\\\\\/][A-Za-z0-9_-]+[A-Za-z0-9_\.-]*)*$/', $tmp, $matches);
		$this->config->set('application_executable', @ basename((string) $matches[0]));

		// The user id under which to run the daemon.
		$tmp = (int) $this->config->get('application_uid', 0);
		$options = array('options' => array('min_range' => 0, 'max_range' => 65000));
		$this->config->set('application_uid', filter_var($tmp, FILTER_VALIDATE_INT, $options));

		// The group id under which to run the daemon.
		$tmp = (int) $this->config->get('application_gid', 0);
		$options = array('options' => array('min_range' => 0, 'max_range' => 65000));
		$this->config->set('application_gid', filter_var($tmp, FILTER_VALIDATE_INT, $options));



		// The maximum execution time of the application in seconds.  Zero is infinite.
		$tmp = (int) $this->config->get('max_execution_time', 0);
		$this->config->set('max_execution_time', $tmp);

		// The maximum request parsing time of the application in seconds.  Zero is infinite.
		$tmp = (int) $this->config->get('max_input_time', 0);
		$this->config->set('max_input_time', $tmp);

		// The maximum amount of memory the application can use.  Zero is infinite.
		$tmp = (string) $this->config->get('max_memory_limit', '128M');
		$this->config->set('max_memory_limit', $tmp);

		// Option to kill the daemon if it cannot switch to the chosen identity.
		$tmp = (bool) $this->config->get('application_require_identity', 1);
		$this->config->set('application_require_identity', $tmp);



		// The home directory of the daemon.
		$tmp = (string) $this->config->get('application_directory', dirname($this->input->executable));
		$this->config->set('application_directory', $tmp);

		// The pid file location.
		$tmp = (string) $this->config->get('application_pid_file', '/var/run/'.$this->name.'/'.$this->name.'.pid');
		$this->config->set('application_pid_file', $tmp);

		// The chkconfig parameters for init.d: runlevel startpriority stoppriority
		$tmp = (string) $this->config->get('application_check_config', '- 99 0');
		$this->config->set('application_check_config', $tmp);

		return true;
	}
}
