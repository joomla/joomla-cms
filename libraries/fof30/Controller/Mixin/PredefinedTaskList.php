<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Controller\Mixin;

defined('_JEXEC') || die;

use FOF30\Controller\Controller;

/**
 * Force a Controller to allow access to specific tasks only, no matter which tasks are already defined in this
 * Controller.
 *
 * Include this Trait and then in your constructor do this:
 * $this->setPredefinedTaskList(['atask', 'anothertask', 'something']);
 *
 * WARNING: If you override execute() you will need to copy the logic from this trait's execute() method.
 */
trait PredefinedTaskList
{

	/**
	 * A list of predefined tasks. Trying to access any other task will result in the first task of this list being
	 * executed instead.
	 *
	 * @var array
	 */
	protected $predefinedTaskList = [];

	/**
	 * Overrides the execute method to implement the predefined task list feature
	 *
	 * @param   string  $task  The task to execute
	 *
	 * @return  mixed   The controller task result
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
	 *
	 * @return  void
	 */
	public function setPredefinedTaskList(array $taskList)
	{
		/** @var Controller $this */

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
