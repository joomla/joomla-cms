<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application;

use Joomla\Input;
use Joomla\Registry\Registry;
use Psr\Log\LoggerAwareInterface;

/**
 * Class to turn Cli applications into daemons.  It requires CLI and PCNTL support built into PHP.
 *
 * @see    http://www.php.net/manual/en/book.pcntl.php
 * @see    http://php.net/manual/en/features.commandline.php
 * @since  1.0
 */
abstract class AbstractDaemonApplication extends AbstractCliApplication implements LoggerAwareInterface
{
	/**
	 * @var    array  The available POSIX signals to be caught by default.
	 * @see    http://php.net/manual/pcntl.constants.php
	 * @since  1.0
	 */
	protected static $signals = array(
		'SIGHUP',
		'SIGINT',
		'SIGQUIT',
		'SIGILL',
		'SIGTRAP',
		'SIGABRT',
		'SIGIOT',
		'SIGBUS',
		'SIGFPE',
		'SIGUSR1',
		'SIGSEGV',
		'SIGUSR2',
		'SIGPIPE',
		'SIGALRM',
		'SIGTERM',
		'SIGSTKFLT',
		'SIGCLD',
		'SIGCHLD',
		'SIGCONT',
		'SIGTSTP',
		'SIGTTIN',
		'SIGTTOU',
		'SIGURG',
		'SIGXCPU',
		'SIGXFSZ',
		'SIGVTALRM',
		'SIGPROF',
		'SIGWINCH',
		'SIGPOLL',
		'SIGIO',
		'SIGPWR',
		'SIGSYS',
		'SIGBABY',
		'SIG_BLOCK',
		'SIG_UNBLOCK',
		'SIG_SETMASK'
	);

	/**
	 * @var    boolean  True if the daemon is in the process of exiting.
	 * @since  1.0
	 */
	protected $exiting = false;

	/**
	 * @var    integer  The parent process id.
	 * @since  1.0
	 */
	protected $parentId = 0;

	/**
	 * @var    integer  The process id of the daemon.
	 * @since  1.0
	 */
	protected $processId = 0;

	/**
	 * @var    boolean  True if the daemon is currently running.
	 * @since  1.0
	 */
	protected $running = false;

	/**
	 * Class constructor.
	 *
	 * @param   Input\Cli      $input     An optional argument to provide dependency injection for the application's input object.  If the
	 *                                    argument is an Input\Cli object that object will become the application's input object, otherwise
	 *                                    a default input object is created.
	 * @param   Registry       $config    An optional argument to provide dependency injection for the application's config object.  If the
	 *                                    argument is a Registry object that object will become the application's config object, otherwise
	 *                                    a default config object is created.
	 * @param   Cli\CliOutput  $output    An optional argument to provide dependency injection for the application's output object.  If the
	 *                                    argument is a Cli\CliOutput object that object will become the application's input object, otherwise
	 *                                    a default output object is created.
	 * @param   Cli\CliInput   $cliInput  An optional argument to provide dependency injection for the application's CLI input object.  If the
	 *                                    argument is a Cli\CliInput object that object will become the application's input object, otherwise
	 *                                    a default input object is created.
	 *
	 * @since   1.0
	 */
	public function __construct(Cli $input = null, Registry $config = null, Cli\CliOutput $output = null, Cli\CliInput $cliInput = null)
	{
		// Verify that the process control extension for PHP is available.
		// @codeCoverageIgnoreStart
		if (!defined('SIGHUP'))
		{
			$this->getLogger()->error('The PCNTL extension for PHP is not available.');

			throw new \RuntimeException('The PCNTL extension for PHP is not available.');
		}

		// Verify that POSIX support for PHP is available.
		if (!function_exists('posix_getpid'))
		{
			$this->getLogger()->error('The POSIX extension for PHP is not available.');

			throw new \RuntimeException('The POSIX extension for PHP is not available.');
		}

		// @codeCoverageIgnoreEnd

		// Call the parent constructor.
		parent::__construct($input, $config, $output, $cliInput);

		// Set some system limits.
		@set_time_limit($this->get('max_execution_time', 0));

		if ($this->get('max_memory_limit') !== null)
		{
			ini_set('memory_limit', $this->get('max_memory_limit', '256M'));
		}

		// Flush content immediately.
		ob_implicit_flush();
	}

	/**
	 * Method to handle POSIX signals.
	 *
	 * @param   integer  $signal  The received POSIX signal.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @see     pcntl_signal()
	 * @throws  \RuntimeException
	 */
	public function signal($signal)
	{
		// Log all signals sent to the daemon.
		$this->getLogger()->debug('Received signal: ' . $signal);

		// Let's make sure we have an application instance.
		if (!is_subclass_of($this, __CLASS__))
		{
			$this->getLogger()->emergency('Cannot find the application instance.');

			throw new \RuntimeException('Cannot find the application instance.');
		}

		// @event onReceiveSignal

		switch ($signal)
		{
			case SIGINT:
			case SIGTERM:
				// Handle shutdown tasks
				if ($this->running && $this->isActive())
				{
					$this->shutdown();
				}
				else
				{
					$this->close();
				}

				break;

			case SIGHUP:
				// Handle restart tasks
				if ($this->running && $this->isActive())
				{
					$this->shutdown(true);
				}
				else
				{
					$this->close();
				}

				break;

			case SIGCHLD:
				// A child process has died
				while ($this->pcntlWait($signal, WNOHANG || WUNTRACED) > 0)
				{
					usleep(1000);
				}

				break;

			case SIGCLD:
				while ($this->pcntlWait($signal, WNOHANG) > 0)
				{
					$signal = $this->pcntlChildExitStatus($signal);
				}

				break;

			default:
				break;
		}
	}

	/**
	 * Check to see if the daemon is active.  This does not assume that $this daemon is active, but
	 * only if an instance of the application is active as a daemon.
	 *
	 * @return  boolean  True if daemon is active.
	 *
	 * @since   1.0
	 */
	public function isActive()
	{
		// Get the process id file location for the application.
		$pidFile = $this->get('application_pid_file');

		// If the process id file doesn't exist then the daemon is obviously not running.
		if (!is_file($pidFile))
		{
			return false;
		}

		// Read the contents of the process id file as an integer.
		$fp = fopen($pidFile, 'r');
		$pid = fread($fp, filesize($pidFile));
		$pid = (int) $pid;
		fclose($fp);

		// Check to make sure that the process id exists as a positive integer.
		if (!$pid)
		{
			return false;
		}

		// Check to make sure the process is active by pinging it and ensure it responds.
		if (!posix_kill($pid, 0))
		{
			// No response so remove the process id file and log the situation.
			@ unlink($pidFile);

			$this->getLogger()->warning('The process found based on PID file was unresponsive.');

			return false;
		}

		return true;
	}

	/**
	 * Load an object or array into the application configuration object.
	 *
	 * @param   mixed  $data  Either an array or object to be loaded into the configuration object.
	 *
	 * @return  AbstractDaemonApplication  Instance of $this to allow chaining.
	 *
	 * @since   1.0
	 */
	public function loadConfiguration($data)
	{
		/*
		 * Setup some application metadata options.  This is useful if we ever want to write out startup scripts
		 * or just have some sort of information available to share about things.
		 */

		// The application author name.  This string is used in generating startup scripts and has
		// a maximum of 50 characters.
		$tmp = (string) $this->get('author_name', 'Joomla Framework');
		$this->set('author_name', (strlen($tmp) > 50) ? substr($tmp, 0, 50) : $tmp);

		// The application author email.  This string is used in generating startup scripts.
		$tmp = (string) $this->get('author_email', 'admin@joomla.org');
		$this->set('author_email', filter_var($tmp, FILTER_VALIDATE_EMAIL));

		// The application name.  This string is used in generating startup scripts.
		$tmp = (string) $this->get('application_name', 'JApplicationDaemon');
		$this->set('application_name', (string) preg_replace('/[^A-Z0-9_-]/i', '', $tmp));

		// The application description.  This string is used in generating startup scripts.
		$tmp = (string) $this->get('application_description', 'A generic Joomla Framework application.');
		$this->set('application_description', filter_var($tmp, FILTER_SANITIZE_STRING));

		/*
		 * Setup the application path options.  This defines the default executable name, executable directory,
		 * and also the path to the daemon process id file.
		 */

		// The application executable daemon.  This string is used in generating startup scripts.
		$tmp = (string) $this->get('application_executable', basename($this->input->executable));
		$this->set('application_executable', $tmp);

		// The home directory of the daemon.
		$tmp = (string) $this->get('application_directory', dirname($this->input->executable));
		$this->set('application_directory', $tmp);

		// The pid file location.  This defaults to a path inside the /tmp directory.
		$name = $this->get('application_name');
		$tmp = (string) $this->get('application_pid_file', strtolower('/tmp/' . $name . '/' . $name . '.pid'));
		$this->set('application_pid_file', $tmp);

		/*
		 * Setup the application identity options.  It is important to remember if the default of 0 is set for
		 * either UID or GID then changing that setting will not be attempted as there is no real way to "change"
		 * the identity of a process from some user to root.
		 */

		// The user id under which to run the daemon.
		$tmp = (int) $this->get('application_uid', 0);
		$options = array('options' => array('min_range' => 0, 'max_range' => 65000));
		$this->set('application_uid', filter_var($tmp, FILTER_VALIDATE_INT, $options));

		// The group id under which to run the daemon.
		$tmp = (int) $this->get('application_gid', 0);
		$options = array('options' => array('min_range' => 0, 'max_range' => 65000));
		$this->set('application_gid', filter_var($tmp, FILTER_VALIDATE_INT, $options));

		// Option to kill the daemon if it cannot switch to the chosen identity.
		$tmp = (bool) $this->get('application_require_identity', 1);
		$this->set('application_require_identity', $tmp);

		/*
		 * Setup the application runtime options.  By default our execution time limit is infinite obviously
		 * because a daemon should be constantly running unless told otherwise.  The default limit for memory
		 * usage is 128M, which admittedly is a little high, but remember it is a "limit" and PHP's memory
		 * management leaves a bit to be desired :-)
		 */

		// The maximum execution time of the application in seconds.  Zero is infinite.
		$tmp = $this->get('max_execution_time');

		if ($tmp !== null)
		{
			$this->set('max_execution_time', (int) $tmp);
		}

		// The maximum amount of memory the application can use.
		$tmp = $this->get('max_memory_limit', '256M');

		if ($tmp !== null)
		{
			$this->set('max_memory_limit', (string) $tmp);
		}

		return $this;
	}

	/**
	 * Execute the daemon.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		// @event onBeforeExecute

		// Enable basic garbage collection.
		gc_enable();

		$this->getLogger()->info('Starting ' . $this->name);

		// Set off the process for becoming a daemon.
		if ($this->daemonize())
		{
			// Declare ticks to start signal monitoring. When you declare ticks, PCNTL will monitor
			// incoming signals after each tick and call the relevant signal handler automatically.
			declare (ticks = 1);

			// Start the main execution loop.
			while (true)
			{
				// Perform basic garbage collection.
				$this->gc();

				// Don't completely overload the CPU.
				usleep(1000);

				// Execute the main application logic.
				$this->doExecute();
			}
		}
		else
		// We were not able to daemonize the application so log the failure and die gracefully.
		{
			$this->getLogger()->info('Starting ' . $this->name . ' failed');
		}

		// @event onAfterExecute
	}

	/**
	 * Restart daemon process.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 * @since   1.0
	 */
	public function restart()
	{
		$this->getLogger()->info('Stopping ' . $this->name);

		$this->shutdown(true);
	}

	/**
	 * Stop daemon process.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 * @since   1.0
	 */
	public function stop()
	{
		$this->getLogger()->info('Stopping ' . $this->name);

		$this->shutdown();
	}

	/**
	 * Method to change the identity of the daemon process and resources.
	 *
	 * @return  boolean  True if identity successfully changed
	 *
	 * @since   1.0
	 * @see     posix_setuid()
	 */
	protected function changeIdentity()
	{
		// Get the group and user ids to set for the daemon.
		$uid = (int) $this->get('application_uid', 0);
		$gid = (int) $this->get('application_gid', 0);

		// Get the application process id file path.
		$file = $this->get('application_pid_file');

		// Change the user id for the process id file if necessary.
		if ($uid && (fileowner($file) != $uid) && (!@ chown($file, $uid)))
		{
			$this->getLogger()->error('Unable to change user ownership of the process id file.');

			return false;
		}

		// Change the group id for the process id file if necessary.
		if ($gid && (filegroup($file) != $gid) && (!@ chgrp($file, $gid)))
		{
			$this->getLogger()->error('Unable to change group ownership of the process id file.');

			return false;
		}

		// Set the correct home directory for the process.
		if ($uid && ($info = posix_getpwuid($uid)) && is_dir($info['dir']))
		{
			system('export HOME="' . $info['dir'] . '"');
		}

		// Change the user id for the process necessary.
		if ($uid && (posix_getuid($file) != $uid) && (!@ posix_setuid($uid)))
		{
			$this->getLogger()->error('Unable to change user ownership of the proccess.');

			return false;
		}

		// Change the group id for the process necessary.
		if ($gid && (posix_getgid($file) != $gid) && (!@ posix_setgid($gid)))
		{
			$this->getLogger()->error('Unable to change group ownership of the proccess.');

			return false;
		}

		// Get the user and group information based on uid and gid.
		$user = posix_getpwuid($uid);
		$group = posix_getgrgid($gid);

		$this->getLogger()->info('Changed daemon identity to ' . $user['name'] . ':' . $group['name']);

		return true;
	}

	/**
	 * Method to put the application into the background.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	protected function daemonize()
	{
		// Is there already an active daemon running?
		if ($this->isActive())
		{
			$this->getLogger()->emergency($this->name . ' daemon is still running. Exiting the application.');

			return false;
		}

		// Reset Process Information
		$this->safeMode = !!@ ini_get('safe_mode');
		$this->processId = 0;
		$this->running = false;

		// Detach process!
		try
		{
			// Check if we should run in the foreground.
			if (!$this->input->get('f'))
			{
				// Detach from the terminal.
				$this->detach();
			}
			else
			{
				// Setup running values.
				$this->exiting = false;
				$this->running = true;

				// Set the process id.
				$this->processId = (int) posix_getpid();
				$this->parentId = $this->processId;
			}
		}
		catch (\RuntimeException $e)
		{
			$this->getLogger()->emergency('Unable to fork.');

			return false;
		}

		// Verify the process id is valid.
		if ($this->processId < 1)
		{
			$this->getLogger()->emergency('The process id is invalid; the fork failed.');

			return false;
		}

		// Clear the umask.
		@ umask(0);

		// Write out the process id file for concurrency management.
		if (!$this->writeProcessIdFile())
		{
			$this->getLogger()->emergency('Unable to write the pid file at: ' . $this->get('application_pid_file'));

			return false;
		}

		// Attempt to change the identity of user running the process.
		if (!$this->changeIdentity())
		{
			// If the identity change was required then we need to return false.
			if ($this->get('application_require_identity'))
			{
				$this->getLogger()->critical('Unable to change process owner.');

				return false;
			}
			else
			{
				$this->getLogger()->warning('Unable to change process owner.');
			}
		}

		// Setup the signal handlers for the daemon.
		if (!$this->setupSignalHandlers())
		{
			return false;
		}

		// Change the current working directory to the application working directory.
		@ chdir($this->get('application_directory'));

		return true;
	}

	/**
	 * This is truly where the magic happens.  This is where we fork the process and kill the parent
	 * process, which is essentially what turns the application into a daemon.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	protected function detach()
	{
		$this->getLogger()->debug('Detaching the ' . $this->name . ' daemon.');

		// Attempt to fork the process.
		$pid = $this->fork();

		// If the pid is positive then we successfully forked, and can close this application.
		if ($pid)
		{
			// Add the log entry for debugging purposes and exit gracefully.
			$this->getLogger()->debug('Ending ' . $this->name . ' parent process');

			$this->close();
		}
		else
		// We are in the forked child process.
		{
			// Setup some protected values.
			$this->exiting = false;
			$this->running = true;

			// Set the parent to self.
			$this->parentId = $this->processId;
		}
	}

	/**
	 * Method to fork the process.
	 *
	 * @return  integer  The child process id to the parent process, zero to the child process.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	protected function fork()
	{
		// Attempt to fork the process.
		$pid = $this->pcntlFork();

		// If the fork failed, throw an exception.
		if ($pid === -1)
		{
			throw new \RuntimeException('The process could not be forked.');
		}
		elseif ($pid === 0)
		// Update the process id for the child.
		{
			$this->processId = (int) posix_getpid();
		}
		else
		// Log the fork in the parent.
		{
			// Log the fork.
			$this->getLogger()->debug('Process forked ' . $pid);
		}

		// Trigger the onFork event.
		$this->postFork();

		return $pid;
	}

	/**
	 * Method to perform basic garbage collection and memory management in the sense of clearing the
	 * stat cache.  We will probably call this method pretty regularly in our main loop.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 * @since   1.0
	 */
	protected function gc()
	{
		// Perform generic garbage collection.
		gc_collect_cycles();

		// Clear the stat cache so it doesn't blow up memory.
		clearstatcache();
	}

	/**
	 * Method to attach the AbstractDaemonApplication signal handler to the known signals.  Applications
	 * can override these handlers by using the pcntl_signal() function and attaching a different
	 * callback method.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 * @see     pcntl_signal()
	 */
	protected function setupSignalHandlers()
	{
		// We add the error suppression for the loop because on some platforms some constants are not defined.
		foreach (self::$signals as $signal)
		{
			// Ignore signals that are not defined.
			if (!defined($signal) || !is_int(constant($signal)) || (constant($signal) === 0))
			{
				// Define the signal to avoid notices.
				$this->getLogger()->debug('Signal "' . $signal . '" not defined. Defining it as null.');

				define($signal, null);

				// Don't listen for signal.
				continue;
			}

			// Attach the signal handler for the signal.
			if (!$this->pcntlSignal(constant($signal), array($this, 'signal')))
			{
				$this->getLogger()->emergency(sprintf('Unable to reroute signal handler: %s', $signal));

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to shut down the daemon and optionally restart it.
	 *
	 * @param   boolean  $restart  True to restart the daemon on exit.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function shutdown($restart = false)
	{
		// If we are already exiting, chill.
		if ($this->exiting)
		{
			return;
		}
		else
		// If not, now we are.
		{
			$this->exiting = true;
		}

		// If we aren't already daemonized then just kill the application.
		if (!$this->running && !$this->isActive())
		{
			$this->getLogger()->info('Process was not daemonized yet, just halting current process');

			$this->close();
		}

		// Only read the pid for the parent file.
		if ($this->parentId == $this->processId)
		{
			// Read the contents of the process id file as an integer.
			$fp = fopen($this->get('application_pid_file'), 'r');
			$pid = fread($fp, filesize($this->get('application_pid_file')));
			$pid = (int) $pid;
			fclose($fp);

			// Remove the process id file.
			@ unlink($this->get('application_pid_file'));

			// If we are supposed to restart the daemon we need to execute the same command.
			if ($restart)
			{
				$this->close(exec(implode(' ', $GLOBALS['argv']) . ' > /dev/null &'));
			}
			else
			// If we are not supposed to restart the daemon let's just kill -9.
			{
				passthru('kill -9 ' . $pid);
				$this->close();
			}
		}
	}

	/**
	 * Method to write the process id file out to disk.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	protected function writeProcessIdFile()
	{
		// Verify the process id is valid.
		if ($this->processId < 1)
		{
			$this->getLogger()->emergency('The process id is invalid.');

			return false;
		}

		// Get the application process id file path.
		$file = $this->get('application_pid_file');

		if (empty($file))
		{
			$this->getLogger()->error('The process id file path is empty.');

			return false;
		}

		// Make sure that the folder where we are writing the process id file exists.
		$folder = dirname($file);

		if (!is_dir($folder) && !@ mkdir($folder, $this->get('folder_permission', 0755)))
		{
			$this->getLogger()->error('Unable to create directory: ' . $folder);

			return false;
		}

		// Write the process id file out to disk.
		if (!file_put_contents($file, $this->processId))
		{
			$this->getLogger()->error('Unable to write proccess id file: ' . $file);

			return false;
		}

		// Make sure the permissions for the proccess id file are accurate.
		if (!chmod($file, $this->get('file_permission', 0644)))
		{
			$this->getLogger()->error('Unable to adjust permissions for the proccess id file: ' . $file);

			return false;
		}

		return true;
	}

	/**
	 * Method to handle post-fork triggering of the onFork event.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function postFork()
	{
		// @event onFork
	}

	/**
	 * Method to return the exit code of a terminated child process.
	 *
	 * @param   integer  $status  The status parameter is the status parameter supplied to a successful call to pcntl_waitpid().
	 *
	 * @return  integer  The child process exit code.
	 *
	 * @codeCoverageIgnore
	 * @see     pcntl_wexitstatus()
	 * @since   1.0
	 */
	protected function pcntlChildExitStatus($status)
	{
		return pcntl_wexitstatus($status);
	}

	/**
	 * Method to return the exit code of a terminated child process.
	 *
	 * @return  integer  On success, the PID of the child process is returned in the parent's thread
	 *                   of execution, and a 0 is returned in the child's thread of execution. On
	 *                   failure, a -1 will be returned in the parent's context, no child process
	 *                   will be created, and a PHP error is raised.
	 *
	 * @codeCoverageIgnore
	 * @see     pcntl_fork()
	 * @since   1.0
	 */
	protected function pcntlFork()
	{
		return pcntl_fork();
	}

	/**
	 * Method to install a signal handler.
	 *
	 * @param   integer   $signal   The signal number.
	 * @param   callable  $handler  The signal handler which may be the name of a user created function,
	 *                              or method, or either of the two global constants SIG_IGN or SIG_DFL.
	 * @param   boolean   $restart  Specifies whether system call restarting should be used when this
	 *                              signal arrives.
	 *
	 * @return  boolean  True on success.
	 *
	 * @codeCoverageIgnore
	 * @see     pcntl_signal()
	 * @since   1.0
	 */
	protected function pcntlSignal($signal , $handler, $restart = true)
	{
		return pcntl_signal($signal, $handler, $restart);
	}

	/**
	 * Method to wait on or return the status of a forked child.
	 *
	 * @param   integer  &$status  Status information.
	 * @param   integer  $options  If wait3 is available on your system (mostly BSD-style systems),
	 *                             you can provide the optional options parameter.
	 *
	 * @return  integer  The process ID of the child which exited, -1 on error or zero if WNOHANG
	 *                   was provided as an option (on wait3-available systems) and no child was available.
	 *
	 * @codeCoverageIgnore
	 * @see     pcntl_wait()
	 * @since   1.0
	 */
	protected function pcntlWait(&$status, $options = 0)
	{
		return pcntl_wait($status, $options);
	}
}
