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
	 * Method to perform basic garbage collection and memory management in the sense of clearing the stat cache.  We
	 * will probably call this method pretty regularly in our main loop.
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
	}

	/**
	 * This is truly where the magic happens.  This is where we fork the process and kill the parent process, which
	 * is essentially what turns the application into a daemon.
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
			JLog::add('Process could not be forked', JLog::WARNING);
			throw new ApplicationException();
		}
		// If the pid is a positive integer then we successfully forked the process and can close this application.
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
}
