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
use Joomla\CMS\Installation\Model\ConfigurationModel;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Console\AbstractCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for installing the Joomla CMS
 *
 * @since  4.0.0
 */
class CoreInstallCommand extends AbstractCommand
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
//		$site_name = $this->ioStyle->ask('What is the name of your website?');
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

		$model = new ConfigurationModel;

		$options['site_name'] = $this->ioStyle->ask('What is the name of your website?');
		$options['admin_user'] = $this->ioStyle->ask('Username?');
		$options['admin_password'] = $this->ioStyle->ask('Password?');
		$options['admin_email'] = $this->ioStyle->ask('Email?');
		$options['db_type'] = 'mysql';
		$options['db_host'] =  $this->ioStyle->ask('Database Host?');
		$options['db_user'] =  $this->ioStyle->ask('Database Username?');
		$options['db_pass'] =  $this->ioStyle->ask('Database Password?');
		$options['db_name'] =  $this->ioStyle->ask('Database Name?');
		$options['db_prefix'] = 'lmao_';
		$options['helpurl'] = 'http://joomla.org';
		$options['db_old'] = 'remove';
		$options['language'] = 'en-GB';
		$model->setup($options);

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
		$this->setName('core:install');

		$this->setDescription('Sets up the joomla CMS.');

		$help = "The <info>%command.name%</info> is used for setting up the Joomla CMS \n 
					<info>php %command.full_name%</info>";

		$this->setHelp($help);
	}

	public function checkCompatibility()
	{
		//
	}
}
