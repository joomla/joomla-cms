<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

defined('JPATH_PLATFORM') or die;

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
 * @since  __DEPLOY_VERSION__
 */
class SchedulerCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
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
	 * Stores the Input Object
	 *
	 * @var InputInterface
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private $cliInput;

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$symfonyStyle   = new SymfonyStyle($input, $output);
		$this->cliInput = $input;
		$symfonyStyle->title('Scheduler');

		// Log the scheduler
		$options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'joomla_cli.php';
		Log::addLogger($options, Log::INFO, array('scheduler'));

		// Initialize the time value.
		$this->time = microtime(true);

		Log::add(
			'Scheduler started', Log::INFO, 'scheduler'
		);

		$symfonyStyle->writeln('<error>Scheduler started</error>');
		$symfonyStyle->writeln('');

		@set_time_limit(0);

		$force = $this->cliInput->getOption('force');

		// Run the jobs
		if ($jobname = $this->cliInput->getOption('jobname'))
		{
			$results = $this->triggerJob($jobname, $force);
		}
		else
		{
			$results = $this->triggerJobs($force);
		}

		$text = [];

		foreach ($results as $run)
		{
			switch ((int) $run['status'])
			{
				case 0:
					$msg = '<info>Executed</info> in ' . round($run['duration'], 3);
					break;

				case 1:
					$msg = '<comment>Not scheduled</comment>';
					break;

				default:
					$msg = '<error>In Error</error>';
					break;
			}

			$text[] = 'Job: <fg=red;options=bold>' . $run['job'] . ' </>' . $msg;
		}

		Log::add(
			'Scheduler took: ' . round(microtime(true) - $this->time, 3), Log::INFO, 'scheduler'
		);
		$symfonyStyle->listing($text);
		$symfonyStyle->success('Scheduler finished in ' . round(microtime(true) - $this->time, 3));

		return 0;
	}

	/**
	 * Trigger the jobs
	 *
	 * @param   boolean  $force  The plugin folder,
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function triggerJobs($force = false): array
	{
		// The job plugin group
		PluginHelper::importPlugin('job');
		PluginHelper::importPlugin('actionlog');

		// Trigger the ExecuteTask event
		$results = $this->getApplication()->triggerEvent('onExecuteScheduledTask', ['force' => $force]);

		foreach ($results as $result)
		{
			$this->getApplication()->triggerEvent('onAfterScheduledTask', [$result]);
		}

		return $results;
	}

	/**
	 * Trigger the job
	 *
	 * @param   string   $jobname  The plugin folder,
	 * @param   boolean  $force    The extension type.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function triggerJob($jobname, $force = false): array
	{
		// The job plugin name
		PluginHelper::importPlugin('job', $jobname);
		PluginHelper::importPlugin('actionlog');

		// Trigger the ExecuteTask event
		$results = $this->getApplication()->triggerEvent('onExecuteScheduledTask', ['force' => $force]);

		foreach ($results as $result)
		{
			$this->getApplication()->triggerEvent('onAfterScheduledTask', [$result]);
		}

		return $results;
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
		$this->addOption('jobname', null, InputOption::VALUE_OPTIONAL, 'The name of the job');
		$this->addOption('force', null, InputOption::VALUE_NONE, 'Force the execution');
		$this->setDescription('Scheduler for job task');
		$this->setHelp(
			<<<EOF
The <info>%command.name%</info> command Scheduler for job task

<info>php %command.full_name%</info>
EOF
		);
	}
}
