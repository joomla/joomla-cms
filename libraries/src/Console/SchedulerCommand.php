<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for checking if there are pending jobs
 *
 * @since  4.0.0
 */
class SchedulerCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected static $defaultName = 'job:run';

	/**
	 * The elapsed time
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $time;

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @since   4.0.0
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$symfonyStyle = new SymfonyStyle($input, $output);

		$symfonyStyle->title('Scheduler');

		// Log the scheduler
		$options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'joomla_cli.php';
		Log::addLogger($options, Log::INFO, array('scheduler'));
		
		// Initialize the time value.
		$this->time = microtime(true);

		if ($input->getOption('jobs'))
		{
			$now  = time();
			$list = $this->listJobs();

			foreach ($list as $job)
			{
				$nextrun    = ', next run will be at the next schedule';
				$taskParams = json_decode($job->params, true);
				$lastrun    = $taskParams['lastrun'];
				$runned     = $taskParams['taskid'];
				$timeout    = $taskParams['cachetimeout'];
				$unit       = $taskParams['unit'];
				$timeout    = ($unit * $timeout);

				if ((abs($now - $lastrun) < $timeout))
				{
					$nextrun = ', next run will be after ' . abs($now - $lastrun - $timeout);
				}

				$symfonyStyle->note('Job: ' . $job->element . ' runned ' . $runned . ' times' . $nextrun);
			}

			$symfonyStyle->success('Scheduler finished in ' . round(microtime(true) - $this->time, 3));

			return 0;
		}

		Log::add(
			'Starting Scheduler', Log::INFO, 'scheduler'
		);

		$symfonyStyle->warning('Starting Scheduler');


		@set_time_limit(0);

		$results = $this->triggerJobs();

		foreach ($results as $run)
		{
			if ($run === null)
			{
				continue;
			}

			$symfonyStyle->note('Executed: ' . $run);
		}

		Log::add(
			'Scheduler tooks:' . round(microtime(true) - $this->time, 3), Log::INFO, 'scheduler'
		);

		$symfonyStyle->success('Scheduler finished in ' . round(microtime(true) - $this->time, 3));

		return 0;
	}

	/**
	 * Trigger the jobs
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function triggerJobs()
	{
		// The job plugin group
		PluginHelper::importPlugin('job');

		// Trigger the ExecuteTask event
		return Factory::getApplication()->triggerEvent('onExecuteScheduledTask', array());
	}

	/**
	 * Configure the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function configure()
	{
		$this->setDescription('Scheduler for job task');
		$this->addOption('jobs', null, InputOption::VALUE_NONE, 'List all jobs');
		$this->setHelp(
<<<EOF
The <info>%command.name%</info> command Scheduler for job task

<info>php %command.full_name%</info>
EOF
		);
	}

	/**
	 * Function to list all the jobs.
	 *
	 * @param   string  $folder  The plugin folder,
	 * @param   string  $type    The extension type.
	 *
	 * @return  object
	 *
	 * @since   4.0.0
	 */
	public static function listJobs(string $folder = 'job', string $type = 'plugin')
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('element'))
			->select($db->quoteName('params'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = :type')
			->where($db->quoteName('folder') . ' = :folder')
			->bind(':type', $type)
			->bind(':folder', $folder);
		$db->setQuery($query);

		try
		{
			$extensions = $db->loadObjectList();
		}
		catch (ExecutionFailureException $e)
		{
			return false;
		}

		return $extensions;
	}
}
