<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console\Event;

use Joomla\Console\Application;
use Joomla\Console\CommandInterface;
use Joomla\Console\ConsoleEvents;
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
	 * @var    CommandInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $command;

	/**
	 * Event constructor.
	 *
	 * @param   string                 $name         The event name.
	 * @param   Application            $application  The active application.
	 * @param   CommandInterface|null  $command      The command being executed.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(string $name, Application $application, CommandInterface $command = null)
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
	 * @return  CommandInterface|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getCommand()
	{
		return $this->command;
	}
}
