<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands;

defined('_JEXEC') || die;

use Akeeba\Backup\Admin\Model\Profiles;
use Akeeba\Engine\Factory;
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
 * akeeba:profile:export
 *
 * Exports an Akeeba Backup profile as a JSON string.
 *
 * @since   7.5.0
 */
class ProfileExport extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:profile:export';

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

		$id      = (int) $this->cliInput->getArgument('id') ?? 0;
		$filters = (bool) $this->cliInput->getOption('filters') ?? false;

		$container = Container::getInstance('com_akeeba');

		/** @var Profiles $model */
		$model = $container->factory->model('Profiles')->tmpInstance();

		try
		{
			$profile = $model->findOrFail($id);
		}
		catch (RecordNotLoaded $e)
		{
			$this->ioStyle->error(sprintf("Cannot export profile %s; profile not found.", $id));

			return 1;
		}

		$data = $profile->toArray();

		if (!$filters)
		{
			unset($data['filters']);
		}

		unset($data['id']);

		// Decrypt configuration data if necessary
		if (substr($data['configuration'], 0, 12) == '###AES128###')
		{
			// Load the server key file if necessary
			$key = Factory::getSecureSettings()->getKey();

			$data['configuration'] = Factory::getSecureSettings()->decryptSettings($data['configuration'], $key);
		}

		echo json_encode($data);

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
		$help = "<info>%command.name%</info> will exports an Akeeba Backup profile as a JSON string.
		\nUsage: <info>php %command.full_name%</info>";

		$this->addArgument('id', InputOption::VALUE_REQUIRED, 'The numeric ID of the profile to modify');
		$this->addOption('filters', null, InputOption::VALUE_NONE, 'Include the filter settings?', false);

		$this->setDescription('Exports an Akeeba Backup profile as a JSON string');
		$this->setHelp($help);
	}
}
