<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Command;

use Joomla\Archive\Archive;
use Joomla\Archive\Zip;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\Exception\UnsupportedAdapterException;
use Joomla\Filesystem\File;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputOption;

/**
 * Console command for exporting the database
 *
 * @since  __DEPLOY_VERSION__
 */
class ExportCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'database:export';

	/**
	 * Database connector
	 *
	 * @var    DatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	private $db;

	/**
	 * Instantiate the command.
	 *
	 * @param   DatabaseDriver  $db  Database connector
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(DatabaseDriver $db)
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

		$symfonyStyle->title('Exporting Database');

		$totalTime = microtime(true);

		if (!class_exists(File::class))
		{
			$symfonyStyle->error('The "joomla/filesystem" Composer package is not installed, cannot create an export.');

			return 1;
		}

		// Make sure the database supports exports before we get going
		try
		{
			$exporter = $this->db->getExporter()
				->withStructure();
		}
		catch (UnsupportedAdapterException $e)
		{
			$symfonyStyle->error(sprintf('The "%s" database driver does not support exporting data.', $this->db->getName()));

			return 1;
		}

		$folderPath = $input->getOption('folder');
		$tableName  = $input->getOption('table');
		$zip        = $input->getOption('zip');

		$zipFile = $folderPath . '/data_exported_' . date("Y-m-d\TH-i-s") . '.zip';
		$tables = $this->db->getTableList();
		$prefix = $this->db->getPrefix();

		if ($tableName)
		{
			if (!\in_array($tableName, $tables))
			{
				$symfonyStyle->error(sprintf('The %s table does not exist in the database.', $tableName));

				return 1;
			}

			$tables = [$tableName];
		}

		if ($zip)
		{
			if (!class_exists(Archive::class))
			{
				$symfonyStyle->error('The "joomla/archive" Composer package is not installed, cannot create ZIP files.');

				return 1;
			}

			/** @var Zip $zipArchive */
			$zipArchive = (new Archive)->getAdapter('zip');
		}

		foreach ($tables as $table)
		{
			// If an empty prefix is in use then we will dump all tables, otherwise the prefix must match
			if (strlen($prefix) === 0 || strpos(substr($table, 0, strlen($prefix)), $prefix) !== false)
			{
				$taskTime = microtime(true);
				$filename = $folderPath . '/' . $table . '.xml';

				$symfonyStyle->text(sprintf('Processing the %s table', $table));

				$data = (string) $exporter->from($table)->withData(true);

				if (file_exists($filename))
				{
					File::delete($filename);
				}

				File::write($filename, $data);

				if ($zip)
				{
					$zipFilesArray[] = ['name' => $table . '.xml', 'data' => $data];
					$zipArchive->create($zipFile, $zipFilesArray);
					File::delete($filename);
				}

				$symfonyStyle->text(sprintf('Exported data for %s in %d seconds', $table, round(microtime(true) - $taskTime, 3)));
			}
		}

		$symfonyStyle->success(sprintf('Export completed in %d seconds', round(microtime(true) - $totalTime, 3)));

		return 0;
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
		$this->setDescription('Export the database');
		$this->addOption('folder', null, InputOption::VALUE_OPTIONAL, 'Path to write the export files to', '.');
		$this->addOption('table', null, InputOption::VALUE_REQUIRED, 'The name of the database table to export');
		$this->addOption('zip', null, InputOption::VALUE_NONE, 'Flag indicating the export will be saved to a ZIP archive');
	}
}
