<?php
/**
 * Joomla! Content Management System.
 *
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

// Restrict direct access
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\Component\Scheduler\Administrator\Scheduler\Scheduler;
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
	 * State to publish
	 *
	 * @since __DEPLOY_VERSION__
	 */
	const STATE_ENABLE = 1;

	/**
	 * State to unpublish
	 *
	 * @since __DEPLOY_VERSION__
	 */
	const STATE_DISABLE = 0;

	/**
	 * State to trash
	 *
	 * @since __DEPLOY_VERSION__
	 */
	const STATE_TRASH = -2;

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

		while (!$id)
		{
			$id = (int) $this->ioStyle->ask('Please speficy the ID of the task');
		}

		$state = (string) $input->getOption('state');

		if (!is_numeric($state))
		{
			$state = ArrayHelper::getValue(['enable' => self::STATE_ENABLE, 'disable' => self::STATE_DISABLE,
				'trash' => self::STATE_TRASH], $state
			);
		}

		while (!strlen($state) || !in_array($state, [self::STATE_ENABLE, self::STATE_DISABLE, self::STATE_TRASH]))
		{
			$state = (string) $this->ioStyle->ask('Should the state be "enable" (1), "disable" (0) or "trash" (-2)');

			if (!is_numeric($state))
			{
				$state = ArrayHelper::getValue(['enable' => self::STATE_ENABLE, 'unpublish' => self::STATE_DISABLE,
					'trash' => self::STATE_TRASH], $state
				);
			}
		}

		$app = $this->getApplication();
		$taskModel = $app->bootComponent('com_scheduler')->getMVCFactory($app)->createModel('Task', 'Administrator');

		$task = $taskModel->getItem($id);

		if (empty($task->id))
		{
			$this->ioStyle->error('Task ID: ' . $id . ' does not exist!');

			return 1;
		}

		if ($taskModel->isCheckedOut($task))
		{
			$this->ioStyle->error("Task ID '${id}' is checked out!");

			return 2;
		}

		$table = $taskModel->getTable();

		$action = array_search($state, ['enable' => self::STATE_ENABLE, 'disable' => self::STATE_DISABLE, 'trash' => self::STATE_TRASH]);

		if (!$table->publish($id, $state))
		{
			$this->ioStyle->error('Can\'t ' . $action . ' Task ID ' . $id);

			return 1;
		}

		$this->ioStyle->success('Task ID ' . $id . ' ' . $action . 'ed');

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
	private function configureIO(InputInterface $input, OutputInterface $output)
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
		$this->addOption('id', 'i', InputOption::VALUE_REQUIRED, 'The id of the task to change');
		$this->addOption('state', 's', InputOption::VALUE_REQUIRED, 'Set the new state of the task, can be 1/enable, 0/disable, -2/trash.');

		$help = "<info>%command.name%</info> changes the state of a task.
		\nUsage: <info>php %command.full_name%</info>";

		$this->setDescription('Enable/Disable/Trash a task');
		$this->setHelp($help);
	}
}
