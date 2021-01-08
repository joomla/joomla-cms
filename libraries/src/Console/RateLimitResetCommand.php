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
use Joomla\Registry\Registry;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for reset webservices rate limit quota
 *
 * @since  __DEPLOY_VERSION__
 */
class RateLimitResetCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'webservices:reset';

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

		$symfonyStyle->title('Webservices Reset Rate Limit');

		// Initialize the time value.
		$this->time = microtime(true);

		$now         = time();
		$webservices = $this->listWebservices();
		$text        = [];

		foreach ($webservices as $webservice)
		{
			$nextrun    = ' the next reset will be as soon as possible';
			$taskParams = json_decode($webservice->params, true);

			if ((!array_key_exists('public', $taskParams)) || ($taskParams['public'] === false))
			{
				continue;
			}

			$lastrun    = $taskParams['lastrun'];
			$lastcount  = $taskParams['taskid'];
			$timeout    = $taskParams['timeout'];
			$unit       = $taskParams['unit'];
			$timeout    = ($unit * $timeout);

			if ((abs($now - $lastrun) < $timeout))
			{
				$nextrun = ' the next reset is scheduled ' . $this->convert(abs($now - $lastrun - $timeout));
			}
			else
			{
				$nextrun = ' rate limit is ' . $taskParams['limit'];

				if ($taskParams['taskid'] > $taskParams['limit'])
				{
					$this->resetLimit($webservice);
					$nextrun = ' rate limit has been resetted ';
				}
			}

			$text[] = 'Webservice: <fg=red;options=bold>' . $webservice->element . '</> has run ' . $lastcount . ' times' . $nextrun;
		}

		$symfonyStyle->listing($text);
		$symfonyStyle->success('RateLimitResetCommand finished in ' . round(microtime(true) - $this->time, 3));

		return 0;
	}

	/**
	 * Function to list all the webservices.
	 *
	 * @param   string  $folder  The plugin folder,
	 * @param   string  $type    The extension type.
	 *
	 * @return  object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function listWebservices(string $folder = 'webservices', string $type = 'plugin')
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('extension_id'))
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
		$this->setDescription('Webservices Reset Rate Limit');
		$this->setHelp(
			<<<EOF
The <info>%command.name%</info> command List schedule time for job task

<info>php %command.full_name%</info>
EOF
		);
	}

	/**
	 * Function to convert seconds to human readable
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

	/**
	 * Function to reset endpoint rate limit
	 *
	 * @param   string  $webservice  The webservice
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function resetLimit($webservice): void
	{
		// Update last run and taskid
		$taskParams = json_decode($webservice->params, true);

		$registry = new Registry($taskParams);
		$registry->set('taskid', 0);
		$registry->set('lastrun', time());
		$jsonparam = $registry->toString('JSON');

		$query  = $this->db->getQuery(true);
		$query->update($this->db->quoteName('#__extensions'))
			->set($this->db->quoteName('params') . ' = :params')
			->where($this->db->quoteName('extension_id') . ' = :eid')
			->bind(':params', $jsonparam)
			->bind(':eid', $webservice->extension_id);

		try
		{
			// Update the plugin parameters
			$result = $this->db->setQuery($query)->execute();
		}
		catch (RuntimeException $e)
		{
			// If we failed to execute
			return;
		}
	}
}
