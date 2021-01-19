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
 * akeeba:option:set
 *
 * Sets the value of a configuration option for an Akeeba Backup profile
 *
 * @since   7.5.0
 */
class OptionsSet extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray, JsonGuiDataParser;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:option:set';

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
		$value = (string) $this->cliInput->getArgument('value') ?? '';

		// Get the key information from the GUI data
		$info = $this->parseJsonGuiData();

		// Does the key exist?
		if (!array_key_exists($key, $info['options']))
		{
			$this->ioStyle->error(sprintf("Invalid option key '%s'.", $key));

			return 2;
		}

		// Validate / sanitize the value
		$optionInfo = $this->getOptionInfo($key, $info);

		switch ($optionInfo['type'])
		{
			case 'integer':
				$value = (int) $value;

				if (($value < $optionInfo['limits']['min']) || ($value > $optionInfo['limits']['max']))
				{
					$this->ioStyle->error(sprintf("Invalid value '%s': out of bounds.", $value));

					return 3;
				}
				break;

			case 'bool':
				if (is_numeric($value))
				{
					$value = (int) $value;
				}
				elseif (is_string($value))
				{
					$value = strtolower($value);
				}

				if (in_array($value, [false, 0, '0', 'false', 'no', 'off'], true))
				{
					$value = 0;
				}
				elseif (in_array($value, [true, 1, '1', 'true', 'yes', 'on'], true))
				{
					$value = 1;
				}
				else
				{
					$this->ioStyle->error(sprintf("Invalid boolean value '%s': use one of 0, false, no, off, 1, true, yes or on.'", $value));

					return 3;
				}

				break;

			case 'enum':
				if (!in_array($value, $optionInfo['options']))
				{
					$options = array_map(function ($v) {
						return "'$v'";
					}, $optionInfo['options']);
					$options = implode(', ', $options);

					$this->ioStyle->error(sprintf("Invalid enumerated value '%s'. Must be one of %s.", $value, $options));

					return 3;
				}

				break;

			case 'hidden':
				$this->ioStyle->error(sprintf("Setting hidden option '%s' is not allowed.", $key));

				return 3;
				break;

			case 'string':
				break;

			default:
				$this->ioStyle->error(sprintf("Unknown type %s for option '%s'. Have you manually tampered with the option JSON files?", $optionInfo['type'], $key));

				return 3;
				break;
		}

		$protected = $config->getProtectedKeys();
		$force     = isset($assoc_args['force']) && $assoc_args['force'];

		if (in_array($key, $protected) && !$force)
		{
			$this->ioStyle->error(sprintf("Cannot set protected option '%s'. Please use the --force option to override the protection.", $key));

			return 4;
		}

		if (in_array($key, $protected) && $force)
		{
			$config->setKeyProtection($key, false);
		}

		$result = $config->set($key, $value, false);

		if ($result === false)
		{
			$this->ioStyle->error(sprintf("Could not set option '%s'.", $key));

			return 5;
		}

		Platform::getInstance()->save_configuration($profileId);

		$this->ioStyle->success(sprintf("Successfully set option '%s' to '%s'", $key, $value));

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
		$help = "<info>%command.name%</info> will set the value of a configuration option for an Akeeba Backup profile.
		\nUsage: <info>php %command.full_name%</info>";

		$this->addArgument('key', InputOption::VALUE_REQUIRED, 'The option key to set');
		$this->addArgument('value', InputOption::VALUE_REQUIRED, 'The value to set');
		$this->addOption('profile', null, InputOption::VALUE_OPTIONAL, 'The backup profile to use. Default: 1.', 1);
		$this->addOption('force', null, InputOption::VALUE_NONE, 'Allow setting the value of protected options.', false);

		$this->setDescription('Sets the value of a configuration option for an Akeeba Backup profile');
		$this->setHelp($help);
	}
}
