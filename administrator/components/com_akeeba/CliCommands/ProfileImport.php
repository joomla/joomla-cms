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
use Joomla\Console\Command\AbstractCommand;
use Akeeba\Backup\Admin\CliCommands\MixIt\ArgumentUtilities;
use Akeeba\Backup\Admin\CliCommands\MixIt\ConfigureIO;
use Akeeba\Backup\Admin\CliCommands\MixIt\PrintFormattedArray;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * akeeba:profile:import
 *
 * Imports an Akeeba Backup profile from a JSON string.
 *
 * @since   7.5.0
 */
class ProfileImport extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:profile:import';

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

		$filename = (string) $this->cliInput->getArgument('fileOrJSON') ?? '';
		$json     = $this->getJSON($filename);

		try
		{
			$decoded = @json_decode($json, true);
		}
		catch (Exception $e)
		{
			$decoded = '';
		}

		if (empty($decoded))
		{
			$this->ioStyle->error("Cannot process input; invalid JSON string or file not found.");

			return 1;
		}

		// We must never pass an ID, forcing the model to create a new record
		if (isset($decoded['id']))
		{
			unset($decoded['id']);
		}

		$container = Container::getInstance('com_akeeba');

		/** @var Profiles $model */
		$model = $container->factory->model('Profiles')->tmpInstance();

		try
		{
			$newProfile = $model->create($decoded);
		}
		catch (Exception $e)
		{
			$this->ioStyle->error(sprintf("Cannot import profile: %s", $e->getMessage()));

			return 2;
		}

		$id     = $newProfile->getId();
		$format = (string) $this->cliInput->getOption('format') ?? 'text';

		if ($format == 'json')
		{
			echo json_encode($id);

			return 0;
		}

		$this->ioStyle->success(sprintf("Successfully imported JSON as profile #%s", $id));

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
		$help = "<info>%command.name%</info> will import an Akeeba Backup profile from a JSON string.
		\nUsage: <info>php %command.full_name%</info>";

		$this->addArgument('fileOrJSON', InputOption::VALUE_OPTIONAL, 'A path to an Akeeba Backup profile export JSON file or a literal JSON string. Uses STDIN if omitted.');
		$this->addOption('format', null, InputOption::VALUE_OPTIONAL, 'The format for the response. Use json to get a JSON-parseable numeric ID of the new backup profile. Values: json, text', 'text');

		$this->setDescription('Imports an Akeeba Backup profile from a JSON string');
		$this->setHelp($help);
	}

	/**
	 * Get the JSON input
	 *
	 * @param   string|null  $filename  The filename to read from, raw JSON data or an empty string
	 *
	 * @return  string  The JSON data
	 *
	 * @since   7.5.0
	 */
	private function getJSON(?string $filename): string
	{
		// No filename or JSON string passed to script; use STDIN
		if (empty($filename))
		{
			$json = '';

			while (!feof(STDIN))
			{
				$json .= fgets(STDIN) . "\n";
			}

			return rtrim($json);
		}

		// An existing file path was passed. Return the contents of the file.
		if (@file_exists($filename))
		{
			$ret = @file_get_contents($filename);

			if ($ret === false)
			{
				return '';
			}
		}

		// Otherwise assume raw JSON was passed back to us.
		return $filename;
	}

}
