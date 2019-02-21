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
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for installing extensions
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
	 * @param   InputInterface   $input   Console Input
	 * @param   OutputInterface  $output  Console Output
	 * @return void
	 *
	 * @since 4.0
	 *
	 */
	private function configureIO(InputInterface $input, OutputInterface $output)
	{
		$this->cliInput = $input;
		$this->ioStyle = new SymfonyStyle($input, $output);
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function configure()
	{
		$this->setName('extension:install');
		$this->addArgument(
			'from',
			InputArgument::REQUIRED,
			'Where do you want to install from?? (path OR url)'
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
			$this->ioStyle->warning('The file path specified does not exist.');

			return false;
		}

		$tmp_path = $this->getApplication()->get('tmp_path');
		$tmp_path = $tmp_path . '/' . basename($path);
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
	 * @return boolean
	 *
	 * @since 4.0
	 *
	 * @throws \Exception
	 */
	public function processUrlInstallation($url)
	{
		$filename = InstallerHelper::downloadPackage($url);

		$tmp_path = $this->getApplication()->get('tmp_path');

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

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @throws \Exception
	 * @since   __DEPLOY_VERSION__
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$this->configureIO($input, $output);

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
}
