<?php
/**
 * Declares the ExecuteTaskEvent Event.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_scheduler
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Event;

// Restrict direct access
defined('_JEXEC') or die;

use BadMethodCallException;
use Joomla\CMS\Event\AbstractEvent;

/**
 * Event class for onExecuteTask event.
 *
 * @since  __DEPLOY_VERSION__
 */
class ExecuteTaskEvent extends AbstractEvent
{
	/**
	 * Constructor.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  BadMethodCallException
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($name, array $arguments = array())
	{
		$arguments['resultSnapshot'] = null;

		if (!isset($arguments['jobId']))
		{
			throw new BadMethodCallException("No jobId given for $name event");
		}

		parent::__construct($name, $arguments);
	}

	/**
	 * Sets the job result snapshot and stops event propagation.
	 *
	 * @param   array  $snapshot  The job snapshot.
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setResult(array $snapshot = []): void
	{
		$this->arguments['resultSnapshot'] = $snapshot;

		if ($snapshot)
		{
			$this->stopPropagation();
		}
	}

	/**
	 * Returns the jobId of the job.
	 *
	 * @return  string  The jobId of the job
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getJobId(): string
	{
		return $this->arguments['jobId'];
	}

	/**
	 * Returns the snapshot of the triggered job if available, else an empty array
	 *
	 * @return array   The job snapshot if available, else null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getResultSnapshot(): array
	{
		return $this->arguments['resultSnapshot'] ?? [];
	}
}
