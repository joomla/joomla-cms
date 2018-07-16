<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

defined('JPATH_PLATFORM') or die;

use Joomla\Console\AbstractCommand;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Joomla\CMS\Factory;

/**
 * Console command for checking if there are pending extension updates
 *
 * @since  4.0.0
 */
class GetConfigurationCommand extends AbstractCommand
{
	/**
	 * Stores the Input Object
	 * @var Input
	 * @since 4.0
	 */
	private $cliInput;

	/**
	 * SymfonyStyle Object
	 * @var SymfonyStyle
	 * @since 4.0
	 */
	private $ioStyle;

	/**
	 * Configures the IO
	 *
	 * @return void
	 *
	 * @since 4.0
	 */
	private function configureIO()
	{
		$this->cliInput = $this->getApplication()->getConsoleInput();
		$this->ioStyle = new SymfonyStyle($this->getApplication()->getConsoleInput(), $this->getApplication()->getConsoleOutput());
	}

	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 *
	 * @since   4.0.0
	 */
	public function execute(): int
	{
		$this->configureIO();
		$option = $this->cliInput->getArgument('option');

		$configs = $this->getApplication()->getConfig()->toArray();

		if (!array_key_exists($option, $configs))
		{
			$this->ioStyle->error("Can't find option *$option* in configuration list");

			return 1;
		}

		$value = $this->getApplication()->get($option) ?: 'Not set';

		$this->ioStyle->table(['Option', 'Value'], [[$option, $value]]);

		return 0;
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function initialise()
	{
		$this->setName('config:get');
		$this->setDescription('Displays the current value of a configuration option');

		$this->addArgument('option', InputArgument::REQUIRED, 'Name of the option');

		$help = "The <info>%command.name%</info> Displays the current value of a configuration option
				\nUsage: <info>php %command.full_name%</info> <option>";

		$this->setHelp($help);
	}
}
