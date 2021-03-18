<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console;

/**
 * Class defining the events available in the console application.
 *
 * @since  __DEPLOY_VERSION__
 */
final class ConsoleEvents
{
	/**
	 * The APPLICATION_ERROR event is an event triggered when an uncaught Throwable is received at the main application executor.
	 *
	 * This event allows developers to handle the Throwable.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public const APPLICATION_ERROR = 'console.application_error';

	/**
	 * The BEFORE_COMMAND_EXECUTE event is an event triggered before a command is executed.
	 *
	 * This event allows developers to modify information about the command or the command's
	 * dependencies prior to the command being executed.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public const BEFORE_COMMAND_EXECUTE = 'console.before_command_execute';

	/**
	 * The COMMAND_ERROR event is an event triggered when an uncaught Throwable from a command is received.
	 *
	 * This event allows developers to handle the Throwable.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public const COMMAND_ERROR = 'console.command_error';

	/**
	 * The TERMINATE event is an event triggered immediately before the application is exited.
	 *
	 * This event allows developers to perform any post-process actions and to maniuplate
	 * the process' exit code.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public const TERMINATE = 'console.terminate';
}
