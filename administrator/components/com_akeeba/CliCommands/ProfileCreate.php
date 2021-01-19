<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands;

defined('_JEXEC') || die;

use Akeeba\Backup\Admin\Model\Profiles;
use Akeeba\Engine\Platform;
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
 * akeeba:profile:create
 *
 * Creates a new Akeeba Backup profile
 *
 * @since   7.5.0
 */
class ProfileCreate extends AbstractCommand
{
	use ConfigureIO, ArgumentUtilities, PrintFormattedArray;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  7.5.0
	 */
	protected static $defaultName = 'akeeba:profile:create';

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

		$format = (string) $this->cliInput->getOption('format') ?? 'text';
		$format = in_array($format, ['text', 'json']) ? $format : 'text';

		$container = Container::getInstance('com_akeeba');

		/** @var Profiles $model */
		$model = $container->factory->model('Profiles')->tmpInstance();

		// Set up the new profile data
		$profileData = [
			'description'   => 'New backup profile',
			'quickicon'     => '1',
			'configuration' => '',
			'filters'       => '',
		];

		$description = (string) $this->cliInput->getArgument('description') ?? 0;

		if (!is_null($description))
		{
			$profileData['description'] = trim($description);
		}

		$profileData['quickicon'] = ((bool) $this->cliInput->getArgument('quickicon') ?? true) ? 1 : 0;

		try
		{
			$newProfile = $model->create($profileData);
		}
		catch (Exception $e)
		{
			$this->ioStyle->error(sprintf("Cannot create profile: %s", $e->getMessage()));

			return 2;
		}

		/**
		 * Create a new profile configuration.
		 *
		 * Loading the new profile's empty configuration causes the Platform code to revert to the default options and
		 * save them automatically to the database.
		 */
		$profileId = $newProfile->getId();
		Platform::getInstance()->load_configuration($profileId);

		if ($format == 'json')
		{
			echo json_encode($newProfile->getId());

			return 0;
		}

		$this->ioStyle->success(sprintf("Created new profile with ID %s.", $newProfile->getId()));

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
		$help = "<info>%command.name%</info> will create a new Akeeba Backup profile.
		\nUsage: <info>php %command.full_name%</info>";

		$this->addOption('description', null, InputOption::VALUE_OPTIONAL, 'Description for the new backup profile. Default: "New backup profile".', 'New backup profile');
		$this->addOption('quickicon', null, InputOption::VALUE_OPTIONAL, 'Should the new backup profile have a one-click backup icon? Default: 1', 1);
		$this->addOption('format', null, InputOption::VALUE_OPTIONAL, 'The format for the response. Use JSON to get a JSON-parseable numeric ID of the new backup profile. Values: text, json', 'text');

		$this->setDescription('Creates a new Akeeba Backup profile');
		$this->setHelp($help);
	}
}
