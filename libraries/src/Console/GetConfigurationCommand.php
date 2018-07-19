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
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for displaying configuration options
 *
 * @since  4.0.0
 */
class GetConfigurationCommand extends AbstractCommand
{
	/**
	 * Stores the Input Object
	 * @var Input
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

		$configs = $this->formatConfig($this->getApplication()->getConfig()->toArray());

		$option = $this->cliInput->getArgument('option');
		$group = $this->cliInput->getOption('group');

		if ($group)
		{
			return $this->processGroupOptions($group);
		}

		if ($option)
		{
			return $this->processSingleOption($option);
		}

		if (!$option && !$group)
		{
			$options = [];
			array_walk($configs, function ($value, $key) use (&$options) {
					$options[] = [$key, $value];
			    }
			);

			$this->ioStyle->title("Current options in Configuration");
			$this->ioStyle->table(['Option', 'Value'], $options);

			return 0;
		}

		return 1;
	}

	/**
	 * Displays logically grouped options
	 *
	 * @param   string  $group  The group to be processed
	 *
	 * @return integer
	 *
	 * @since 4.0
	 */
	public function processGroupOptions($group)
	{
		$configs = $this->getApplication()->getConfig()->toArray();
		$configs = $this->formatConfig($configs);

		switch ($group)
		{
			case 'db':
				$options[] = ['dbtype', $configs['dbtype']];
				$options[] = ['host', $configs['host']];
				$options[] = ['user', $configs['user']];
				$options[] = ['password', $configs['password']];
				$options[] = ['db', $configs['db']];
				$options[] = ['dbprefix', $configs['dbprefix']];
				break;

			case 'mail':
				$options[] = ['mailonline', $configs['mailonline']];
				$options[] = ['mailer', $configs['mailer']];
				$options[] = ['mailfrom', $configs['mailfrom']];
				$options[] = ['fromname', $configs['fromname']];
				$options[] = ['sendmail', $configs['sendmail']];
				$options[] = ['smtpauth', $configs['smtpauth']];
				$options[] = ['smtpuser', $configs['smtpuser']];
				$options[] = ['smtppass', $configs['smtppass']];
				$options[] = ['smtphost', $configs['smtphost']];
				$options[] = ['smtpsecure', $configs['smtpsecure']];
				$options[] = ['smtpport', $configs['smtpport']];
				break;

			case 'session':
				$options[] = ['session_handler', $configs['session_handler']];
				$options[] = ['shared_session', $configs['shared_session']];
				$options[] = ['session_metadata', $configs['session_metadata']];
				break;

			default:
				$this->ioStyle->error('Group not found, available groups are: db, mail, session');

				return 1;
				break;
		}

		$this->ioStyle->table(['Option', 'Value'], $options);

		return 0;
	}

	/**
	 * Formats the configuration array into desired format
	 *
	 * @param   array  $configs  Array of the configurations
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	public function formatConfig($configs)
	{
		foreach ($configs as $key => $config)
		{
			$config = $config === false ? "false" : $config;
			$config = $config === true ? "true" : $config;

			if (!in_array($key, ['cwd', 'execution']))
			{
				$newConfig[$key] = $config;
			}
 		}

		return $newConfig;
	}

	/**
	 * Handles the command when an single option is requested
	 *
	 * @param   string  $option  The option we want to get its value
	 *
	 * @return integer
	 *
	 * @since 4.0
	 */
	public function processSingleOption($option)
	{
		$configs = $this->getApplication()->getConfig()->toArray();

		if (!array_key_exists($option, $configs))
		{
			$this->ioStyle->error("Can't find option *$option* in configuration list");

			return 1;
		}

		$value = $this->getApplication()->get($option) ?: 'Not set';

		$this->ioStyle->table(['Option', 'Value'], [[$option, $value]]);

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
		$this->setName('config:get');
		$this->setDescription('Displays the current value of a configuration option');

		$this->addArgument('option', null, 'Name of the option');
		$this->addOption('group', 'g', InputOption::VALUE_REQUIRED, 'Name of the option');

		$help = "The <info>%command.name%</info> Displays the current value of a configuration option
				\nUsage: <info>php %command.full_name%</info> <option>";

		$this->setHelp($help);
	}
}
