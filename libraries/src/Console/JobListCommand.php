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
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for list jobs queue
 *
 * @since  4.0.0
 */
class JobListCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected static $defaultName = 'job:list';

	/**
	 * The elapsed time
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $time;

	/**
	 * Database connector
	 *
	 * @var    DatabaseInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $db;

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

		$symfonyStyle->title('Job list');

		// Initialize the time value.
		$this->time = microtime(true);	

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

		$symfonyStyle->success('Job list finished in ' . round(microtime(true) - $this->time, 3));

		return 0;
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
	public function listJobs(string $folder = 'job', string $type = 'plugin')
	{
		$this->db = Factory::getDbo();
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('element'))
			->select($this->db->quoteName('params'))
			->from($this->db->quoteName('#__extensions'))
			->where($this->db->quoteName('type') . ' = :type')
			->where($this->db->quoteName('folder') . ' = :folder')
			->bind(':type', $type)
			->bind(':folder', $folder);
		$this->db->setQuery($query);

		return $this->db->loadObjectList();
	}

	/**
	 * Configure the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function configure(): void
	{
		$this->setDescription('List all jobs');
		$this->setHelp(
<<<EOF
The <info>%command.name%</info> command List schedule time for job task

<info>php %command.full_name%</info>
EOF
		);
	}
}
