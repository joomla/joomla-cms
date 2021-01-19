<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands;

defined('_JEXEC') || die;

use Akeeba\Backup\Admin\Model\Profiles;
use FOF30\Container\Container;
use Joomla\Console\Command\AbstractCommand;
use Akeeba\Backup\Admin\CliCommands\MixIt\ArgumentUtilities;
use Akeeba\Backup\Admin\CliCommands\MixIt\ConfigureIO;
use Akeeba\Backup\Admin\CliCommands\MixIt\PrintFormattedArray;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * akeeba:profile:list
 *
 * Lists the Akeeba Backup backup profiles
 *
 * @since   7.5.0
 */
class ProfileList extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:profile:list';

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

		$format    = (string) $this->cliInput->getOption('format') ?? 'table';
		$container = Container::getInstance('com_akeeba');

		/** @var Profiles $model */
		$model    = $container->factory->model('Profiles')->tmpInstance();
		$profiles = $model->get(true);

		$output = array_map(function (array $profile) {
			return [
				'id'          => $profile['id'],
				'description' => $profile['description'],
				'quickicon'   => $profile['quickicon'],
			];
		}, $profiles->toArray());

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
		$help = "<info>%command.name%</info> will list the Akeeba Backup backup profiles.
		\nUsage: <info>php %command.full_name%</info>";


		$this->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format: table, json, yaml, csv, count.', 'table');

		$this->setDescription('Lists the Akeeba Backup backup profiles');
		$this->setHelp($help);
	}
}
