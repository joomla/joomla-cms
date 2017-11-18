<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\Console\AbstractCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for cleaning the system cache
 *
 * @since  __DEPLOY_VERSION__
 */
class CleanCacheCommand extends AbstractCommand
{
	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function execute(): int
	{
		$symfonyStyle = new SymfonyStyle($this->getApplication()->getConsoleInput(), $this->getApplication()->getConsoleOutput());

		$symfonyStyle->title('Cleaning System Cache');

		Factory::getCache()->gc();

		$symfonyStyle->success('Cache cleaned');

		return 0;
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function initialise()
	{
		$this->setName('cache:clean');
		$this->setDescription('Cleans expired cache entries');
		$this->setHelp(<<<EOF
The <info>%command.name%</info> command cleans the system cache of expired entries

<info>php %command.full_name%</info>
EOF
		);
	}
}
