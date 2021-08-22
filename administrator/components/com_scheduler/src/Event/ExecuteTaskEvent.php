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

		if (!isset($arguments['taskId']))
		{
			throw new BadMethodCallException("No taskId given for $name event");
		}

		parent::__construct($name, $arguments);
	}

	/**
	 * Sets the task result snapshot and stops event propagation.
	 *
	 * @param   array  $snapshot  The task snapshot.
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
	 * @return  string  The task's taskId.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getTaskId(): string
	{
		return $this->arguments['taskId'];
	}

	/**
	 * Returns the snapshot of the triggered task if available, else an empty array
	 *
	 * @return array   The task snapshot if available, else null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getResultSnapshot(): array
	{
		return $this->arguments['resultSnapshot'] ?? [];
	}
}
