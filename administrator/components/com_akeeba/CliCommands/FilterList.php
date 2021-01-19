<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands;

defined('_JEXEC') || die;

use Akeeba\Backup\Admin\Model\DatabaseFilters;
use Akeeba\Backup\Admin\Model\FileFilters;
use Akeeba\Backup\Admin\Model\IncludeFolders;
use Akeeba\Backup\Admin\Model\MultipleDatabases;
use Akeeba\Backup\Admin\Model\RegExDatabaseFilters;
use Akeeba\Backup\Admin\Model\RegExFileFilters;
use FOF30\Container\Container;
use Joomla\Console\Command\AbstractCommand;
use Akeeba\Backup\Admin\CliCommands\MixIt\ArgumentUtilities;
use Akeeba\Backup\Admin\CliCommands\MixIt\ConfigureIO;
use Akeeba\Backup\Admin\CliCommands\MixIt\FilterRoots;
use Akeeba\Backup\Admin\CliCommands\MixIt\IsPro;
use Akeeba\Backup\Admin\CliCommands\MixIt\PrintFormattedArray;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * akeeba:filter:list
 *
 * Get the filter values known to Akeeba Backup.
 *
 * @since   7.5.0
 */
class FilterList extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray, IsPro, FilterRoots;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:filter:list';

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

		$root   = (string) ($this->cliInput->getOption('root') ?? '');
		$target = (string) ($this->cliInput->getOption('target') ?? 'fs');
		$type   = (string) ($this->cliInput->getOption('type') ?? 'exclude');
		$format = (string) ($this->cliInput->getOption('format') ?? 'table');

		if (!in_array($target, ['fs', 'db']))
		{
			$target = 'fs';
		}

		if (!in_array($type, ['include', 'exclude', 'regex']))
		{
			$type = 'exclude';
		}

		if (!$this->isPro())
		{
			$type = 'exclude';
		}

		$roots = $this->getRoots($target);

		if (empty($root))
		{
			$root = ($target == 'fs') ? '[SITEROOT]' : '[SITEDB]';
		}

		$output = [];

		if (!in_array($root, $roots))
		{
			$this->ioStyle->error(sprintf("Unknown %s root '%s'.", $target, $root));

			return 1;
		}


		if ($format === 'table')
		{
			$this->ioStyle->title('List of Akeeba Backup filters matching your criteria');
		}

		$container = Container::getInstance('com_akeeba', [], 'admin');

		switch ("$target.$type")
		{
			case "fs.exclude":
				/** @var FileFilters $model */
				$model      = $container->factory->model('FileFilters')->tmpInstance();
				$allFilters = $model->get_filters($root);

				foreach ($allFilters as $item)
				{
					$output[] = [
						'filter' => $item['node'],
						'type'   => $item['type'],
					];
				}

				break;

			case "fs.regex":
				/** @var RegExFileFilters $model */
				$model      = $container->factory->model('RegExFileFilters')->tmpInstance();
				$allFilters = $model->get_regex_filters($root);

				foreach ($allFilters as $item)
				{
					$output[] = [
						'filter' => $item['item'],
						'type'   => $item['type'],
					];
				}

				break;

			case "fs.include":
				/** @var IncludeFolders $model */
				$model      = $container->factory->model('IncludeFolders')->tmpInstance();
				$allFilters = $model->get_directories();

				foreach ($allFilters as $uuid => $item)
				{
					$output[] = [
						'filter'               => $uuid,
						'type'                 => 'extradirs',
						'filesystem_directory' => $item[0],
						'virtual_directory'    => $item[1],
					];
				}

				break;

			case "db.exclude":
				/** @var DatabaseFilters $model */
				$model      = $container->factory->model('DatabaseFilters')->tmpInstance();
				$allFilters = $model->get_filters($root);

				foreach ($allFilters as $item)
				{
					$output[] = [
						'filter' => $item['node'],
						'type'   => $item['type'],
					];
				}

				break;

			case "db.regex":
				/** @var RegExDatabaseFilters $model */
				$model      = $container->factory->model('RegExDatabaseFilters')->tmpInstance();
				$allFilters = $model->get_regex_filters($root);

				foreach ($allFilters as $item)
				{
					$output[] = [
						'filter' => $item['item'],
						'type'   => $item['type'],
					];
				}

				break;

			case "db.include":
				/** @var MultipleDatabases $model */
				$model      = $container->factory->model('MultipleDatabases')->tmpInstance();
				$allFilters = $model->get_databases();

				foreach ($allFilters as $uuid => $item)
				{
					$output[] = [
						'filter'   => $uuid,
						'type'     => 'multidb',
						'host'     => $item['host'],
						'driver'   => $item['driver'],
						'port'     => $item['port'],
						'username' => $item['username'],
						'password' => $item['password'],
						'database' => $item['database'],
						'prefix'   => $item['prefix'],
						'dumpFile' => $item['dumpFile'],
					];
				}

				break;
		}

		return $this->printFormattedAndReturn($output, $format);
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
		$help = "<info>%command.name%</info> will list filter values for an Akeeba Backup profile.
		\nUsage: <info>php %command.full_name%</info>";


		$this->addOption('root', null, InputOption::VALUE_OPTIONAL, 'Which filter root to use. Defaults to [SITEROOT] or [SITEDB] depending on the --target option. Ignored for --type=include. Tip: the filesystem and database roots are the "filter" column for --type=include. There are two special roots, [SITEROOT] (the filesystem root of the Joomla site) and [SITEDB] (the main database of the Joomla site).', '');
		$this->addOption('profile', null, InputOption::VALUE_OPTIONAL, 'The backup profile to use. Default: 1.', 1);
		$this->addOption('target', null, InputOption::VALUE_OPTIONAL, 'The target of filters you want to list: fs (files and folders) or db (database)', 'fs');
		$this->addOption('type', null, InputOption::VALUE_OPTIONAL, 'The type of filters you want to list: exclude, include or regex', 'exclude');
		$this->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: table, json, yaml, csv, count.', 'table');

		$this->setDescription('Get the filter values known to Akeeba Backup.');
		$this->setHelp($help);
	}
}
