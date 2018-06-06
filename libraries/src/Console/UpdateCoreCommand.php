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
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;


/**
 * Console command for checking if there are pending extension updates
 *
 * @since  4.0.0
 */
class UpdateCoreCommand extends AbstractCommand
{
	/**
	 * Stores the Input Object
	 * @var
	 * @since 4.0
	 */
	private $cliInput;

	/**
	 * SymfonyStyle Object
	 * @var
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

		$action = $this->cliInput->getArgument('action');
		if ($action == 'update')
		{
			if ($this->updateJoomlaCore())
			{
				$this->ioStyle->success('Joomla Core Updated Successfuly.');
			}
			else
			{
				$this->ioStyle->note('No Joomla update is available');
			}
		}
		else
		{
			$this->ioStyle->error("Action not recognized");
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
		$this->setName('core');
		$this->setDescription('Updates Joomla Core');

		$this->addArgument(
			'action',
			InputArgument::REQUIRED,
			'The action you want to perform on the core. e.g update'
		);

		$help = "The <info>%command.name%</info> Updates the Joomla Core \n <info>php %command.full_name%</info>";
		$this->setHelp($help);
	}

	/**
	 * Update Core Joomla
	 *
	 * @return  bool  success
	 *
	 * @since 4.0
	 */
	private function updateJoomlaCore()
	{
		$app = Factory::getApplication();
		$updatemodel = $app->bootComponent('com_joomlaupdate')->createMVCFactory($app)->createModel('Update', 'Administrator');

		$updatemodel->purge();

		$updatemodel->refreshUpdates(true);

		$updateInformation = $updatemodel->getUpdateInformation();

		if (!empty($updateInformation['hasUpdate']))
		{
			$packagefile = InstallerHelper::downloadPackage($updateInformation['object']->downloadurl->_data);
			$tmp_path    = $app->get('tmp_path');
			$packagefile = $tmp_path . '/' . $packagefile;
			$package     = InstallerHelper::unpack($packagefile, true);
			\JFolder::copy($package['extractdir'], JPATH_BASE, '', true);

			$result = $updatemodel->finaliseUpgrade();

			if ($result)
			{
				// Remove the xml
				if (file_exists(JPATH_BASE . '/joomla.xml'))
				{
					\JFile::delete(JPATH_BASE . '/joomla.xml');
				}

				InstallerHelper::cleanupInstall($packagefile, $package['extractdir']);

				return true;
			}
		}

		return false;
	}
}
