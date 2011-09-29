<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector for the JDaemon class.
 *
 * @package		Joomla.UnitTest
 * @subpackage  Application
 *
 * @since       11.1
 */
class JDaemonInspector extends JDaemon
{
	/**
	 * @var     integer  Mimic the response of the pcntlChildExitStatus method.
	 * @since   11.3
	 */
	public static $pcntlChildExitStatus = 0;

	/**
	 * @var     integer  Mimic the response of the pcntlFork method.
	 * @since   11.3
	 */
	public static $pcntlFork = 0;

	/**
	 * @var     boolean  Mimic the response of the pcntlSignal method.
	 * @since   11.3
	 */
	public static $pcntlSignal = true;

	/**
	 * @var     integer  Mimic the response of the pcntlWait method.
	 * @since   11.3
	 */
	public static $pcntlWait = 0;

	/**
	 * @var     array  Container for successfully setup signal handlers.
	 * @since   11.3
	 */
		public $setupSignalHandlers = array();

	/**
	 * Method for inspecting protected variables.
	 *
	 * @param   string  $name  The name of the property.
	 *
	 * @return  mixed  The value of the class variable.
	 *
	 * @since   11.3
	 */
	public function getClassProperty($name)
	{
		if (property_exists($this, $name))
		{
			return $this->$name;
		}
		else
		{
			throw new Exception('Undefined or private property: ' . __CLASS__.'::'.$name);
		}
	}

	/**
	 * Method for setting protected static $instance.
	 *
	 * @param   mixed  $value  The value of the property.
	 *
	 * @return  void.
	 *
	 * @since   11.3
	 */
	public function setClassInstance($value)
	{
		self::$instance = $value;
	}

	/**
	 * Method for setting protected static $signals.
	 *
	 * @param   mixed  $value  The value of the property.
	 *
	 * @return  void.
	 *
	 * @since   11.3
	 */
	public function setClassSignals(array $value)
	{
		self::$signals = $value;
	}

	/**
	 * Method for setting protected variables.
	 *
	 * @param   string  $name  The name of the property.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void.
	 *
	 * @since   11.3
	 */
	public function setClassProperty($name, $value)
	{
		if (property_exists($this, $name))
		{
			$this->$name = $value;
		}
		else
		{
			throw new Exception('Undefined or private property: ' . __CLASS__.'::'.$name);
		}
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  boolean  True if identity successfully changed
	 *
	 * @since   11.3
	 */
	public function changeIdentity()
	{
		return parent::changeIdentity();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function gc()
	{
		return parent::gc();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function daemonize()
	{
		return parent::daemonize();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function setupSignalHandlers()
	{
		return parent::setupSignalHandlers();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function fork()
	{
		return parent::fork();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function writeProcessIdFile()
	{
		return parent::writeProcessIdFile();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   boolean  $restart  True to restart the daemon on exit.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function shutdown($restart = false)
	{
		return parent::shutdown($restart);
	}

	/**
	 * Method to return the exit code of a terminated child process.
	 *
	 * @param   integer  $status  The status parameter is the status parameter supplied to a successful call to pcntl_waitpid().
	 *
	 * @return  integer  The child process exit code.
	 *
	 * @see     pcntl_wexitstatus()
	 * @since   11.3
	 */
	public function pcntlChildExitStatus($status)
	{
		return self::$pcntlChildExitStatus;
	}

	/**
	 * Method to return the exit code of a terminated child process.
	 *
	 * @return  integer  On success, the PID of the child process is returned in the parent's thread
	 *                   of execution, and a 0 is returned in the child's thread of execution. On
	 *                   failure, a -1 will be returned in the parent's context, no child process
	 *                   will be created, and a PHP error is raised.
	 *
	 * @see     pcntl_fork()
	 * @since   11.3
	 */
	public function pcntlFork()
	{
		return self::$pcntlFork;
	}

	/**
	 * Method to install a signal handler.
	 *
	 * @param   integer   $signal   The signal number.
	 * @param   callback  $handler  The signal handler which may be the name of a user created function,
	 *                              or method, or either of the two global constants SIG_IGN or SIG_DFL.
	 * @param   boolean   $restart  Specifies whether system call restarting should be used when this
	 *                              signal arrives.
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     pcntl_signal()
	 * @since   11.3
	 */
	public function pcntlSignal($signal , $handler, $restart = true)
	{
		if (self::$pcntlSignal)
		{
			$this->setupSignalHandlers[] = $signal;
		}

		return self::$pcntlSignal;
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
	 * @see     pcntl_wait()
	 * @since   11.3
	 */
	public function pcntlWait(&$status, $options = 0)
	{
		return self::$pcntlWait;
	}
}
