<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

\defined('JPATH_PLATFORM') or die;

use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console command for list discovered extensions
 *
 * @since  __DEPLOY_VERSION__
 */
class ExtensionDiscoverListCommand extends ExtensionsListCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'extension:discover:list';

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function configure(): void
	{
		$help = "<info>%command.name%</info> is used to list all extensions that could be installed via discoverinstall
		\nUsage:
		\n  <info>php %command.full_name%</info>";

		$this->setDescription('List discovered extensions');
		$this->setHelp($help);
	}

	/**
	 * Filters the extension state
	 *
	 * @param   string  $state  Extension state
	 *
	 * @return array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function filterExtensionsBasedOnState($state): array
	{
		$extensions = [];

		foreach ($this->extensions as $key => $extension)
		{
			if ($extension['state'] === $state)
			{
				$extensions[] = $extension;
			}
		}

		return $extensions;
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
		$extensions = $this->getExtensions();
		$discovered_extensions = $this->filterExtensionsBasedOnState(-1);

		if (empty($discovered_extensions))
		{
			$this->ioStyle->note("Cannot find discovered extensions. Perhaps you need to run extension:discover first?");

			return Command::SUCCESS;
		}

		$discovered_extensions = $this->getExtensionsNameAndId($discovered_extensions);

		$this->ioStyle->title('Discovered extensions.');
		$this->ioStyle->table(['Name', 'Extension ID', 'Version', 'Type', 'Active'], $discovered_extensions);

		return Command::SUCCESS;
	}
}
