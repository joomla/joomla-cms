<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Controller\Mixin;

// Protect from unauthorized access
defined('_JEXEC') || die();

/**
 * Force a Controller to allow access to specific tasks only, no matter which tasks are already defined in this
 * Controller.
 */
trait PredefinedTaskList
{
	/**
	 * A list of predefined tasks. Trying to access any other task will result in the first task of this list being
	 * executed instead.
	 *
	 * @var array
	 */
	protected $predefinedTaskList = array();

	/**
	 * Overrides the execute method to implement the predefined task list feature
	 *
	 * @param   string  $task  The task to execute
	 *
	 * @return  mixed  The controller task result
	 */
	public function execute($task)
	{
		if (!in_array($task, $this->predefinedTaskList))
		{
			$task = reset($this->predefinedTaskList);
		}

		return parent::execute($task);
	}

	/**
	 * Sets the predefined task list and registers the first task in the list as the Controller's default task
	 *
	 * @param   array  $taskList  The task list to register
	 */
	public function setPredefinedTaskList(array $taskList)
	{
		// First, unregister all known tasks which are not in the taskList
		$allTasks = $this->getTasks();

		foreach ($allTasks as $task)
		{
			if (in_array($task, $taskList))
			{
				continue;
			}

			$this->unregisterTask($task);
		}

		// Set the predefined task list
		$this->predefinedTaskList = $taskList;

		// Set the default task
		$this->registerDefaultTask(reset($this->predefinedTaskList));

	}
}
