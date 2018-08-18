<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console\Event;

use Joomla\Application\ApplicationEvents;
use Joomla\Console\Application;
use Joomla\Console\CommandInterface;

/**
 * Event triggered when an uncaught Throwable is received by the application.
 *
 * @since  __DEPLOY_VERSION__
 */
class ConsoleErrorEvent extends ConsoleEvent
{
	/**
	 * The Throwable object with the error data.
	 *
	 * @var    \Throwable
	 * @since  __DEPLOY_VERSION__
	 */
	private $error;

	/**
	 * The exit code to use for the application.
	 *
	 * @var    integer|null
	 * @since  __DEPLOY_VERSION__
	 */
	private $exitCode;

	/**
	 * Event constructor.
	 *
	 * @param   \Throwable             $error        The Throwable object with the error data.
	 * @param   Application            $application  The active application.
	 * @param   CommandInterface|null  $command      The command being executed.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(\Throwable $error, Application $application, CommandInterface $command = null)
	{
		parent::__construct(ApplicationEvents::ERROR, $application, $command);

		$this->error = $error;
	}

	/**
	 * Get the error object.
	 *
	 * @return  \Throwable
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getError(): \Throwable
	{
		return $this->error;
	}

	/**
	 * Gets the exit code.
	 *
	 * @return  integer
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getExitCode(): int
	{
		return $this->exitCode ?: (is_int($this->error->getCode()) && $this->error->getCode() !== 0 ? $this->error->getCode() : 1);
	}

	/**
	 * Set the error object.
	 *
	 * @param   \Throwable  $error  The error object to set to the event.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setError(\Throwable $error)
	{
		$this->error = $error;
	}

	/**
	 * Sets the exit code.
	 *
	 * @param   integer  $exitCode  The command exit code.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setExitCode(int $exitCode)
	{
		$this->exitCode = $exitCode;

		$r = new \ReflectionProperty($this->error, 'code');
		$r->setAccessible(true);
		$r->setValue($this->error, $this->exitCode);
	}
}
