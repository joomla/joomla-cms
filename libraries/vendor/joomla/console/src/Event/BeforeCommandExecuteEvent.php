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

/**
 * Event triggered before a command is executed.
 *
 * @since  __DEPLOY_VERSION__
 */
class BeforeCommandExecuteEvent extends ConsoleEvent
{
	/**
	 * The return code for a command disabled by this event.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	const RETURN_CODE_DISABLED = 113;

	/**
	 * Flag indicating the command is enabled
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $commandEnabled = true;

	/**
	 * Event constructor.
	 *
	 * @param   Application            $application  The active application.
	 * @param   CommandInterface|null  $command      The command being executed.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(Application $application, CommandInterface $command = null)
	{
		parent::__construct(ConsoleEvents::BEFORE_COMMAND_EXECUTE, $application, $command);

		if ($command)
		{
			$this->commandEnabled = $command->isEnabled();
		}
	}

	/**
	 * Disable the command.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function disableCommand()
	{
		$this->commandEnabled = false;
	}

	/**
	 * Enable the command.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function enableCommand()
	{
		$this->commandEnabled = false;
	}

	/**
	 * Check if the command is enabled.
	 *
	 * @return    boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isCommandEnabled(): bool
	{
		return $this->commandEnabled;
	}
}
