<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands;

defined('_JEXEC') || die;

use Akeeba\Backup\Admin\Model\Log;
use FOF30\Container\Container;
use Joomla\Console\Command\AbstractCommand;
use Akeeba\Backup\Admin\CliCommands\MixIt\ArgumentUtilities;
use Akeeba\Backup\Admin\CliCommands\MixIt\ConfigureIO;
use Akeeba\Backup\Admin\CliCommands\MixIt\PrintFormattedArray;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * akeeba:log:get
 *
 * Retrieves log files known to Akeeba Backup
 *
 * @since   7.5.0
 */
class LogGet extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:log:get';

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
		$log_tag    = (string) $this->cliInput->getArgument('log_tag') ?? 1;

		define('AKEEBA_PROFILE', $profile_id);

		$container = Container::getInstance('com_akeeba', [], 'admin');

		/** @var Log $model */
		$model = $container->factory->model('Log')->tmpInstance();
		$model->setState('tag', $log_tag);
		$model->echoRawLog(true);

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
		$help = "<info>%command.name%</info> will retrieve a log file from the output directory of the Akeeba Backup profile specified. Note: log files from other backup profiles or Akeeba Backup installations sharing the same output directory can also be retrieved.
		\nUsage: <info>php %command.full_name%</info>";

		$this->addArgument('profile_id', InputArgument::REQUIRED, 'Log files in the output directory of this Akeeba Backup profile will be retrieved');
		$this->addArgument('log_tag', InputArgument::REQUIRED, 'The tag of the log file to retrieve');
		$this->setDescription('Retrieve a log file known to Akeeba Backup');
		$this->setHelp($help);
	}
}
