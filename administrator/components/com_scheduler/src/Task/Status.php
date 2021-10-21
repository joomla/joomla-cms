<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Task;


/**
 * A namespace mapping Task statuses to integer values.
 *
 * @since __DEPLOY_VERSION__
 */
abstract class Status
{
	/**
	 * Replacement exit code used when a routine returns an invalid (non-integer) exit code.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const INVALID_EXIT = -2;

	/**
	 * Replacement exit code used when a routine does not return an exit code.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public const NO_EXIT = -1;

	/**
	 * Status code used when the routine just starts. This is not meant to be an exit code.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const RUNNING = 1;

	/**
	 * Exit code used on failure to acquire a pseudo-lock.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const NO_LOCK = 2;

	/**
	 * Exit code used on failure to run the task.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const NO_RUN = 3;

	/**
	 * Exit code used on failure to release lock/update the record.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public const NO_RELEASE = 4;

	/**
	 * Exit code used for task knockout.
	 * ? Should this be retained ?
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public const KO_RUN = 5;

	/**
	 * Exit code used when a task times out.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public const TIMEOUT = 124;

	/**
	 * Exit code when a *task* does not exist.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public const NO_TASK = 125;

	/**
	 * Exit code used when a *routine* is missing.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public const NO_ROUTINE = 127;

	/**
	 * Exit code on success.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const OK = 0;
}
