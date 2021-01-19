<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands;

defined('_JEXEC') || die;

use Akeeba\Backup\Admin\Model\Statistics;
use FOF30\Container\Container;
use Joomla\Console\Command\AbstractCommand;
use Akeeba\Backup\Admin\CliCommands\MixIt\ArgumentUtilities;
use Akeeba\Backup\Admin\CliCommands\MixIt\ConfigureIO;
use Akeeba\Backup\Admin\CliCommands\MixIt\PrintFormattedArray;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * akeeba:backup:list
 *
 * Lists backup records known to Akeeba Backup
 *
 * @since   7.5.0
 */
class BackupList extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:backup:list';

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

		$from    = (int) ($this->cliInput->getOption('from') ?? 0);
		$limit   = (int) ($this->cliInput->getOption('limit') ?? 0);
		$format  = (string) ($this->cliInput->getOption('format') ?? 'table');
		$filters = $this->getFilters();
		$order   = $this->getOrdering();

		if ($format === 'table')
		{
			$this->ioStyle->title('List of Akeeba Backup records matching your criteria');
		}

		$container = Container::getInstance('com_akeeba', [], 'admin');

		/** @var Statistics $model */
		$model = $container->factory->model('Statistics')->tmpInstance();

		$model->setState('limitstart', $from);
		$model->setState('limit', $limit);

		$output = $model->getStatisticsListWithMeta(false, $filters, $order);

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
		$help = "<info>%command.name%</info> will list backup records known to Akeeba Backup
		\nUsage: <info>php %command.full_name%</info>";

		$this->addOption('from', null, InputOption::VALUE_OPTIONAL, 'How many backup records to skip before starting the output.', 0);
		$this->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Maximum number of backup records to display.', 50);
		$this->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: table, json, yaml, csv, count.', 'table');
		$this->addOption('description', null, InputOption::VALUE_OPTIONAL, 'Listed backup records must match this (partial) description.');
		$this->addOption('after', null, InputOption::VALUE_OPTIONAL, 'List backup records taken after this date.');
		$this->addOption('before', null, InputOption::VALUE_OPTIONAL, 'List backup records taken before this date.');
		$this->addOption('origin', null, InputOption::VALUE_OPTIONAL, 'List backups from this origin only: backend, frontend, json, cli.');
		$this->addOption('profile', null, InputOption::VALUE_OPTIONAL, 'List backups taken with this profile. Give the numeric profile ID.');
		$this->addOption('sort-by', null, InputOption::VALUE_OPTIONAL, 'Sort the output by the given column: id, description, profile_id, backupstart', 'id');
		$this->addOption('sort-order', null, InputOption::VALUE_OPTIONAL, 'Sort order: asc, desc.', 'desc');
		$this->setDescription('Lists backup records known to Akeeba Backup');
		$this->setHelp($help);
	}

	private function getFilters(): ?array
	{
		$filters = [];

		$description = $this->cliInput->getOption('description') ?? '';

		if ($description)
		{
			$filters[] = [
				'field'   => 'description',
				'operand' => 'LIKE',
				'value'   => $description,
			];
		}

		$after  = $this->cliInput->getOption('after') ?? '';
		$before = $this->cliInput->getOption('before') ?? '';

		if (!empty($after) && !empty($before))
		{
			$filters[] = [
				'field'   => 'backupstart',
				'operand' => 'BETWEEN',
				'value'   => $after,
				'value2'  => $before,
			];
		}
		elseif (!empty($after))
		{
			$filters[] = [
				'field'   => 'backupstart',
				'operand' => '>=',
				'value'   => $after,
			];
		}
		elseif (!empty($before))
		{
			$filters[] = [
				'field'   => 'backupstart',
				'operand' => '<=',
				'value'   => $before,
			];
		}

		$origin = $this->cliInput->getOption('origin') ?? '';

		if (!empty($origin))
		{
			$filters[] = [
				'field'   => 'origin',
				'operand' => '=',
				'value'   => $origin,
			];
		}

		$profile = (int) ($this->cliInput->getOption('profile') ?? 0);

		if ($profile > 0)
		{
			$filters[] = [
				'field'   => 'profile_id',
				'operand' => '=',
				'value'   => $profile,
			];
		}

		return !empty($filters) ? $filters : null;
	}

	private function getOrdering(): array
	{
		$order = strtolower($this->cliInput->getOption('sort-order') ?? 'desc');
		$order = in_array($order, ['asc', 'desc']) ?: 'desc';

		return [
			'by'    => $this->cliInput->getOption('sort-by') ?? 'id',
			'order' => $order,
		];
	}
}
