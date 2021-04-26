<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Installer\Installer;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for discovering extensions
 *
 * @since  __DEPLOY_VERSION__
 */
class ExtensionDiscoverInstallCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'extension:discoverinstall';

	/**
	 * Stores the Input Object
	 *
	 * @var    InputInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $cliInput;

	/**
	 * SymfonyStyle Object
	 *
	 * @var    SymfonyStyle
	 * @since  __DEPLOY_VERSION__
	 */
	private $ioStyle;

	/**
	 * Database connector
	 *
	 * @var    DatabaseInterface
	 * @since  4.0.0
	 */
	private $db;

	/**
	 * Exit Code For Discover Failure
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const DISCOVER_FAILED = 1;

	/**
	 * Exit Code For Discover Success
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const DISCOVER_SUCCESSFUL = 0;

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
	 * Configures the IO
	 *
	 * @param   InputInterface   $input   Console Input
	 * @param   OutputInterface  $output  Console Output
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function configureIO(InputInterface $input, OutputInterface $output): void
	{
		$this->cliInput = $input;
		$this->ioStyle = new SymfonyStyle($input, $output);
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function configure(): void
	{
		$this->addOption('eid', null, InputOption::VALUE_REQUIRED, 'The ID of the extension to discover');

		$help = "<info>%command.name%</info> is used to discover extensions
		\nYou can provide the following option to the command:
		\n  --eid: The ID of the extension
		\nUsage:
		\n  <info>php %command.full_name% --eid=<id_of_the_extension></info>";

		$this->setDescription('Discover and install all extensions or a specified extension');
		$this->setHelp($help);
	}

	/**
	 * Used for discovering extensions
	 *
	 * @param   string  $eid  Id of the extension
	 *
	 * @return  boolean
	 *
	 * @throws  \Exception
	 * @since   __DEPLOY_VERSION__
	 */
	public function processDiscover($eid): bool
	{
		$jInstaller = new Installer;
		$result = true;

		if ($eid === -1)
		{
			$db    = $this->db;
			$query = $db->getQuery(true)
				->select($db->quoteName(['extension_id']))
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('state') . ' = -1');
			$db->setQuery($query);
			$eidsToDiscover = $db->loadObjectList();

			foreach ($eidsToDiscover as $eidToDiscover)
			{
				if (!$jInstaller->discover_install($eidToDiscover->extension_id))
				{
					$this->ioStyle->warning('There was a problem installing the extension with ID ' . $eidToDiscover->extension_id . '.');
					$result = false;
				}
			}

			if (empty($eidsToDiscover))
			{
				$this->ioStyle->warning('There is no extension to discover and install.');
			}

			return $result;
		}
		else
		{
			return $jInstaller->discover_install($eid);
		}
	}

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @throws  \Exception
	 * @since   __DEPLOY_VERSION__
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$this->configureIO($input, $output);

		if ($eid = $this->cliInput->getOption('eid'))
		{
			$result = $this->processDiscover($eid);

			if (!$result)
			{
				$this->ioStyle->error('Unable to discover and install the extension with ID ' . $eid);

				return self::DISCOVER_FAILED;
			}

			$this->ioStyle->success('Extension with ID ' . $eid . ' discovered and installed successfully.');

			return self::DISCOVER_SUCCESSFUL;
		}
		else
		{
			$result = $this->processDiscover(-1);

			if (!$result)
			{
				$this->ioStyle->error('Unable to discover and install all extensions');

				return self::DISCOVER_FAILED;
			}

			$this->ioStyle->success('All extensions discovered and installed successfully.');

			return self::DISCOVER_SUCCESSFUL;
		}
	}
}
