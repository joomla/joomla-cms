<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Command;

use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseInterface;
use Joomla\Session\Exception\CreateSessionTableException;
use Joomla\Session\Exception\UnsupportedDatabaseDriverException;
use Joomla\Session\Handler\DatabaseHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command used to create the session database table.
 *
 * @since  __DEPLOY_VERSION__
 */
class CreateSessionTableCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'session:create-table';

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
	 * Configure the command.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function configure(): void
	{
		$this->setDescription('Creates the session database table if not already present');
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
		$io = new SymfonyStyle($input, $output);

		$io->title('Create Session Table');

		// Check if the table exists
		if (\in_array($this->db->replacePrefix('#__session'), $this->db->getTableList()))
		{
			$io->success('The session table already exists.');

			return 0;
		}

		try
		{
			(new DatabaseHandler($this->db))->createDatabaseTable();
		}
		catch (UnsupportedDatabaseDriverException $exception)
		{
			$io->error($exception->getMessage());

			return 1;
		}
		catch (CreateSessionTableException $exception)
		{
			$io->error(\sprintf('The session table could not be created: %s', $exception->getMessage()));

			return 1;
		}

		$io->success('The session table has been created.');

		return 0;
	}
}
