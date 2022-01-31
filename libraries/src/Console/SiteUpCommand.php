<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

\defined('JPATH_PLATFORM') or die;

use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command wrapper for getting the site into offline mode
 *
 * @since  4.0.0
 */
class SiteUpCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected static $defaultName = 'site:up';

	/**
	 * SymfonyStyle Object
	 * @var SymfonyStyle
	 * @since 4.0.0
	 */
	private $ioStyle;

	/**
	 * Return code if site:up failed
	 * @since 4.0.0
	 */
	const SITE_UP_FAILED = 1;

	/**
	 * Return code if site:up was successful
	 * @since 4.0.0
	 */
	const SITE_UP_SUCCESSFUL = 0;

	/**
	 * Configures the IO
	 *
	 * @param   InputInterface   $input   Console Input
	 * @param   OutputInterface  $output  Console Output
	 *
	 * @return void
	 *
	 * @since 4.0.0
	 *
	 */
	private function configureIO(InputInterface $input, OutputInterface $output)
	{
		$this->ioStyle = new SymfonyStyle($input, $output);
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function configure(): void
	{
		$help = "<info>%command.name%</info> puts the site into online mode
				\nUsage: <info>php %command.full_name%</info>";

		$this->setDescription('Put the site into online mode');
		$this->setHelp($help);
	}

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @since   4.0.0
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$this->configureIO($input, $output);

		$returnCode = $this->getApplication()->getCommand(SetConfigurationCommand::getDefaultName())->execute(
			new ArrayInput(['options' => ['offline=false']]), $output
		);

		if ($returnCode === 0)
		{
			$this->ioStyle->success("Website is now online");

			return self::SITE_UP_SUCCESSFUL;
		}

		return self::SITE_UP_FAILED;
	}
}
