<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands;

defined('_JEXEC') || die;

use Akeeba\Backup\Admin\Model\Statistics;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use FOF30\Container\Container;
use Joomla\Console\Command\AbstractCommand;
use Akeeba\Backup\Admin\CliCommands\MixIt\ArgumentUtilities;
use Akeeba\Backup\Admin\CliCommands\MixIt\ConfigureIO;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * akeeba:backup:download
 *
 * Returns a backup archive part for a backup record known to Akeeba Backup
 *
 * @since   7.5.0
 */
class BackupDownload extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:backup:download';

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

		$container = Container::getInstance('com_akeeba', [], 'admin');

		$id      = (int) $this->cliInput->getArgument('id') ?? 0;
		$part    = (int) $this->cliInput->getArgument('part') ?? 0;
		$outFile = $this->cliInput->getOption('file');

		if (!empty($outFile))
		{
			$this->ioStyle->title(sprintf('Retrieving part #%d of Akeeba Backup record #%d', $part, $id));
		}

		if ($id <= 0)
		{
			$this->ioStyle->error('Invalid backup record');

			return 1;
		}


		/** @var Statistics $model */
		$model = $container->factory->model('Statistics')->tmpInstance();
		$model->setState('id', $id);

		$stat         = Platform::getInstance()->get_statistics($id);
		$allFileNames = Factory::getStatistics()->get_all_filenames($stat);

		if (empty($allFileNames))
		{
			$this->ioStyle->error(sprintf("Backup record '%s' does not have any files available for download. Have you already deleted them?", $id));

			return 1;
		}

		if (is_null($allFileNames))
		{
			$this->ioStyle->error(sprintf("Backup record '%s' does not have any files available for download on the server. If they are stored remotely you may need to use the fetch command first.", $id));

			return 2;
		}

		if (($part >= (is_array($allFileNames) || $allFileNames instanceof \Countable ? count($allFileNames) : 0)) || !isset($allFileNames[$part]))
		{
			$this->ioStyle->error(sprintf("There is no part '%s' of backup record '%s'.", $part, $id));

			return 3;
		}

		$fileName = $allFileNames[$part];

		if (!@file_exists($fileName))
		{
			$this->ioStyle->error(sprintf("Can not find part '%s' of backup record '%s' on the server.", $part, $id));

			return 4;
		}

		$basename  = @basename($fileName);
		$fileSize  = @filesize($fileName);
		$extension = strtolower(str_replace(".", "", strrchr($fileName, ".")));

		if (empty($outFile))
		{
			readfile($fileName);

			return 0;
		}

		if (is_dir($outFile))
		{
			$outFile = rtrim($outFile, '//\\') . DIRECTORY_SEPARATOR . $basename;
		}
		else
		{
			$dotPos  = strrpos($outFile, '.');
			$outFile = ($dotPos === false) ? $outFile : (substr($outFile, 0, $dotPos) . '.' . $extension);
		}

		// Read in 1M chunks
		$blocksize = 1048576;
		$handle    = @fopen($fileName, "r");

		if ($handle === false)
		{
			$this->ioStyle->error(sprintf("Cannot open '%s' for reading. Check the permissions / ACLs of the file.", $fileName));

			return 5;
		}

		$fp = @fopen($outFile, 'wb');

		if ($fp === false)
		{
			fclose($handle);

			$this->ioStyle->error(sprintf("Cannot open '%s' for writing. Check whether the folder exists and the permissions / ACLs of both the enclosing folder and the file.", $outFile));

			return 6;
		}

		$progress    = $this->ioStyle->createProgressBar($fileSize);
		$runningSize = 0;
		$progress->display();

		while (!@feof($handle))
		{
			$data        = @fread($handle, $blocksize);
			$readLength  = strlen($data);
			$runningSize += $readLength;

			fwrite($fp, $data);

			$progress->setProgress($readLength);
		}

		$progress->finish();

		$this->ioStyle->newLine(2);

		@fclose($handle);
		@fclose($fp);

		$this->ioStyle->success(sprintf('Downloaded part %d of backup record #%d into file %s', $part, $id, $outFile));

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
		$help = "<info>%command.name%</info> will output or write a file with a backup archive part of a backup record known to Akeeba Backup
		\nUsage: <info>php %command.full_name%</info>";

		$this->addArgument('id', InputArgument::REQUIRED, 'The id of the backup record to retrieve archives for');
		$this->addArgument('part', InputArgument::OPTIONAL, 'The part number of the backup archive to retrieve');
		$this->addOption('file', null, InputOption::VALUE_OPTIONAL, 'File path to write to. Will output to STDOUT if not defined.');
		$this->setDescription('Returns a backup archive part for a backup record known to Akeeba Backup');
		$this->setHelp($help);
	}
}
