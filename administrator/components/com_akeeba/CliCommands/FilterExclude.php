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
 * akeeba:filter:exclude
 *
 * Set an exclusion filter to Akeeba Backup.
 *
 * @since   7.5.0
 */
class FilterExclude extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray, IsPro, FilterRoots;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:filter:exclude';

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
			'Adding %s filter “%s” of type %s to profile #%d',
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

			return 1;
		}

		$success = $filterObject->set($root, $filter);

		if (!$success)
		{
			$this->ioStyle->error(sprintf("Could not add filter '%s' of type '%s'.", $filter, $filterType));

			return 2;
		}

		Factory::getFilters()->save();

		$this->ioStyle->success(sprintf("Added filter '%s' of type '%s'.", $filter, $filterType));

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
		$help = "<info>%command.name%</info> will set a file, folder or table exclusion filter to Akeeba Backup.
		\nUsage: <info>php %command.full_name%</info>";

		$this->addArgument('filter', InputArgument::REQUIRED, 'The filter target to add. This is the full path to a file/directory, a table name or a regular expression, depending on the filter type.');
		$this->addOption('root', null, InputOption::VALUE_OPTIONAL, 'Which filter root to use. Defaults to [SITEROOT] or [SITEDB] depending on the filter type.', '');
		$this->addOption('filterType', null, InputOption::VALUE_REQUIRED, 'The type of filter you want to add: files, directories, skipdirs, skipfiles, regexfiles, regexdirectories, regexskipdirs, regexskipfiles, tables, tabledata, regextables, regextabledata', 'files');
		$this->addOption('profile', null, InputOption::VALUE_OPTIONAL, 'The backup profile to use. Default: 1.', 1);

		$this->setDescription('Set an exclusion filter to Akeeba Backup.');
		$this->setHelp($help);
	}
}
