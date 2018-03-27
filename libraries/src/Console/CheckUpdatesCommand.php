<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Updater\Updater;
use Joomla\Console\AbstractCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for checking if there are pending extension updates
 *
 * @since  4.0.0
 */
class CheckUpdatesCommand extends AbstractCommand
{
	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 *
	 * @since   4.0.0
	 */
	public function execute(): int
	{
		$symfonyStyle = new SymfonyStyle($this->getApplication()->getConsoleInput(), $this->getApplication()->getConsoleOutput());

		$symfonyStyle->title('Fetching Extension Updates');

		// Get the update cache time
		$component = ComponentHelper::getComponent('com_installer');

		$cache_timeout = 3600 * (int) $component->getParams()->get('cachetimeout', 6);

		// Find all updates
		Updater::getInstance()->findUpdates(0, $cache_timeout);

		$symfonyStyle->success('Finished fetching updates');

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
		$this->setName('update:extensions:check');
		$this->setDescription('Checks for pending extension updates');
		$this->setHelp(
<<<EOF
The <info>%command.name%</info> command checks for pending extension updates

<info>php %command.full_name%</info>
EOF
		);
	}
}
