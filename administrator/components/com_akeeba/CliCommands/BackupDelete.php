<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands;

defined('_JEXEC') || die;

use Akeeba\Backup\Admin\Model\Statistics;
use FOF30\Container\Container;
use Joomla\Console\Command\AbstractCommand;
use Akeeba\Backup\Admin\CliCommands\MixIt\ArgumentUtilities;
use Akeeba\Backup\Admin\CliCommands\MixIt\ConfigureIO;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * akeeba:backup:delete
 *
 * Deletes a backup record known to Akeeba Backup, or just its files
 *
 * @since   7.5.0
 */
class BackupDelete extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:backup:delete';

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

		$id        = (int) $this->cliInput->getArgument('id') ?? 0;
		$onlyFiles = $this->cliInput->getOption('only-files');

		$this->ioStyle->title(sprintf('Deleting Akeeba Backup record #%d', $id));

		if ($id <= 0)
		{
			$this->ioStyle->error('Invalid backup record');

			return 1;
		}

		$container = Container::getInstance('com_akeeba', [], 'admin');
		/** @var Statistics $model */
		$model = $container->factory->model('Statistics')->tmpInstance();
		$model->setState('id', $id);

		try
		{
			if ($onlyFiles)
			{
				$model->deleteFile();

				$this->ioStyle->success(sprintf('The files of backup record #%d have been deleted.', $id));

				return 0;
			}

			$model->delete();

			$this->ioStyle->success(sprintf('The backup record #%d has been deleted.', $id));

		}
		catch (\RuntimeException $e)
		{
			if ($onlyFiles)
			{
				$this->ioStyle->error(sprintf('Cannot delete the files of backup record #%d: %s', $id, $e->getMessage()));
			}
			else
			{
				$this->ioStyle->error(sprintf('Cannot delete backup record #%d: %s', $id, $e->getMessage()));
			}

			return 1;
		}

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
		$help = "<info>%command.name%</info> will delete a backup record known to Akeeba Backup, or just its files
		\nUsage: <info>php %command.full_name%</info>";

		$this->addArgument('id', InputArgument::REQUIRED, 'The id of the backup record to delete');
		$this->addOption('only-files', null, InputOption::VALUE_NONE, 'Only delete the backup files stored on the site\'s server, not the record itself.');
		$this->setDescription('Deletes a backup record known to Akeeba Backup, or just its files');
		$this->setHelp($help);
	}
}
