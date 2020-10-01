<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for list jobs queue
 *
 * @since  __DEPLOY_VERSION__
 */
class JobListCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
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
	 * Instantiate the command.
	 *
	 * @param   DatabaseInterface  $db  Database connector
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(DatabaseInterface $db)
	{
		$this->db = $db;
		parent::__construct();
	}

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
		$symfonyStyle = new SymfonyStyle($input, $output);

		$symfonyStyle->title('Job list');

		// Initialize the time value.
		$this->time = microtime(true);

		$now  = time();
		$list = $this->listJobs();
		$text = [];

		foreach ($list as $job)
		{
			$nextrun    = ' the next run will be as soon as possible';
			$taskParams = json_decode($job->params, true);
			$lastrun    = $taskParams['lastrun'];
			$lastcount  = $taskParams['taskid'];
			$timeout    = $taskParams['timeout'];
			$unit       = $taskParams['unit'];
			$timeout    = ($unit * $timeout);

			if ((abs($now - $lastrun) < $timeout))
			{
				$nextrun = ' the next run is scheduled ' . $this->convert(abs($now - $lastrun - $timeout));
			}

			$text[] = 'Job: <fg=red;options=bold>' . $job->element . '</> has run ' . $lastcount . ' times' . $nextrun;
		}

		$symfonyStyle->listing($text);
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function listJobs(string $folder = 'job', string $type = 'plugin')
	{
		$query1 = $this->db->getQuery(true);

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('element'))
			->select($this->db->quoteName('params'))
			->from($this->db->quoteName('#__extensions'))
			->where($this->db->quoteName('type') . ' = :type')
			->where($this->db->quoteName('folder') . ' = :folder')
			->where($this->db->quoteName('enabled') . ' = 1')
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
	 * @since   __DEPLOY_VERSION__
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

	/**
	 * Function to cpnvert seconds to human readable
	 *
	 * @param   integer  $seconds  The seconds,
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function convert($seconds): string
	{
		$dt1 = new \DateTime("@0");
		$dt2 = new \DateTime("@$seconds");

		return $dt1->diff($dt2)->format('%a days, %h hours, %i minutes %s seconds');
	}
}
