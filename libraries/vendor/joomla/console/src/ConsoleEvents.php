<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console;

/**
 * Class defining the events available in the application.
 *
 * @since  __DEPLOY_VERSION__
 */
final class ConsoleEvents
{
	/**
	 * The BEFORE_COMMAND_EXECUTE event is an event triggered before a command is executed.
	 *
	 * This event allows developers to modify information about the command or the command's
	 * dependencies prior to the command being executed.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	const BEFORE_COMMAND_EXECUTE = 'console.before_command_execute';

	/**
	 * The ERROR event is an event triggered when a Throwable is uncaught.
	 *
	 * This event allows you to inspect the Throwable, implement additional error handling/reporting
	 * mechanisms, and set the process' exit code.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	const ERROR = 'console.error';

	/**
	 * The TERMINATE event is an event triggered immediately before the application is exited.
	 *
	 * This event allows developers to perform any post-process actions and to maniuplate
	 * the process' exit code.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	const TERMINATE = 'console.terminate';
}
