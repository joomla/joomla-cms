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
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command wrapper for getting the site into offline mode
 *
 * @since  4.0.0
 */
class SiteUpCommand extends AbstractCommand
{
	/**
	 * SymfonyStyle Object
	 * @var SymfonyStyle
	 * @since 4.0
	 */
	private $ioStyle;

	/**
	 * Return code if site:up failed
	 * @since 4.0
	 */
	const SITE_UP_FAILED = 1;

	/**
	 * Return code if site:up was successful
	 * @since 4.0
	 */
	const SITE_UP_SUCCESSFUL = 0;

	/**
	 * Configures the IO
	 *
	 * @return void
	 *
	 * @since 4.0
	 */
	private function configureIO()
	{
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

		$command = $this->getApplication()->getCommand('config:set');

		$command->setOptions('offline=false');

		$returnCode = $command->execute();

		if ($returnCode === 0)
		{
			$this->ioStyle->success("Website is now online");

			return self::SITE_UP_SUCCESSFUL;
		}


		return self::SITE_UP_FAILED;
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
		$this->setName('site:up');
		$this->setDescription('Puts the site into online mode');

		$help = "The <info>%command.name%</info> Puts the site into online mode
				\nUsage: <info>php %command.full_name%</info>";

		$this->setHelp($help);
	}
}
