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
use Joomla\Component\Jobs\Administrator\Table\TaskTable;
use Joomla\Component\Scheduler\Administrator\Model\TaskModel;
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
 * @since __DEPLOY_VERSION__
 */
class TasksStateCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'scheduler:state';

	/**
	 * The console application object
	 *
	 * @var Application
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $application;

	/**
	 * @var SymfonyStyle
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $ioStyle;

	/**
	 * State to enable.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	const STATE_ENABLE = 1;

	/**
	 * State to disable.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	const STATE_DISABLE = 0;

	/**
	 * State to trash.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	const STATE_TRASH = -2;

	/**
	 * Map state enumerations to language verbs.
	 *
	 * @since __DEPLOY__VERSION__
	 */
	const STATE_MAP = [
		self::STATE_TRASH   => 'trash',
		self::STATE_DISABLE => 'disable',
		self::STATE_ENABLE  => 'enable',
	];

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws \Exception
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		Factory::getApplication()->getLanguage()->load('joomla', JPATH_ADMINISTRATOR);

		$this->configureIO($input, $output);

		$id = (int) $input->getOption('id');
		$state = (string) $input->getOption('state');

		while (!$id)
		{
			$id = (int) $this->ioStyle->ask('Please specify the ID of the task');
		}

		if (\strlen($state) && !is_numeric($state))
		{
			// We try to get the enumerated state here (but as a string)
			$state = (string) ArrayHelper::arraySearch($state, self::STATE_MAP);

			if (!\strlen($state))
			{
				$this->ioStyle->error('Invalid state passed!');

				return 2;
			}
		}

		// If we didn't get state as a flag, ask for it interactively
		while ($state === false || !$this->isValidState($state))
		{
			$state = (string) $this->ioStyle->ask('Should the state be "enable" (1), "disable" (0) or "trash" (-2)');

			// Ensure we have the enumerated value (still as a string)
			$state = ($this->isValidState($state)) ?: ArrayHelper::arraySearch($state, self::STATE_MAP);
		}

		// Finally, the enumerated state in its pure form
		$state = (int) $state;

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

		$action = self::STATE_MAP[$state];

		if (!$table->publish($id, $state))
		{
			$this->ioStyle->error("Can't ${action} Task ID '${id}'");

			return 3;
		}

		$actionAdjective = $action . ($action[-1] === 'e' ? 'd' : 'ed');
		$this->ioStyle->success("Task ID ${id} ${actionAdjective}.");

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
	 * @since  __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
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

	/**
	 * Private method to determine whether an enumerated task state (as a string) is valid.
	 *
	 * @param   string  $state  The task state (enumerated).
	 *
	 * @return boolean
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function isValidState(string $state): bool
	{
		if (!is_numeric($state))
		{
			return false;
		}

		// Takes care of interpreting as float/int
		$state = $state + 0;

		return ArrayHelper::getValue(self::STATE_MAP, $state) !== null;
	}
}
