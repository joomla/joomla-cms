<?php
/**
 * @package         Joomla.
 * @subpackage      sub
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
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
	 * Replacement exit code for task with no exit code
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public const NO_EXIT = -1;

	/**
	 * Exit Code For no time to run
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const NO_TIME = 1;

	/**
	 * Exit code on failure to acquire a pseudo-lock.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const NO_LOCK = 2;

	/**
	 * Exit code on failure to run the task.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const NO_RUN = 3;

	/**
	 * Exit code on failure to release lock/update the record.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public const NO_RELEASE = 4;

	/**
	 * Exit code for task knockout.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public const KO_RUN = 5;

	/**
	 * Exit code for task timeout.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public const TIMEOUT = 124;

	/**
	 * Exit code on success.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const OK = 0;
}
