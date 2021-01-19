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
use Akeeba\Engine\Platform;
use FOF30\Container\Container;
use FOF30\Model\DataModel\Exception\RecordNotLoaded;
use Joomla\Console\Command\AbstractCommand;
use Akeeba\Backup\Admin\CliCommands\MixIt\ArgumentUtilities;
use Akeeba\Backup\Admin\CliCommands\MixIt\ConfigureIO;
use Akeeba\Backup\Admin\CliCommands\MixIt\JsonGuiDataParser;
use Akeeba\Backup\Admin\CliCommands\MixIt\PrintFormattedArray;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * akeeba:option:get
 *
 * Gets the value of a configuration option for an Akeeba Backup profile
 *
 * @since   7.5.0
 */
class OptionsGet extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray, JsonGuiDataParser;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:option:get';

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

		$container = Container::getInstance('com_akeeba');
		$profileId = (int) ($this->cliInput->getOption('profile') ?? 1);

		define('AKEEBA_PROFILE', $profileId);

		$format = (string) $this->cliInput->getOption('format') ?? 'text';

		/** @var Profiles $model */
		$model = $container->factory->model('Profiles')->tmpInstance();

		try
		{
			$model->findOrFail($profileId);
		}
		catch (RecordNotLoaded $e)
		{
			$this->ioStyle->error(sprintf("Could not find profile #%s.", $profileId));

			return 1;
		}

		unset($model);

		// Get the profile's configuration
		Platform::getInstance()->load_configuration($profileId);
		$config = Factory::getConfiguration();

		$key   = (string) $this->cliInput->getArgument('key') ?? '';
		$value = $config->get($key, null, false);

		if (!is_null($value) && !is_scalar($value))
		{
			$this->ioStyle->error(sprintf("This command cannot return multiple values given the partial key prefix “%s”. Please supply they exact key name you want to retrieve. Use the akeeba:option:list command to see the available keys with the prefix %s.", $key, $key));

			return 2;
		}

		if (is_null($value))
		{
			$this->ioStyle->error(sprintf("Invalid key “%s”.", $key));

			return 3;
		}

		switch ($format)
		{
			case 'text':
			default:
				echo $value . PHP_EOL;
				break;

			case 'json':
				echo json_encode($value) . PHP_EOL;
				break;

			case 'print_r':
				print_r($value);
				echo PHP_EOL;
				break;

			case 'var_dump':
				var_dump($value);
				echo PHP_EOL;
				break;

			case 'var_export':
				var_export($value);
				break;

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
		$help = "<info>%command.name%</info> will get the value of a configuration option for an Akeeba Backup profile.
		\nUsage: <info>php %command.full_name%</info>";

		$this->addArgument('key', InputOption::VALUE_REQUIRED, 'The option key to retrieve');
		$this->addOption('profile', null, InputOption::VALUE_OPTIONAL, 'The backup profile to use. Default: 1.', 1);
		$this->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text, json, print_r, var_dump, var_export.', 'text');

		$this->setDescription('Gets the value of a configuration option for an Akeeba Backup profile');
		$this->setHelp($help);
	}
}
