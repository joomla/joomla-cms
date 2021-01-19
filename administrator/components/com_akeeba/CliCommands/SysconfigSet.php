<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands;

defined('_JEXEC') || die;

use Akeeba\Backup\Admin\Helper\SecretWord;
use FOF30\Container\Container;
use Joomla\Console\Command\AbstractCommand;
use Akeeba\Backup\Admin\CliCommands\MixIt\ArgumentUtilities;
use Akeeba\Backup\Admin\CliCommands\MixIt\ComponentOptions;
use Akeeba\Backup\Admin\CliCommands\MixIt\ConfigureIO;
use Akeeba\Backup\Admin\CliCommands\MixIt\PrintFormattedArray;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * akeeba:sysconfig:set
 *
 * Sets the value of an Akeeba Backup component-wide option
 *
 * @since   7.5.0
 */
class SysconfigSet extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray, ComponentOptions;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:sysconfig:set';

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

		$key     = (string) $this->cliInput->getArgument('key') ?? '';
		$value   = (string) $this->cliInput->getArgument('value') ?? '';
		$format  = (string) $this->cliInput->getOption('format') ?? 'table';
		$options = $this->getComponentOptions();

		if (!array_key_exists($key, $options))
		{
			$this->ioStyle->error(sprintf('Cannot find option “%s”.', $key));

			return 1;
		}

		if ((string) $options[$key] === $value)
		{
			return 0;
		}

		$container = Container::getInstance('com_akeeba');
		$container->params->set($key, $value);
		$container->params->save();

		// Make sure the front-end backup Secret Word is stored encrypted
		$params = $container->params;
		SecretWord::enforceEncryption($params, 'frontend_secret_word');

		$this->ioStyle->success(sprintf('Set component option “%s” to “%s”', $key, $value));

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
		$help = "<info>%command.name%</info> will set the value of an Akeeba Backup component-wide option.
		\nUsage: <info>php %command.full_name%</info>";


		$this->addArgument('key', null, InputOption::VALUE_REQUIRED, 'The option key to set');
		$this->addArgument('value', null, InputOption::VALUE_REQUIRED, 'The option value to set');
		$this->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text, json, print_r, var_dunp, var_export.', 'text');

		$this->setDescription('Sets the value of an Akeeba Backup component-wide option');
		$this->setHelp($help);
	}
}
