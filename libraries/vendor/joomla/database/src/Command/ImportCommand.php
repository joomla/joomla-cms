<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Command;

use Joomla\Archive\Archive;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\Exception\UnsupportedAdapterException;
use Joomla\Filesystem\Exception\FilesystemException;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for importing the database
 *
 * @since  __DEPLOY_VERSION__
 */
class ImportCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'database:import';

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
	 * Checks if the zip file contains database export files
	 *
	 * @param   string  $archive  A zip archive to analyze
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	private function checkZipFile(string $archive): void
	{
		if (!extension_loaded('zip'))
		{
			throw new \RuntimeException('The PHP zip extension is not installed or is disabled');
		}

		$zip = zip_open($archive);

		if (!\is_resource($zip))
		{
			throw new \RuntimeException('Unable to open archive');
		}

		while ($file = @zip_read($zip))
		{
			if (strpos(zip_entry_name($file), $this->db->getPrefix()) === false)
			{
				zip_entry_close($file);
				@zip_close($zip);

				throw new \RuntimeException('Unable to find table matching database prefix');
			}

			zip_entry_close($file);
		}

		@zip_close($zip);
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

		$symfonyStyle->title('Importing Database');

		$totalTime = microtime(true);

		// Make sure the database supports imports before we get going
		try
		{
			$importer = $this->db->getImporter()
				->withStructure()
				->asXml();
		}
		catch (UnsupportedAdapterException $e)
		{
			$symfonyStyle->error(sprintf('The "%s" database driver does not support importing data.', $this->db->getName()));

			return 1;
		}

		$folderPath = $input->getOption('folder');
		$tableName  = $input->getOption('table');
		$zipFile    = $input->getOption('zip');

		if ($zipFile)
		{
			if (!class_exists(File::class))
			{
				$symfonyStyle->error('The "joomla/filesystem" Composer package is not installed, cannot process ZIP files.');

				return 1;
			}

			if (!class_exists(Archive::class))
			{
				$symfonyStyle->error('The "joomla/archive" Composer package is not installed, cannot process ZIP files.');

				return 1;
			}

			$zipPath = $folderPath . '/' . $zipFile;

			try
			{
				$this->checkZipFile($zipPath);
			}
			catch (\RuntimeException $e)
			{
				$symfonyStyle->error($e->getMessage());

				return 1;
			}

			$folderPath .= File::stripExt($zipFile);

			try
			{
				Folder::create($folderPath);
			}
			catch (FilesystemException $e)
			{
				$symfonyStyle->error($e->getMessage());

				return 1;
			}

			try
			{
				(new Archive)->extract($zipPath, $folderPath);
			}
			catch (\RuntimeException $e)
			{
				$symfonyStyle->error($e->getMessage());
				Folder::delete($folderPath);

				return 1;
			}
		}

		if ($tableName)
		{
			$tables = [$tableName . '.xml'];
		}
		else
		{
			$tables = Folder::files($folderPath, '\.xml$');
		}

		foreach ($tables as $table)
		{
			$taskTime = microtime(true);
			$percorso = $folderPath . '/' . $table;

			// Check file
			if (!file_exists($percorso))
			{
				$symfonyStyle->error(sprintf('The %s file does not exist.', $table));

				return 1;
			}

			$tableName = str_replace('.xml', '', $table);
			$symfonyStyle->text(sprintf('Importing %1$s from %2$s', $tableName, $table));

			$importer->from(file_get_contents($percorso));

			$symfonyStyle->text(sprintf('Processing the %s table', $tableName));

			try
			{
				$this->db->dropTable($tableName, true);
			}
			catch (ExecutionFailureException $e)
			{
				$symfonyStyle->error(sprintf('Error executing the DROP TABLE statement for %1$s: %2$s', $tableName, $e->getMessage()));

				return 1;
			}

			try
			{
				$importer->mergeStructure();
			}
			catch (\Exception $e)
			{
				$symfonyStyle->error(sprintf('Error merging the structure for %1$s: %2$s', $tableName, $e->getMessage()));

				return 1;
			}

			try
			{
				$importer->importData();
			}
			catch (\Exception $e)
			{
				$symfonyStyle->error(sprintf('Error importing the data for %1$s: %2$s', $tableName, $e->getMessage()));

				return 1;
			}

			$symfonyStyle->text(sprintf('Imported data for %s in %d seconds', $table, round(microtime(true) - $taskTime, 3)));
		}

		if ($zipFile)
		{
			Folder::delete($folderPath);
		}

		$symfonyStyle->success(sprintf('Import completed in %d seconds', round(microtime(true) - $totalTime, 3)));

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
		$this->setDescription('Import the database');
		$this->addOption('folder', null, InputOption::VALUE_OPTIONAL, 'Path to the folder containing files to import', '.');
		$this->addOption('zip', null, InputOption::VALUE_REQUIRED, 'The name of a ZIP file to import');
		$this->addOption('table', null, InputOption::VALUE_REQUIRED, 'The name of the database table to import');
	}
}
