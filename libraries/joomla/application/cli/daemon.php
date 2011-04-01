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
jimport('joomla.filesystem.folder');

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
	 * @var    integer  The process id of the daemon.
	 * @since  11.1
	 */
	protected $processId = 0;

	/**
	 * @var    bool  True if the daemon is currently running.
	 * @since  11.1
	 */
	protected $running = false;

	/**
	 * @var    array  The available POSIX signals to be caught by default.
	 * @since  11.1
	 * @see    http://php.net/manual/pcntl.constants.php
	 */
	protected static $signals = array(
		SIGHUP, SIGINT, SIGQUIT, SIGILL, SIGTRAP, SIGABRT, SIGIOT, SIGBUS, SIGFPE, SIGUSR1,
		SIGSEGV, SIGUSR2, SIGPIPE, SIGALRM, SIGTERM, SIGSTKFLT, SIGCLD, SIGCHLD, SIGCONT,
		SIGTSTP, SIGTTIN, SIGTTOU, SIGURG, SIGXCPU, SIGXFSZ, SIGVTALRM, SIGPROF, SIGWINCH,
		SIGPOLL, SIGIO, SIGPWR, SIGSYS, SIGBABY, SIG_BLOCK, SIG_UNBLOCK, SIG_SETMASK
	);

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

		JLog::add('Starting '.$this->name, JLog::INFO);

		// Set off the process for becoming a daemon.
		if ($this->daemonize()) {

			// Daemonization succeeded (is that a word?), so now we start our main execution loop.
			while (true)
			{
				usleep(1000);
				$this->gc();
				$this->execute();
			}
		}
		else {
			JLog::add('Starting '.$this->name.' failed', JLog::INFO);
		}
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
		JLog::add('Stopping '.$this->name, JLog::INFO);
		$this->shutdown(true);
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
		JLog::add('Stopping '.$this->name, JLog::INFO);
		$this->shutdown();
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
	 * @return  bool
	 *
	 * @since   11.1
	 * @throws  ApplicationException
	 */
	protected function daemonize()
	{
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
		try {
			$this->fork();
		}
		catch (ApplicationException $e) {
			JLog::add('Unable to fork.', JLog::EMERGENCY);
			return false;
		}

		// Verify the process id is valid.
		if ($this->processId < 1) {
			JLog::add('The process id is invalid; the fork failed.', JLog::EMERGENCY);
			return false;
		}

		// Clear the umask.
		@ umask(0);

		// Write out the process id file for concurrency management.
		if (!$this->writeProcessIdFile()) {
			JLog::add('Unable to write the pid file at: '.$this->config->get('application_pid_file'), JLog::EMERGENCY);
			return false;
		}

		// Attempt to change the identity of user running the process.
//		if (!$this->changeIdentity()) {
//
//			// If the identity change was required then we need to return false.
//			if ($this->config->get('application_require_identity')) {
//				JLog::add('Unable to change process owner.', JLog::CRITICAL);
//				return false;
//			}
//			else {
//				JLog::add('Unable to change process owner.', JLog::WARNING);
//			}
//		}

		// Setup the signal handlers for the daemon.
		if (!$this->setupSignalHandlers()) {
			return false;
		}

		// Change the current working directory to the application working directory.
		@ chdir($this->config->get('application_directory'));

		return true;
	}

	/**
	 * Method to write the process id file out to disk.
	 *
	 * @return  bool
	 *
	 * @since   11.1
	 */
	protected function writeProcessIdFile()
	{
		// Verify the process id is valid.
		if ($this->processId < 1) {
			JLog::add('The process id is invalid.', JLog::EMERGENCY);
			return false;
		}

		// Get the application process id file path.
		$file = $this->config->get('application_pid_file');
		if (empty($file)) {
			JLog::add('The process id file path is empty.', JLog::ERROR);
			return false;
		}

		// Make sure that the folder where we are writing the process id file exists.
		$folder = dirname($file);
		if (!is_dir($folder) && !JFolder::create($folder)) {
			JLog::add('Unable to create directory: '.$folder, JLog::ERROR);
			return false;
		}

		// Write the process id file out to disk.
		if (!file_put_contents($file, $this->processId)) {
			JLog::add('Unable to write proccess id file: '.$file, JLog::ERROR);
			return false;
		}

		// Make sure the permissions for the proccess id file are accurate.
		if (!chmod($file, 0644)) {
			JLog::add('Unable to adjust permissions for the proccess id file: '.$file, JLog::ERROR);
			return false;
		}

		return true;
	}

	/**
	 * Method to handle POSIX signals.
	 *
	 * @param   integer  $signal  The recieved POSIX signal.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @see     pcntl_signal()
	 */
	static public function handleSignal($signal)
	{
		// Log all signals sent to the daemon.
		JLog::add('Received signal: '.$signal, JLog::DEBUG);

		// Fire the onRecieveSignal event.
		$this->triggerEvent('onRecieveSignal', array($signal));

		switch ($signal)
		{
			case SIGTERM :
				// Handle shutdown tasks
				if ($this->running && $this->isActive()) {
					$this->shutdown();
				} else {
					$this->close();
				}
				break;
			case SIGHUP :
				// Handle restart tasks
				if ($this->running && $this->isActive()) {
					$this->shutdown(true);
				} else {
					$this->close();
				}
				break;
			case SIGCHLD :
				// A child process has died
				while (pcntl_wait($signal, WNOHANG OR WUNTRACED) > 0)
				{
					usleep(1000);
				}
				break;
			case SIGCLD:
				while (($pid = pcntl_wait($signal, WNOHANG)) > 0)
				{
					$signal = pcntl_wexitstatus($signal);
				}
				break;
			default :
				break;
		}
	}

	/**
	 * Method to attach the JDaemon signal handler to the known signals.  Applications can override
	 * these handlers by using the pcntl_signal() function and attaching a different callback method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @see     pcntl_signal()
	 */
	protected function setupSignalHandlers()
	{
		// We add the error suppression for the loop because on some platforms some constants are not defined.
		foreach (@ self::$signals as $signal)
		{
			// Ignore signals that are not defined.
			if (!is_int($signal)) {
				continue;
			}

			// Attach the signal handler for the signal.
			if (!pcntl_signal($signal, array('JDaemon', 'handleSignal'))) {
				JLog::add(sprintf('Unable to reroute signal handler: %s', $signal), JLog::EMERGENCY);
				return false;
			}
		}

		return true;
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
		$fp = fopen($pidFile, 'r');
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
			$this->processId = (int) posix_getpid();
		}
	}

	/**
	 * Method to shut down the daemon and optionally restart it.
	 *
	 * @param   bool  $restart  True to restart the daemon on exit.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function shutdown($restart = false)
	{
		// If we are already exiting, chill.
		if ($this->exiting) {
			return;
		}
		// If not, now we are.
		else {
			$this->exiting = true;
		}

		// Following caused a bug if pid couldn't be written because of
		// privileges
		// || !file_exists(self::opt('appPidLocation'))
		if ($this->running && $this->isActive()) {
			JLog::add('Process was not daemonized yet, just halting current process', JLog::INFO);
			$this->close();
		}

		// Read the contents of the process id file as an integer.
		$fp = fopen($this->config->get('application_pid_file'), 'r');
		$pid = fread($fp, filesize($this->config->get('application_pid_file')));
		$pid = intval($pid);
		fclose($fp);

		// Remove the process id file.
		@ unlink($this->config->get('application_pid_file'));

		// If we are supposed to restart the daemon we need to execute the same command.
		if ($restart) {
			$this->close(exec(implode(' ', $GLOBALS['argv']).' > /dev/null &'));
		}
		// If we are not supposed to restart the daemon let's just kill -9.
		else {
			passthru('kill -9 '.$pid);
			$this->close();
		}
	}

	/**
	 * Load the configuration file into the site object.
	 *
	 * @param   string  $file  The path to the configuration file.
	 *
	 * @return  bool    True on success.
	 *
	 * @since   11.1
	 */
	protected function loadConfiguration($file)
	{
		$loaded = true;

		// Perform the configuration file load.
		if (!parent::loadConfiguration($file)) {
			$loaded = false;
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
		$this->config->set('application_directory', strtolower($tmp));

		// The pid file location.
		$tmp = (string) $this->config->get('application_pid_file', '/tmp/'.$this->name.'/'.$this->name.'.pid');
		$this->config->set('application_pid_file', strtolower($tmp));

		// The chkconfig parameters for init.d: runlevel startpriority stoppriority
		$tmp = (string) $this->config->get('application_check_config', '- 99 0');
		$this->config->set('application_check_config', $tmp);

		return $loaded;
	}
}
