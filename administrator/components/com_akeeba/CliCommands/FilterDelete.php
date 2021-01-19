<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands;

defined('_JEXEC') || die;

use Akeeba\Engine\Factory;
use Joomla\Console\Command\AbstractCommand;
use Akeeba\Backup\Admin\CliCommands\MixIt\ArgumentUtilities;
use Akeeba\Backup\Admin\CliCommands\MixIt\ConfigureIO;
use Akeeba\Backup\Admin\CliCommands\MixIt\FilterRoots;
use Akeeba\Backup\Admin\CliCommands\MixIt\IsPro;
use Akeeba\Backup\Admin\CliCommands\MixIt\PrintFormattedArray;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * akeeba:filter:delete
 *
 * Delete a filter value known to Akeeba Backup.
 *
 * @since   7.5.0
 */
class FilterDelete extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray, IsPro, FilterRoots;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:filter:delete';

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @since   7.5.0
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$this->configureSymfonyIO($input, $output);

		$profileId = (int) ($this->cliInput->getOption('profile') ?? 1);

		define('AKEEBA_PROFILE', $profileId);

		$filterType = (string) ($this->cliInput->getOption('filterType') ?? 'files');
		$target     = (in_array($filterType, [
			'tables', 'tabledata', 'regextables', 'regextabledata', 'multidb',
		])) ? 'db' : 'fs';
		$root       = (string) ($this->cliInput->getOption('root') ?? (($target == 'fs') ? '[SITEROOT]' : '[SITEDB]'));

		if (!in_array($root, $this->getRoots($target)))
		{
			$this->ioStyle->error(sprintf("Unknown %s root '%s'.", $target, $root));

			return 1;
		}

		$filter = (string) $this->cliInput->getArgument('filter') ?? '';

		$this->ioStyle->title(sprintf(
			'Deleting %s filter “%s” of type %s from profile #%d',
			$target === 'db' ? 'database' : 'filesystem',
			$filter,
			$filterType,
			$profileId
		));

		// Delete the filter
		$filterObject = Factory::getFilterObject($filterType);

		if ((stripos($filterType, 'regex') !== false) && !$this->isPro())
		{
			$this->ioStyle->error(sprintf("Filters of the '%s' type are only available with Akeeba Backup Professional.", $filterType));

			return 2;
		}

		switch ($filterType)
		{
			case 'extradirs':
			case 'multidb':
				if (!$this->isPro())
				{
					$this->ioStyle->error(sprintf("Filters of the '%s' type are only available with Akeeba Backup Professional.", $filterType));

					return 2;
				}

				$success = $filterObject->remove($filter);
				break;

			default:
				$success = $filterObject->remove($root, $filter);
				break;
		}

		if (!$success)
		{
			$this->ioStyle->error(sprintf("Could not delete filter '%s' of type '%s'.", $filter, $filterType));

			return 3;
		}

		Factory::getFilters()->save();

		$this->ioStyle->success(sprintf("Deleted filter '%s' of type '%s'.", $filter, $filterType));

		return 0;
	}

	/**
	 * Configure the command.
	 *
	 * @return  void
	 *
	 * @since   7.5.0
	 */
	protected function configure(): void
	{
		$help = "<info>%command.name%</info> will delete a filter value known to Akeeba Backup.
		\nUsage: <info>php %command.full_name%</info>";

		$this->addArgument('filter', InputArgument::REQUIRED, 'The filter name to delete');
		$this->addOption('root', null, InputOption::VALUE_OPTIONAL, 'Which filter root to use. Defaults to [SITEROOT] or [SITEDB] depending on the fitler type.', '');
		$this->addOption('filterType', null, InputOption::VALUE_REQUIRED, 'The type of filter you want to delete: files, directories, skipdirs, skipfiles, regexfiles, regexdirectories, regexskipdirs, regexskipfiles, tables, tabledata, regextables, regextabledata, extradirs, multidb', 'files');
		$this->addOption('profile', null, InputOption::VALUE_OPTIONAL, 'The backup profile to use. Default: 1.', 1);


		$this->setDescription('Delete a filter value known to Akeeba Backup.');
		$this->setHelp($help);
	}
}
