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
use Symfony\Component\Console\Command\Command;
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
	protected static $defaultName = 'extension:discover:install';

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
	 * 
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
	 * Configures the IO
	 *
	 * @param   InputInterface   $input   Console Input
	 * @param   OutputInterface  $output  Console Output
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 *
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
		\n  If you do not provide a ID all discovered extensions are installed.
		\nUsage:
		\n  <info>php %command.full_name% --eid=<id_of_the_extension></info>";

		$this->setDescription('Install discovered extensions');
		$this->setHelp($help);
	}

	/**
	 * Used for discovering extensions
	 *
	 * @param   string  $eid  Id of the extension
	 *
	 * @return  integer  The count of installed extensions
	 *
	 * @throws  \Exception
	 * @since   __DEPLOY_VERSION__
	 */
	public function processDiscover($eid): int
	{
		$jInstaller = new Installer;
		$count = 0;

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
					return -1;
				}

				$count++;
			}

			if (empty($eidsToDiscover))
			{
				return 0;
			}
		}
		else
		{
			if ($jInstaller->discover_install($eid))
			{
				return 1;
			}
			else
			{
				return -1;
			}
		}

		return $count;
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
		$this->configureIO($input, $output);

		if ($eid = $this->cliInput->getOption('eid'))
		{
			$result = $this->processDiscover($eid);

			if ($result === -1)
			{
				$this->ioStyle->error('Unable to install the extension with ID ' . $eid);

				return Command::FAILURE;
			}

			$this->ioStyle->success('Extension with ID ' . $eid . ' installed successfully.');

			return Command::SUCCESS;
		}
		else
		{
			$result = $this->processDiscover(-1);

			if ($result === -1)
			{
				$this->ioStyle->error('Unable to install discovered extensions.');

				return Command::FAILURE;
			}
			elseif ($result === 0)
			{
				$this->ioStyle->note('There are no discovered extensions for install. Perhaps you need to run extension:discover first?');

				return Command::SUCCESS;
			}

			elseif ($result === 1)
			{
				$this->ioStyle->note($result . ' discovered extension has been installed.');

				return Command::SUCCESS;
			}
			else
			{
				$this->ioStyle->note($result . ' discovered extensions have been installed.');

				return Command::SUCCESS;
			}
		}
	}
}
