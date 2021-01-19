<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands;

defined('_JEXEC') || die;

use Akeeba\Backup\Admin\Model\Profiles;
use Exception;
use FOF30\Container\Container;
use FOF30\Model\DataModel\Exception\RecordNotLoaded;
use Joomla\Console\Command\AbstractCommand;
use Akeeba\Backup\Admin\CliCommands\MixIt\ArgumentUtilities;
use Akeeba\Backup\Admin\CliCommands\MixIt\ConfigureIO;
use Akeeba\Backup\Admin\CliCommands\MixIt\PrintFormattedArray;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * akeeba:profile:delete
 *
 * Delete an Akeeba Backup profile
 *
 * @since   7.5.0
 */
class ProfileDelete extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:profile:delete';

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

		$id = (int) $this->cliInput->getArgument('id') ?? 0;

		if ($id === 1)
		{
			$this->ioStyle->error('You cannot delete the default backup profile (#1)');
		}

		$container = Container::getInstance('com_akeeba');

		/** @var Profiles $model */
		$model = $container->factory->model('Profiles')->tmpInstance();

		try
		{
			$profile = $model->findOrFail($id);
		}
		catch (RecordNotLoaded $e)
		{
			$this->ioStyle->error(sprintf("Cannot delete profile %s; profile not found.", $id));

			return 2;
		}

		try
		{
			$newProfile = $model->forceDelete($profile->getId());
		}
		catch (Exception $e)
		{
			$this->ioStyle->error(sprintf("Cannot delete profile #%s: %s", $id, $e->getMessage()));

			return 3;
		}

		$this->ioStyle->success(sprintf("Profile #%d has been deleted.", $newProfile->getId()));

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
		$help = "<info>%command.name%</info> will delete an Akeeba Backup profile.
		\nUsage: <info>php %command.full_name%</info>";

		$this->addArgument('id', InputOption::VALUE_REQUIRED, 'The numeric ID of the profile to delete');

		$this->setDescription('Delete an Akeeba Backup profile');
		$this->setHelp($help);
	}
}
