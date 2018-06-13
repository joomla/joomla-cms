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
use Joomla\CMS\Installer\Installer;
use Joomla\Console\AbstractCommand;
use Joomla\CMS\Installer\InstallerHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Console command for checking if there are pending extension updates
 *
 * @since  4.0.0
 */
class ExtensionInstallCommand extends AbstractCommand
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

		$from = $this->cliInput->getArgument('from');

		if ($from === 'path')
		{
			$result = $this->processPathInstallation($this->cliInput->getOption('path'));

			if (!$result)
			{
				$this->ioStyle->error('Unable to install extension');
			}
			else
			{
				$this->ioStyle->success('Extension installed successfully.');
			}
		}
		elseif ($from === 'url')
		{
			$result = $this->processUrlInstallation($this->cliInput->getOption('url'));

			if (!$result)
			{
				$this->ioStyle->error('Unable to install extension');
			}
			else
			{
				$this->ioStyle->success('Extension installed successfully.');
			}
		}
		else
		{
			$this->ioStyle->error('Invalid argument supplied for command.');
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
		$this->setName('extension:install');
		$this->addArgument(
			'from',
			InputArgument::REQUIRED,
			'From where do you want to install? (path OR url)'
		);

		$this->addOption('path', null, InputOption::VALUE_REQUIRED, 'The path to the extension');
		$this->addOption('url', null, InputOption::VALUE_REQUIRED, 'The url to the extension');

		$this->setDescription('Installs an extension from a URL or from a Path.');

		$help = "The <info>%command.name%</info> is used for installing extensions \n 
					--path=<path_to_extension> OR --url=<url_to_download_extension> \n 
					<info>php %command.full_name%</info>";
		
		$this->setHelp($help);
	}

	/**
	 * Used for installing extension from a path
	 *
	 * @param   string  $path  Path to the extension zip file
	 *
	 * @return bool|int
	 *
	 * @since 4.0
	 *
	 * @throws \Exception
	 */
	public function processPathInstallation($path)
	{
		if (!file_exists($path))
		{
			$this->ioStyle->error('The file path specified does not exist.');
			exit(2);
		}

		$tmp_path = Factory::getApplication()->get('tmp_path');
		$tmp_path     = $tmp_path . '/' . basename($path);
		$package  = InstallerHelper::unpack($path, true);

		if ($package['type'] === false)
		{
			return false;
		}

		$jInstaller = Installer::getInstance();
		$result     = $jInstaller->install($package['extractdir']);
		InstallerHelper::cleanupInstall($tmp_path, $package['extractdir']);

		return $result;
	}


	/**
	 * Used for installing extension from a URL
	 *
	 * @param   string  $url  URL to the extension zip file
	 *
	 * @return bool
	 *
	 * @since 4.0
	 *
	 * @throws \Exception
	 */
	public function processUrlInstallation($url)
	{
		$filename = InstallerHelper::downloadPackage($url);

		$tmp_path = Factory::getApplication()->get('tmp_path');

		$path     = $tmp_path . '/' . basename($filename);
		$package  = InstallerHelper::unpack($path, true);

		if ($package['type'] === false)
		{
			return false;
		}

		$jInstaller = Installer::getInstance();
		$result     = $jInstaller->install($package['extractdir']);
		InstallerHelper::cleanupInstall($path, $package['extractdir']);

		return $result;
	}
}
