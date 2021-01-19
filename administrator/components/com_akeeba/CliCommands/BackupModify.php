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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * akeeba:backup:modify
 *
 * Modifies a backup record known to Akeeba Backup
 *
 * @since   7.5.0
 */
class BackupModify extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:backup:modify';

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

		$id          = (int) $this->cliInput->getArgument('id') ?? 0;
		$description = $this->cliInput->getOption('description');
		$comment     = $this->cliInput->getOption('comment');

		$this->ioStyle->title(sprintf('Modifying Akeeba Backup record #%d', $id));

		if ($id <= 0)
		{
			$this->ioStyle->error('Invalid backup record');

			return 1;
		}

		if (is_null($description) && is_null($comment))
		{
			$this->ioStyle->error('You must specify one or both of --description and --comment');

			return 2;
		}

		$record = Platform::getInstance()->get_statistics($id);

		if (empty($record))
		{
			$this->ioStyle->error('Invalid backup record');

			return 1;
		}

		if (!is_null($description))
		{
			$record['description'] = (string) $description;
		}

		if (!is_null($comment))
		{
			$record['comment'] = (string) $comment;
		}

		$result = Platform::getInstance()->set_or_update_statistics($id, $record);

		if ($result === false)
		{
			$this->ioStyle->error(sprintf('Cannot modify backup record #%d', $id));

			return 3;
		}

		$this->ioStyle->success(sprintf('Backup record #%d has been modified.', $id));

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
		$help = "<info>%command.name%</info> will modify a backup record known to Akeeba Backup
		\nUsage: <info>php %command.full_name%</info>";

		$this->addArgument('id', InputArgument::REQUIRED, 'The id of the backup record to modify');
		$this->addOption('description', null, InputOption::VALUE_OPTIONAL, 'Change the short description to this value.');
		$this->addOption('comment', null, InputOption::VALUE_OPTIONAL, 'Change the backup comment to this value (accepts HTML).');
		$this->setDescription('Modifies a backup record known to Akeeba Backup');
		$this->setHelp($help);
	}
}
