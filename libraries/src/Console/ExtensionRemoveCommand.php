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
		$extension_id = $this->cliInput->getArgument('extension_id');
		$response = $this->ioStyle->ask('Are you sure you want to remove this extension?', 'yes/no');
		if ($response == 'yes')
		{
			if ($this->removeExtension($extension_id))
			{
				$this->ioStyle->success('Extension Removed!');
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
	 * Removes the extension
	 *
	 * @param   integer  $extension_id  ID of extension to be removed
	 *
	 * @return bool
	 *
	 * @since 4.0
	 */
	protected function removeExtension($extension_id)
	{
		$id = (int) $extension_id;
		$result = true;

		$installer = \JInstaller::getInstance();
		$row       = \JTable::getInstance('extension');
		if (!$row->load($id))
		{
			$this->ioStyle->error("Extension with ID of $extension_id not found.");
			return false;
		}

		if ($row->type && $row->type != 'language')
		{
			$result = $installer->uninstall($row->type, $id);
		}
		return $result;
	}
}
