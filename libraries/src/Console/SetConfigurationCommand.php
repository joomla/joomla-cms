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
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command Setting Configuration options
 *
 * @since  4.0.0
 */
class SetConfigurationCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected static $defaultName = 'config:set';

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
	 * Options Array
	 * @var array
	 * @since 4.0
	 */
	private $options;


	/**
	 * Return code if configuration is set successfully
	 * @since 4.0
	 */
	const CONFIG_SET_SUCCESSFUL = 0;

	/**
	 * Return code if configuration set failed
	 * @since 4.0
	 */
	const CONFIG_SET_FAILED = 3;

	/**
	 * Return code if database validation failed
	 * @since 4.0
	 */
	const DB_VALIDATION_FAILED = 1;

	/**
	 * Return code if config validation failed
	 * @since 4.0
	 */
	const CONFIG_VALIDATION_FAILED = 2;

	/**
	 * Configures the IO
	 *
	 * @param   InputInterface   $input   Console Input
	 * @param   OutputInterface  $output  Console Output
	 *
	 * @return void
	 *
	 * @since 4.0
	 *
	 */
	private function configureIO(InputInterface $input, OutputInterface $output)
	{
		$language = Factory::getLanguage();
		$language->load('', JPATH_INSTALLATION, null, false, false) ||
		$language->load('', JPATH_INSTALLATION, null, true);
		$this->cliInput = $input;
		$this->ioStyle = new SymfonyStyle($input, $output);
	}


	/**
	 * Collects options from user input
	 *
	 * @param   array  $options  Options inputed by users
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	public function retrieveOptionsFromInput($options)
	{
		$collected = [];

		foreach ($options as $option)
		{
			if (strpos($option, '=') === false)
			{
				$this->ioStyle
					->error('Options and values should be separated by "="');
				exit;
			}

			list($option, $value) = explode('=', $option);

			$collected[$option] = $value;
		}

		return $collected;
	}


	/**
	 * Validates the options provided
	 *
	 * @param   array  $options  Options Array
	 *
	 * @return mixed
	 *
	 * @since 4.0
	 */
	public function validateOptions($options)
	{
		$config = $this->getInitialConfigurationOptions();

		$configs = $config->toArray();

		$valid = true;
		array_walk(
			$options, function ($value, $key) use ($configs, &$valid) {
				if (!array_key_exists($key, $configs))
				{
					$this->getApplication()
						->enqueueMessage(
							"Can't find option *$key* in configuration list",
							'error'
						);

					$valid = false;
				}
			}
		);

		return $valid;
	}

	/**
	 * Sets the options array
	 *
	 * @param   string  $options  Options string
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function setOptions($options)
	{
		$this->options = explode(' ', $options);
	}

	/**
	 * Collects the options array
	 *
	 * @return array|mixed
	 *
	 * @since 4.0
	 */
	public function getOptions()
	{
		if ($this->options)
		{
			return $this->options;
		}

		return $this->cliInput->getArgument('options');
	}

	/**
	 * Returns Default configuration Object
	 *
	 * @return Registry
	 *
	 * @since 4.0
	 */
	public function getInitialConfigurationOptions(): Registry
	{
		return (new Registry(new \JConfig));
	}


	/**
	 * Save the configuration file
	 *
	 * @param   array  $options  Collected options
	 *
	 * @return boolean
	 *
	 * @since 4.0
	 */
	public function saveConfiguration($options)
	{
		$config = $this->getInitialConfigurationOptions();

		foreach ($options as $key => $value)
		{
			$value = $value === 'false' ? false : $value;
			$value = $value === 'true' ? true : $value;

			$config->set($key, $value);
		}

		$config->remove('cwd');
		$config->remove('execution');
		$buffer = $config->toString(
			'PHP',
			array('class' => 'JConfig', 'closingtag' => false)
		);

		$path = JPATH_CONFIGURATION . '/configuration.php';

		if ($this->writeFile($buffer, $path))
		{
			return true;
		}

		return false;
	}

	/**
	 * Initialise the command.
	 *
	 * @return void
	 *
	 * @since 4.0
	 */
	protected function configure(): void
	{
		$this->setDescription('Sets a value for a configuration option');

		$this->addArgument(
			'options',
			InputArgument::REQUIRED | InputArgument::IS_ARRAY,
			'All options you want to set'
		);

		$help = "The <info>%command.name%</info> 
				Sets a value for a configuration option
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
		$options = $this->getApplication()->getConfig();

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

		return $canWrite ? File::write($path, $buffer) : false;
	}


	/**
	 * Verifies database connection
	 *
	 * @param   array  $options  Options array
	 *
	 * @return bool|\Joomla\Database\DatabaseInterface
	 *
	 * @since 4.0
	 * @throws \Exception
	 */
	public function checkDb($options)
	{
		$options = [
			'db_type' => $options['dbtype'],
			'db_host' => $options['host'],
			'db_prefix' => $options['dbprefix'],
			'db_name' => $options['db'],
			'db_pass' => $options['password'],
			'db_user' => $options['user'],
		];


		// Get the options as an object for easier handling.
		$options = ArrayHelper::toObject($options);

		// Load the backend language files so that the DB error messages work.
		$lang = Factory::getLanguage();
		$currentLang = $lang->getTag();

		// Load the selected language
		if (LanguageHelper::exists($currentLang, JPATH_ADMINISTRATOR))
		{
			$lang->load('joomla', JPATH_ADMINISTRATOR, $currentLang, true);
		}
		// Pre-load en-GB in case the chosen language files do not exist.
		else
		{
			$lang->load('joomla', JPATH_ADMINISTRATOR, 'en-GB', true);
		}

		// Ensure a database type was selected.
		if (empty($options->db_type))
		{
			$this->getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_INVALID_TYPE'), 'warning');

			return false;
		}

		// Ensure that a hostname and user name were input.
		if (empty($options->db_host) || empty($options->db_user))
		{
			$this->getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_INVALID_DB_DETAILS'), 'warning');

			return false;
		}

		// Ensure that a database name was input.
		if (empty($options->db_name))
		{
			$this->getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_EMPTY_NAME'), 'warning');

			return false;
		}

		// Validate database table prefix.
		if (isset($options->db_prefix) && !preg_match('#^[a-zA-Z]+[a-zA-Z0-9_]*$#', $options->db_prefix))
		{
			$this->getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_PREFIX_MSG'), 'warning');

			return false;
		}

		// Validate length of database table prefix.
		if (isset($options->db_prefix) && strlen($options->db_prefix) > 15)
		{
			$this->getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_FIX_TOO_LONG'), 'warning');

			return false;
		}

		// Validate length of database name.
		if (strlen($options->db_name) > 64)
		{
			$this->getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_NAME_TOO_LONG'), 'warning');

			return false;
		}

		// Workaround for UPPERCASE table prefix for PostgreSQL
		if (in_array($options->db_type, ['pgsql', 'postgresql']))
		{
			if (isset($options->db_prefix) && strtolower($options->db_prefix) !== $options->db_prefix)
			{
				$this->getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_FIX_LOWERCASE'), 'warning');

				return false;
			}
		}

		// Build the connection options array.
		$settings = [
			'driver'   => $options->db_type,
			'host'     => $options->db_host,
			'user'     => $options->db_user,
			'password' => $options->db_pass,
			'database' => $options->db_name,
			'prefix'   => $options->db_prefix,
			'select'   => isset($options->db_select) ? $options->db_select : false
		];

		// Get a database object.
		try
		{
			return DatabaseDriver::getInstance($settings)->connect();
		}
		catch (\RuntimeException $e)
		{
			$this->getApplication()->enqueueMessage(
				Text::sprintf('Cannot connect to database, verify that you specified the correct database details', null),
				'error'
			);

			return false;
		}
	}

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws \Exception
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$this->configureIO($input, $output);

		$options = $this->getOptions();

		$options = $this->retrieveOptionsFromInput($options);

		$valid = $this->validateOptions($options);


		if (!$valid)
		{
			return self::CONFIG_VALIDATION_FAILED;
		}

		$initialOptions = $this->getInitialConfigurationOptions()->toArray();

		$combinedOptions = array_merge($initialOptions, $options);

		$db = $this->checkDb($combinedOptions);

		if ($db === false)
		{
			return self::DB_VALIDATION_FAILED;
		}

		if ($this->saveConfiguration($options))
		{
			$this->options ?: $this->ioStyle->success('Configuration set');

			return self::CONFIG_SET_SUCCESSFUL;
		}

		return self::CONFIG_SET_FAILED;
	}
}
