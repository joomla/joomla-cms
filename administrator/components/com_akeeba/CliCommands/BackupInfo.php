<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands;

defined('_JEXEC') || die;

use Akeeba\Engine\Platform;
use Joomla\Console\Command\AbstractCommand;
use Akeeba\Backup\Admin\CliCommands\MixIt\ArgumentUtilities;
use Akeeba\Backup\Admin\CliCommands\MixIt\ConfigureIO;
use Akeeba\Backup\Admin\CliCommands\MixIt\PrintFormattedArray;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * akeeba:backup:info
 *
 * Lists a backup record known to Akeeba Backup
 *
 * @since   7.5.0
 */
class BackupInfo extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:backup:info';

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

		$id     = (int) $this->cliInput->getArgument('id') ?? 0;
		$format = (string) ($this->cliInput->getOption('format') ?? 'table');

		if ($format === 'table')
		{
			$this->ioStyle->title(sprintf('Information for Akeeba Backup record #%d', $id));
		}

		$record = Platform::getInstance()->get_statistics($id);

		return $this->printFormattedAndReturn($record, $format);
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
		$help = "<info>%command.name%</info> will list a backup record known to Akeeba Backup
		\nUsage: <info>php %command.full_name%</info>";

		$this->addArgument('id', InputArgument::REQUIRED, 'The id of the backup record to list');
		$this->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: table, json, yaml, csv, count.', 'table');
		$this->setDescription('Lists a backup record known to Akeeba Backup');
		$this->setHelp($help);
	}
}
