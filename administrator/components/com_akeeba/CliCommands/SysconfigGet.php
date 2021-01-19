<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands;

defined('_JEXEC') || die;

use Joomla\Console\Command\AbstractCommand;
use Akeeba\Backup\Admin\CliCommands\MixIt\ArgumentUtilities;
use Akeeba\Backup\Admin\CliCommands\MixIt\ComponentOptions;
use Akeeba\Backup\Admin\CliCommands\MixIt\ConfigureIO;
use Akeeba\Backup\Admin\CliCommands\MixIt\PrintFormattedArray;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * akeeba:sysconfig:get
 *
 * Gets the value of an Akeeba Backup component-wide option
 *
 * @since   7.5.0
 */
class SysconfigGet extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray, ComponentOptions;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:sysconfig:get';

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
		$format  = (string) $this->cliInput->getOption('format') ?? 'table';
		$options = $this->getComponentOptions();

		if (!array_key_exists($key, $options))
		{
			$this->ioStyle->error(sprintf('Cannot find option “%s”.', $key));

			return 1;
		}

		$value = $options[$key] ?? '';

		switch ($format)
		{
			case 'text':
			default:
				echo $value;
				break;

			case 'json':
				echo json_encode($value);
				break;

			case 'print_r':
				print_r($value);
				break;

			case 'var_dump':
				var_dump($value);
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
		$help = "<info>%command.name%</info> will get the value of an Akeeba Backup component-wide option.
		\nUsage: <info>php %command.full_name%</info>";


		$this->addArgument('key', null, InputOption::VALUE_REQUIRED, 'The option key to retrieve');
		$this->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: text, json, print_r, var_dunp, var_export.', 'text');

		$this->setDescription('Gets the value of an Akeeba Backup component-wide option');
		$this->setHelp($help);
	}
}
