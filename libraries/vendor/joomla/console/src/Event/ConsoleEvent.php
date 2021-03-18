<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console\Event;

use Joomla\Console\Application;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Event\Event;

/**
 * Base event class for console events.
 *
 * @since  __DEPLOY_VERSION__
 */
class ConsoleEvent extends Event
{
	/**
	 * The active application.
	 *
	 * @var    Application
	 * @since  __DEPLOY_VERSION__
	 */
	private $application;

	/**
	 * The command being executed.
	 *
	 * @var    AbstractCommand|null
	 * @since  __DEPLOY_VERSION__
	 */
	private $command;

	/**
	 * Event constructor.
	 *
	 * @param   string                $name         The event name.
	 * @param   Application           $application  The active application.
	 * @param   AbstractCommand|null  $command      The command being executed.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(string $name, Application $application, ?AbstractCommand $command = null)
	{
		parent::__construct($name);

		$this->application = $application;
		$this->command     = $command;
	}

	/**
	 * Get the active application.
	 *
	 * @return  Application
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getApplication(): Application
	{
		return $this->application;
	}

	/**
	 * Get the command being executed.
	 *
	 * @return  AbstractCommand|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getCommand(): ?AbstractCommand
	{
		return $this->command;
	}
}
