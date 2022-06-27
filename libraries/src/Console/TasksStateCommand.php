<?php
/**
 * Joomla! Content Management System.
 *
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

// Restrict direct access
\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Factory;
use Joomla\Component\Scheduler\Administrator\Model\TaskModel;
use Joomla\Component\Scheduler\Administrator\Table\TaskTable;
use Joomla\Component\Scheduler\Administrator\Task\Task;
use Joomla\Console\Application;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Utilities\ArrayHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to change the state of tasks.
 *
 * @since 4.1.0
 */
class TasksStateCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  4.1.0
	 */
	protected static $defaultName = 'scheduler:state';

	/**
	 * The console application object
	 *
	 * @var Application
	 *
	 * @since 4.1.0
	 */
	protected $application;

	/**
	 * @var SymfonyStyle
	 *
	 * @since  4.1.0
	 */
	private $ioStyle;

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @since   4.1.0
	 * @throws \Exception
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		Factory::getApplication()->getLanguage()->load('joomla', JPATH_ADMINISTRATOR);

		$this->configureIO($input, $output);

		$id = (string) $input->getOption('id');
		$state = (string) $input->getOption('state');

		// Try to validate and process ID, if passed
		if (\strlen($id))
		{
			if (!Task::isValidId($id))
			{
				$this->ioStyle->error('Invalid id passed!');

				return 2;
			}

			$id = (is_numeric($id)) ? ($id + 0) : $id;
		}

		// Try to validate and process state, if passed
		if (\strlen($state))
		{
			// If we get the logical state, we try to get the enumeration (but as a string)
			if (!is_numeric($state))
			{
				$state = (string) ArrayHelper::arraySearch($state, Task::STATE_MAP);
			}

			if (!\strlen($state) || !Task::isValidState($state))
			{
				$this->ioStyle->error('Invalid state passed!');

				return 2;
			}
		}

		// If we didn't get ID as a flag, ask for it interactively
		while (!Task::isValidId($id))
		{
			$id = $this->ioStyle->ask('Please specify the ID of the task');
		}

		// If we didn't get state as a flag, ask for it interactively
		while ($state === false || !Task::isValidState($state))
		{
			$state = (string) $this->ioStyle->ask('Should the state be "enable" (1), "disable" (0) or "trash" (-2)');

			// Ensure we have the enumerated value (still as a string)
			$state = (Task::isValidState($state)) ? $state : ArrayHelper::arraySearch($state, Task::STATE_MAP);
		}

		// Finally, the enumerated state and id in their pure form
		$state = (int) $state;
		$id = (int) $id;

		/** @var ConsoleApplication $app */
		$app = $this->getApplication();

		/** @var TaskModel $taskModel */
		$taskModel = $app->bootComponent('com_scheduler')->getMVCFactory()->createModel('Task', 'Administrator');

		$task = $taskModel->getItem($id);

		// We couldn't fetch that task :(
		if (empty($task->id))
		{
			$this->ioStyle->error("Task ID '${id}' does not exist!");

			return 1;
		}

		// If the item is checked-out we need a check in (currently not possible through the CLI)
		if ($taskModel->isCheckedOut($task))
		{
			$this->ioStyle->error("Task ID '${id}' is checked out!");

			return 1;
		}

		/** @var TaskTable $table */
		$table = $taskModel->getTable();

		$action = Task::STATE_MAP[$state];

		if (!$table->publish($id, $state))
		{
			$this->ioStyle->error("Can't ${action} Task ID '${id}'");

			return 3;
		}

		$this->ioStyle->success("Task ID ${id} ${action}.");

		return 0;
	}

	/**
	 * Configure the IO.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  void
	 *
	 * @since  4.1.0
	 */
	private function configureIO(InputInterface $input, OutputInterface $output): void
	{
		$this->ioStyle = new SymfonyStyle($input, $output);
	}

	/**
	 * Configure the command.
	 *
	 * @return  void
	 *
	 * @since   4.1.0
	 */
	protected function configure(): void
	{
		$this->addOption('id', 'i', InputOption::VALUE_REQUIRED, 'The id of the task to change state.');
		$this->addOption('state', 's', InputOption::VALUE_REQUIRED, 'The new state of the task, can be 1/enable, 0/disable, or -2/trash.');

		$help = "<info>%command.name%</info> changes the state of a task.
		\nUsage: <info>php %command.full_name%</info>";

		$this->setDescription('Enable, disable or trash a scheduled task');
		$this->setHelp($help);
	}
}
