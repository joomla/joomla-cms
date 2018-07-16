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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Joomla\CMS\Factory;

/**
 * Console command Setting Configuration options
 *
 * @since  4.0.0
 */
class SetConfigurationCommand extends AbstractCommand
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

		$option = $this->cliInput->getArgument('option');

		$config = $this->getApplication()->getConfig();

		$configs = $config->toArray();

		if (!array_key_exists($option, $configs))
		{
			$this->ioStyle->error("Can't find option *$option* in configuration list");

			return 1;
		}

		$value = $this->cliInput->getArgument('value');
		$value = $value === 'false' ? false : $value;
		$value = $value === 'true' ? true : $value;

		$config->set($option, (boolean) $value);
		$config->remove('cwd');
		$config->remove('execution');
		$buffer = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));

		$path = JPATH_CONFIGURATION . '/configuration.php';

		if ($this->writeFile($buffer, $path))
		{
			$this->ioStyle->success('Configuration set');

			return 0;
		}

		return 2;
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
		$this->setName('config:set');
		$this->setDescription('Sets a value for a configuration option');

		$this->addArgument('option', InputArgument::REQUIRED, 'Name of the option');
		$this->addArgument('value', InputArgument::REQUIRED, 'Value of the option');

		$help = "The <info>%command.name%</info> Sets a value for a configuration option
				\nUsage: <info>php %command.full_name%</info> <option> <value>";

		$this->setHelp($help);
	}

	/**
	 * Writes a string to a given file path
	 *
	 * @param   string  $buffer  The string that will be written to the file
	 * @param   string  $path    The path to write the file
	 *
	 * @return boolean
	 *
	 * @since 4.0
	 */
	public function writeFile($buffer, $path)
	{
		// Determine if the configuration file path is writable.
		if (file_exists($path))
		{
			$canWrite = is_writable($path);
		}
		else
		{
			$canWrite = is_writable(JPATH_CONFIGURATION . '/');
		}

		/*
		 * If the file exists but isn't writable OR if the file doesn't exist and the parent directory
		 * is not writable we need to use FTP.
		 */
		$useFTP = false;

		if ((file_exists($path) && !is_writable($path)) || (!file_exists($path) && !is_writable(dirname($path) . '/')))
		{
			return false;
		}

		// Check for safe mode.
		if (ini_get('safe_mode'))
		{
			$useFTP = true;
		}

		// Enable/Disable override.
		if (!isset($options->ftpEnable) || ($options->ftpEnable != 1))
		{
			$useFTP = false;
		}

		if ($canWrite)
		{
			file_put_contents($path, $buffer);
		}
		else
		{
			// If we cannot write the configuration.php, setup fails!
			return false;
		}

		return true;
	}
}