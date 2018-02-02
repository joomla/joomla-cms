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
 * Console command for removing files which should have been cleared during an update
 *
 * @since  4.0.0
 */
class RemoveOldFilesCommand extends AbstractCommand
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

		$symfonyStyle->title('Removing Old Files');

		// Import the dependencies
		\JLoader::import('joomla.filesystem.file');
		\JLoader::import('joomla.filesystem.folder');

		// We need the update script
		\JLoader::register('JoomlaInstallerScript', JPATH_ADMINISTRATOR . '/components/com_admin/script.php');

		(new \JoomlaInstallerScript)->deleteUnexistingFiles();

		$symfonyStyle->success('Files removed');

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
		$this->setName('update:joomla:remove-old-files');
		$this->setDescription('Removes old system files');
		$this->setHelp(
<<<EOF
The <info>%command.name%</info> command removes old files which should have been deleted during a Joomla update

<info>php %command.full_name%</info>
EOF
		);
	}
}
