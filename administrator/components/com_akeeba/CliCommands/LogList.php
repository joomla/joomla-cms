<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands;

defined('_JEXEC') || die;

use Akeeba\Backup\Admin\Model\Log;
use Akeeba\Engine\Factory;
use FOF30\Container\Container;
use Joomla\Console\Command\AbstractCommand;
use Akeeba\Backup\Admin\CliCommands\MixIt\ArgumentUtilities;
use Akeeba\Backup\Admin\CliCommands\MixIt\ConfigureIO;
use Akeeba\Backup\Admin\CliCommands\MixIt\PrintFormattedArray;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * akeeba:log:list
 *
 * Lists log files known to Akeeba Backup
 *
 * @since   7.5.0
 */
class LogList extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:log:list';

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

		$profile_id = max(1, (int) $this->cliInput->getArgument('profile_id') ?? 1);
		$format     = (string) ($this->cliInput->getOption('format') ?? 'table');

		define('AKEEBA_PROFILE', $profile_id);

		$configuration   = Factory::getConfiguration();
		$outputDirectory = $configuration->get('akeeba.basic.output_directory');

		if ($format === 'table')
		{
			$this->ioStyle->title(sprintf('List of Akeeba Backup log files for output directory %s', $outputDirectory));
		}

		$container = Container::getInstance('com_akeeba', [], 'admin');

		/** @var Log $model */
		$model = $container->factory->model('Log')->tmpInstance();

		$output = array_map(function ($tag) use ($outputDirectory) {
			$possibilities = [
				$outputDirectory . '/akeeba.' . $tag . '.log',
				$outputDirectory . '/akeeba.' . $tag . '.log.php',
				$outputDirectory . '/akeeba' . $tag . '.log',
				$outputDirectory . '/akeeba' . $tag . '.log.php',
			];

			$path = null;

			foreach ($possibilities as $possiblePath)
			{
				if (@is_file($possiblePath))
				{
					$path = $possiblePath;

					break;
				}
			}

			if (empty($path))
			{
				return null;
			}

			return [
				'tag'           => $tag,
				'absolute_path' => $path,
			];

		}, $model->getLogFiles());

		$output = array_filter($output, function ($x) {
			return !is_null($x);
		});

		return $this->printFormattedAndReturn(array_values($output), $format);
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
		$help = "<info>%command.name%</info> will list all log files in the output directory of the Akeeba Backup profile specified. Note: log files from other backup profiles or Akeeba Backup installations sharing the same output directory will also be listed.
		\nUsage: <info>php %command.full_name%</info>";

		$this->addArgument('profile_id', InputArgument::OPTIONAL, 'Log files in the output directory of this Akeeba Backup profile will be listed', 1);
		$this->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: table, json, yaml, csv, count.', 'table');
		$this->setDescription('Lists log files known to Akeeba Backup');
		$this->setHelp($help);
	}
}
