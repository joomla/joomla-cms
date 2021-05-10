<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Installer\Installer;
use Joomla\Component\Installer\Administrator\Model\DiscoverModel;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for discovering extensions
 *
 * @since  __DEPLOY_VERSION__
 */
class ExtensionDiscoverCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'extension:discover';

	/**
	 * Stores the Input Object
	 *
	 * @var    InputInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $cliInput;

	/**
	 * SymfonyStyle Object
	 *
	 * @var    SymfonyStyle
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $ioStyle;

	/**
	 * Instantiate the command.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Configures the IO
	 *
	 * @param   InputInterface   $input   Console Input
	 * @param   OutputInterface  $output  Console Output
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 */
	private function configureIO(InputInterface $input, OutputInterface $output): void
	{
		$this->cliInput = $input;
		$this->ioStyle = new SymfonyStyle($input, $output);
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function configure(): void
	{
		$help = "<info>%command.name%</info> is used to discover extensions
		\nUsage:
		\n  <info>php %command.full_name%</info>";

		$this->setDescription('Discover extensions');
		$this->setHelp($help);
	}

	/**
	 * Used for discovering extensions
	 *
	 * @return  integer  The count of discovered extensions
	 *
	 * @throws  \Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function processDiscover(): int
	{
		$app = $this->getApplication();

		$mvcFactory = $app->bootComponent('com_installer')->getMVCFactory();

		$model = $mvcFactory->createModel('Discover', 'Administrator');

		return $model->discover();
	}

	/**
	 * Used for finding the text for the note
	 *
	 * @return  string  The text for the note
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getNote(int $count): string
	{
		if ($count < 1)
		{
			return 'There is no extension to discover.';
		}
		elseif ($count === 1)
		{
			return $count . ' extension has been discovered successfully.';
		}
		else
		{
			return $count . ' extensions have been discovered successfully.';
		}
	}

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$this->configureIO($input, $output);

		$count = $this->processDiscover();

		$this->ioStyle->note($this->getNote($count));

		return Command::SUCCESS;
	}
}
