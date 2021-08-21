<?php
/**
 * Implements the TaskOptions class used by com_scheduler as the subject arg for the `OnCronOptionsList` event.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_scheduler
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 *
 */

namespace Joomla\Component\Scheduler\Administrator\Tasks;

// Restrict direct access
defined('_JEXEC') or die;

use function defined;

/**
 * The TaskOptions class.
 * Used as the subject argument for the `OnCronOptionsList` event, plugins that support tasks must add them to the object
 * through the addOptions() method.
 *
 * @since  __DEPLOY_VERSION__
 */
class TaskOptions
{
	/**
	 * An array of TaskOptions
	 *
	 * @var TaskOption[]
	 * @since  __DEPLOY_VERSION__
	 */
	public $options = [];


	/**
	 * A plugin can support several task routines
	 * This method is used by a plugin's OnCronOptionsList subscriber to advertise supported routines.
	 *
	 * @param   array  $taskRoutines  An associative array of {@var TaskOption} constructor argument pairs:
	 *                              [ 'taskId' => 'languageConstantPrefix', ... ]
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function addOptions(array $taskRoutines): void
	{
		foreach ($taskRoutines as $routineId => $langConstPrefix)
		{
			$this->options[] = new TaskOption($routineId, $langConstPrefix);
		}
	}

	/**
	 * @param   ?string  $taskType  A unique identifier for a plugin task routine
	 *
	 * @return  ?TaskOption  A matching TaskOption if available, null otherwise
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function findOption(?string $taskType): ?TaskOption
	{
		if ($taskType === null)
		{
			return null;
		}

		foreach ($this->options as $task)
		{
			if ($task->type === $taskType)
			{
				return $task;
			}
		}

		return null;
	}
}
