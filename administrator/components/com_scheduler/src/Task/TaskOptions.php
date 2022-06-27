<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Task;

// Restrict direct access
\defined('_JEXEC') or die;

/**
 * The TaskOptions class.
 * Used as the subject argument for the `onTaskOptionsList` event, plugins that support tasks must add them to the
 * object through the addOptions() method.
 *
 * @since  4.1.0
 */
class TaskOptions
{
	/**
	 * An array of TaskOptions
	 *
	 * @var TaskOption[]
	 * @since  4.1.0
	 */
	public $options = [];

	/**
	 * A plugin can support several task routines
	 * This method is used by a plugin's onTaskOptionsList subscriber to advertise supported routines.
	 *
	 * @param   array  $taskRoutines  An associative array of {@var TaskOption} constructor argument pairs:
	 *                                [ 'routineId' => 'languageConstantPrefix', ... ]
	 *
	 * @return void
	 *
	 * @since  4.1.0
	 */
	public function addOptions(array $taskRoutines): void
	{
		foreach ($taskRoutines as $routineId => $langConstPrefix)
		{
			$this->options[] = new TaskOption($routineId, $langConstPrefix);
		}
	}

	/**
	 * @param   ?string  $routineId  A unique identifier for a plugin task routine
	 *
	 * @return  ?TaskOption  A matching TaskOption if available, null otherwise
	 *
	 * @since  4.1.0
	 */
	public function findOption(?string $routineId): ?TaskOption
	{
		if ($routineId === null)
		{
			return null;
		}

		foreach ($this->options as $option)
		{
			if ($option->id === $routineId)
			{
				return $option;
			}
		}

		return null;
	}
}
