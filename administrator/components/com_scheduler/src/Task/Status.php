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
 * @since 4.1.0
 */
abstract class Status
{
    /**
     * Replacement exit code used when a routine returns an invalid (non-integer) exit code.
     *
     * @since  4.1.0
     */
    public const INVALID_EXIT = -2;

    /**
     * Replacement exit code used when a routine does not return an exit code.
     *
     * @since 4.1.0
     */
    public const NO_EXIT = -1;

    /**
     * Status code used when the routine just starts. This is not meant to be an exit code.
     *
     * @since  4.1.0
     */
    public const RUNNING = 1;

    /**
     * Exit code used on failure to acquire a pseudo-lock.
     *
     * @since  4.1.0
     */
    public const NO_LOCK = 2;

    /**
     * Exit code used on failure to run the task.
     *
     * @since  4.1.0
     */
    public const NO_RUN = 3;

    /**
     * Exit code used on failure to release lock/update the record.
     *
     * @since 4.1.0
     */
    public const NO_RELEASE = 4;

    /**
     * Exit code used when a routine is either "knocked out" by an exception or encounters an exception it cannot handle
     * gracefully.
     * ? Should this be retained ?
     *
     * @since 4.1.0
     */
    public const KNOCKOUT = 5;

    /**
     * Exit code used when a task needs to resume (reschedule it to run a.s.a.p.).
     *
     * Use this for long running tasks, e.g. batch processing of hundreds or thousands of files,
     * sending newsletters with thousands of subscribers etc. These are tasks which might run out of
     * memory and/or hit a time limit when lazy scheduling or web triggering of tasks is being used.
     * Split them into smaller batches which return Status::WILL_RESUME. When the last batch is
     * executed return Status::OK.
     *
     * @since 4.1.0
     */
    public const WILL_RESUME = 123;

    /**
     * Exit code used when a task times out.
     *
     * @since 4.1.0
     */
    public const TIMEOUT = 124;

    /**
     * Exit code when a *task* does not exist.
     *
     * @since 4.1.0
     */
    public const NO_TASK = 125;

    /**
     * Exit code used when a *routine* is missing.
     *
     * @since 4.1.0
     */
    public const NO_ROUTINE = 127;

    /**
     * Exit code on success.
     *
     * @since  4.1.0
     */
    public const OK = 0;
}
