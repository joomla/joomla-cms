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
 * akeeba:profile:modify
 *
 * Modifies an Akeeba Backup profile
 *
 * @since   7.5.0
 */
class ProfileModify extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:profile:modify';

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

		$description = (string) $this->cliInput->getArgument('description') ?? 0;

		if (!is_null($description))
		{
			$profile->description = $description;
		}

		$profile->quickicon = (bool) $this->cliInput->getArgument('quickicon') ?? $profile->quickicon;

		try
		{
			$newProfile = $profile->save();
		}
		catch (Exception $e)
		{
			$this->ioStyle->error(sprintf("Cannot modify profile #%s: %s", $id, $e->getMessage()));

			return 2;
		}

		$this->ioStyle->success(sprintf("Profile #%s modified successfully.", $newProfile->getId()));

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
		$help = "<info>%command.name%</info> will modify an Akeeba Backup profile.
		\nUsage: <info>php %command.full_name%</info>";

		$this->addArgument('id', InputOption::VALUE_REQUIRED, 'The numeric ID of the profile to modify');
		$this->addOption('description', null, InputOption::VALUE_OPTIONAL, 'Description for the new backup profile. Uses the old profile\'s description if not specified.', null);
		$this->addOption('quickicon', null, InputOption::VALUE_OPTIONAL, 'Should the new backup profile have a one-click backup icon? Copies the old profile\'s setting if not specified.', null);

		$this->setDescription('Modifies an Akeeba Backup profile');
		$this->setHelp($help);
	}
}
