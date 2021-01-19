<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands;

defined('_JEXEC') || die;

use Akeeba\Backup\Admin\Model\Profiles;
use Akeeba\Engine\Platform;
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
 * akeeba:profile:reset
 *
 * Resets an Akeeba Backup profile
 *
 * @since   7.5.0
 */
class ProfileReset extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:profile:reset';

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

		$id            = (int) $this->cliInput->getArgument('id') ?? 0;
		$filters       = (bool) $this->cliInput->getOption('filters') ?? false;
		$configuration = (bool) $this->cliInput->getOption('configuration') ?? false;

		$container = Container::getInstance('com_akeeba');

		/** @var Profiles $model */
		$model = $container->factory->model('Profiles')->tmpInstance();

		try
		{
			$profile = $model->findOrFail($id);
		}
		catch (RecordNotLoaded $e)
		{
			$this->ioStyle->error(sprintf("Cannot modify profile %s; profile not found.", $id));

			return 1;
		}

		if ($filters)
		{
			$profile->filters = '';
		}

		if ($configuration)
		{
			$profile->configuration = '';
		}

		try
		{
			$newProfile = $profile->save();
		}
		catch (Exception $e)
		{
			$this->ioStyle->error(sprintf("Cannot reset profile #%s: %s", $id, $e->getMessage()));

			return 2;
		}

		/**
		 * Loading the new profile's empty configuration causes the Platform code to revert to the default options and
		 * save them automatically to the database.
		 */
		if ($configuration)
		{
			Platform::getInstance()->load_configuration($id);
		}

		$this->ioStyle->success(sprintf("Profile #%s reset successfully.", $newProfile->getId()));

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
		$help = "<info>%command.name%</info> will resets an Akeeba Backup profile.
		\nUsage: <info>php %command.full_name%</info>";

		$this->addArgument('id', InputOption::VALUE_REQUIRED, 'The numeric ID of the profile to modify');
		$this->addOption('filters', null, InputOption::VALUE_NONE, 'Reset the filters?', false);
		$this->addOption('configuration', null, InputOption::VALUE_NONE, 'Reset the configuration?', false);

		$this->setDescription('Resets an Akeeba Backup profile');
		$this->setHelp($help);
	}
}
