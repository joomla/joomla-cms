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
 * akeeba:option:list
 *
 * Lists the configuration options for an Akeeba Backup profile, including their titles
 *
 * @since   7.5.0
 */
class OptionsList extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray, JsonGuiDataParser;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:option:list';

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

		$format = (string) $this->cliInput->getOption('format') ?? 'table';

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
		$config  = Factory::getConfiguration();
		$rawJson = $config->exportAsJSON();

		unset($config);

		// Get the key information from the GUI data
		$info = $this->parseJsonGuiData();

		// Convert the INI data we got into an array we can print
		$rawValues = json_decode($rawJson, true);

		unset($rawJson);

		$output = [];

		$rawValues = $this->flattenOptions($rawValues);

		foreach ($rawValues as $key => $v)
		{
			$output[$key] = array_merge([
				'key'          => $key,
				'value'        => $v,
				'title'        => '',
				'description'  => '',
				'type'         => '',
				'default'      => '',
				'section'      => '',
				'options'      => [],
				'optionTitles' => [],
				'limits'       => [],
			], $this->getOptionInfo($key, $info));
		}

		// Filter the returned options
		$filter = (string) $this->cliInput->getOption('filter') ?? '';

		$output = array_filter($output, function ($item) use ($filter) {
			if (!empty($filter) && strpos($item['key'], $filter) === false)
			{
				return false;
			}

			return $item['type'] != 'hidden';
		});

		// Sort the results
		$sort  = (string) $this->cliInput->getOption('sort-by') ?? 'none';
		$order = (string) $this->cliInput->getOption('sort-order') ?? 'asc';

		if ($sort != 'none')
		{
			usort($output, function ($a, $b) use ($sort, $order) {
				if ($a[$sort] == $b[$sort])
				{
					return 0;
				}

				$signChange = ($order == 'asc') ? 1 : -1;
				$isGreater  = $a[$sort] > $b[$sort] ? 1 : -1;

				return $signChange * $isGreater;
			});
		}

		// Output the list
		if (empty($output))
		{
			$this->ioStyle->error("No options found matching your criteria.");

			return 2;
		}

		if ($format === 'table')
		{
			$output = array_map(function (array $optionDef) {
				return array_map(function ($value) {
					return is_array($value) ? implode(', ', $value) : $value;
				}, $optionDef);
			}, $output);
		}

		return $this->printFormattedAndReturn($output, $format);
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
		$help = "<info>%command.name%</info> will list the configuration options for an Akeeba Backup profile, including their titles.
		\nUsage: <info>php %command.full_name%</info>";


		$this->addOption('profile', null, InputOption::VALUE_OPTIONAL, 'The backup profile to use. Default: 1.', 1);
		$this->addOption('filter', null, InputOption::VALUE_OPTIONAL, 'Only return records whose keys begin with the given filter.', '');
		$this->addOption('sort-by', null, InputOption::VALUE_OPTIONAL, 'Sort the output by the given column: none, key, value, type, default, title, description, section', 'none');
		$this->addOption('sort-order', null, InputOption::VALUE_OPTIONAL, 'Sort order: asc, desc.', 'desc');
		$this->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: table, json, yaml, csv, count.', 'table');

		$this->setDescription('Lists the configuration options for an Akeeba Backup profile, including their titles');
		$this->setHelp($help);
	}
}
