<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Table\Table;
use Joomla\Console\AbstractCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;


/**
 * Console command for checking if there are pending extension updates
 *
 * @since  4.0.0
 */
class ExtensionRemoveCommand extends AbstractCommand
{
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
		$extension_id = (int) $this->cliInput->getArgument('extension_id');

		$extension = $this->getExtension($extension_id);

		if (!$extension->load($extension_id))
		{
			$this->ioStyle->error("Extension with ID of $extension_id not found.");
			return 0;
		}

		$response = $this->ioStyle->ask('Are you sure you want to remove this extension?', 'yes/no');

		if ($response == 'yes')
		{
			if ($extension->type && $extension->type != 'language')
			{
				$installer = Installer::getInstance();
				$result    = $installer->uninstall($extension->type, $extension_id);
				if ($result)
				{
					$this->ioStyle->success('Extension Removed!');
				}
			}
		}
		elseif ($response == 'no')
		{
			$this->ioStyle->note('Extension Not Removed.');
			return 0;
		}
		else
		{
			$this->ioStyle->warning('Invalid Response');
			return 2;
		}
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
		$this->setName('extension:remove');
		$this->addArgument(
			'extension_id',
			InputArgument::REQUIRED,
			'ID of extension to be Removed (Run extension:list command to check)'
		);

		$this->setDescription('Removes an Extension');

		$help = "The <info>%command.name%</info> Removes an extension \n <info>php %command.full_name%</info>";
		$this->setHelp($help);
	}

	/**
	 * Gets the extension from DB
	 *
	 * @param   integer  $extension_id  ID of extension to be removed
	 *
	 * @return bool
	 *
	 * @since 4.0
	 */
	protected function getExtension($extension_id)
	{
		$row       = Table::getInstance('extension');
		return $row;
	}
}
