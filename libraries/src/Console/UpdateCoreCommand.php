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
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\Console\AbstractCommand;
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
	 * @var CliInput
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
	 * Update Information
	 * @var array
	 * @since 4.0
	 */
	public $updateInfo;

	/**
	 * Update Model
	 * @var array
	 * @since 4.0
	 */
	public $updateModel;

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
	 * Run Checks after Update
	 *
	 * @return boolean
	 *
	 * @since 4.0
	 *
	 * @throws \Exception
	 */
	public function runChecks()
	{
		unset($this->updateModel);
		$this->getUpdateModel()->purge();
		$this->getUpdateModel()->refreshUpdates();

		$updateInfo = $this->getUpdateModel()->getUpdateInformation();

		if (JVERSION !== $updateInfo['latest'])
		{
			return false;
		}


		if ($updateInfo['hasUpdate'])
		{
			return false;
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('version');
		$query->from('#__updates');
		$query->where(['element = "joomla"']);
		$db->setQuery((string) $query);
		$update = $db->loadObjectList();
		$update = count($update) > 0 ? $update : null;

		if ($update && $update[0]->version !== $this->updateInfo['latest'])
		{
			return false;
		}

		return true;
	}


	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 *
	 * @since   4.0.0
	 *
	 * @throws null
	 */
	public function execute(): int
	{
		$this->configureIO();

		$model = $this->getUpdateModel();

		$this->setUpdateInfo($model->getUpdateInformation());

		if ($this->runChecks())
		{
			$this->ioStyle->note('You already have the latest Joomla! version.');

			return 1;
		}

		if ($this->updateJoomlaCore($model) && $this->runChecks())
		{
			$this->ioStyle->success('Joomla core updated successfully.');

			return 0;
		}
		else
		{
			$this->ioStyle->note('Update cannot be performed.');

			return 2;
		}
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
		$this->setName('core:update');
		$this->setDescription('Updates joomla core');

		$help = "The <info>%command.name%</info> Updates the Joomla core \n <info>php %command.full_name%</info>";

		$this->setHelp($help);
	}

	/**
	 * Update Core Joomla
	 *
	 * @param   mixed  $updatemodel  Update Model
	 *
	 * @return  boolean  success
	 *
	 * @since 4.0
	 */
	private function updateJoomlaCore($updatemodel)
	{
		$updateInformation = $this->updateInfo;

		if (!empty($updateInformation['hasUpdate']))
		{
			$package = $this->processUpdatePackage($updateInformation);

			$result = $updatemodel->finaliseUpgrade();

			if ($result)
			{
				// Remove the xml
				if (file_exists(JPATH_BASE . '/joomla.xml'))
				{
					File::delete(JPATH_BASE . '/joomla.xml');
				}

				InstallerHelper::cleanupInstall($package['file'], $package['extractdir']);

				return true;
			}
		}

		return false;
	}

	/**
	 * Sets the update Information
	 *
	 * @param   array  $data  Stores the update information
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function setUpdateInfo($data)
	{
		$this->updateInfo = $data;
	}

	/**
	 * Retrieves the Update model from com_joomlaupdate
	 *
	 * @return mixed
	 *
	 * @since 4.0
	 *
	 * @throws \Exception
	 */
	public function getUpdateModel()
	{
		if (!isset($this->updateModel))
		{
			$this->setUpdateModel();
		}

		return $this->updateModel;
	}

	/**
	 * Sets the Update Model
	 *
	 * @return void
	 *
	 * @since 4.0
	 */
	public function setUpdateModel()
	{
		$app = $this->getApplication();
		$updatemodel = $app->bootComponent('com_joomlaupdate')->createMVCFactory($app)->createModel('Update', 'Administrator');

		if (is_bool($updatemodel))
		{
			$this->updateModel = $updatemodel;

			return;
		}

		$updatemodel->purge();
		$updatemodel->refreshUpdates(true);

		$this->updateModel = $updatemodel;
	}

	/**
	 * Downloads and extracts the update Package
	 *
	 * @param   array  $updateInformation  Stores the update information
	 *
	 * @return array | boolean
	 *
	 * @since 4.0
	 */
	public function processUpdatePackage($updateInformation)
	{
		if (!$updateInformation['object'])
		{
			return false;
		}

		$file = $this->downloadFile($updateInformation['object']->downloadurl->_data);

		$tmpPath    = $this->getApplication()->get('tmp_path');
		$updatePackage = $tmpPath . '/' . $file;

		$package = $this->extractFile($updatePackage);

		$this->copyFileTo($package['extractdir'], JPATH_BASE);

		return ['file' => $updatePackage, 'extractdir' => $package['extractdir']];
	}

	/**
	 * Downloads the Update file
	 *
	 * @param   string  $url  URL to update file
	 *
	 * @return boolean | string
	 *
	 * @since 4.0
	 */
	public function downloadFile($url)
	{
		$file = InstallerHelper::downloadPackage($url);

		if (!$file)
		{
			return false;
		}

		return $file;
	}

	/**
	 * Extracts Update file
	 *
	 * @param   string  $file  Full path to file location
	 *
	 * @return array | boolean
	 *
	 * @since 4.0
	 */
	public function extractFile($file)
	{
		$package = InstallerHelper::unpack($file, true);

		return $package;
	}

	/**
	 * Copy a file to a destination directory
	 *
	 * @param   string  $file  Full path to file
	 * @param   string  $dir   Destination directory
	 *
	 * @return void
	 *
	 * @since 4.0
	 */
	public function copyFileTo($file, $dir)
	{
		Folder::copy($file, $dir, '', true);
	}
}
