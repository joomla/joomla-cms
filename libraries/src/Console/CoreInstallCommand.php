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
use Joomla\CMS\Installation\Form\Field\Installation\PrefixField;
use Joomla\CMS\Installation\Helper\DatabaseHelper;
use Joomla\CMS\Installation\Model\ChecksModel;
use Joomla\CMS\Installation\Model\ConfigurationModel;
use Joomla\CMS\Installation\Model\SetupModel;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\Console\AbstractCommand;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Console command for installing the Joomla CMS
 *
 * @since  4.0.0
 */
class CoreInstallCommand extends AbstractCommand
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
	 * SetupModel Object
	 * @var SetupModel
	 * @since 4.0
	 */
	private $setup;

	/**
	 * ChecksModel Object
	 * @var ChecksModel
	 * @since 4.0
	 */
	private $check;

	/**
	 * Environment Options
	 * @var array
	 * @since 4.0
	 */
	private $envOptions;

	/**
	 * Registry Object
	 * @var Registry
	 * @since 4.0
	 */
	private $registry;

	/**
	 * Registry Object
	 * @var ProgressBar
	 * @since 4.0
	 */
	private $progressBar;

	/**
	 * Return code for successful installation
	 * @since 4.0
	 */
	const INSTALLATION_SUCCESSFUL = 0;

	/**
	 * Return code when Joomla! is already installed
	 * @since 4.0
	 */
	const JOOMLA_ALREADY_SETUP = 1;

	/**
	 * Return code when PHP options are not set properly
	 * @since 4.0
	 */
	const PHP_OPTIONS_NOT_SET = 2;

	/**
	 * Return code when file provided is invalid or returns non array when parsed
	 * @since 4.0
	 */
	const BAD_INPUT_FLE = 3;

	/**
	 * Return code for unsuccessful installation
	 * @since 4.0
	 */
	const INSTALLATION_UNSUCCESSFUL = 4;

	/**
	 * Configures the IO
	 *
	 * @return void
	 *
	 * @since 4.0
	 *
	 * @throws null
	 */
	private function configureIO()
	{
		$language = Factory::getLanguage();
		$language->load('', JPATH_INSTALLATION, null, false, false) ||
		$language->load('', JPATH_INSTALLATION, null, true);

		$this->registry = new Registry;
		$this->cliInput = $this->getApplication()->getConsoleInput();

		ProgressBar::setFormatDefinition('custom', ' %current%/%max% -- %message%');
		$this->progressBar = new ProgressBar($this->getApplication()->getConsoleOutput(), 7);
		$this->progressBar->setFormat('custom');

		$this->ioStyle = new SymfonyStyle($this->getApplication()->getConsoleInput(), $this->getApplication()->getConsoleOutput());
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
		$this->progressBar->setMessage("Starting Joomla! installation ...");
		$this->progressBar->start();
		define('JPATH_COMPONENT', JPATH_BASE . '/installation');

		if (file_exists(JPATH_CONFIGURATION . '/configuration.php'))
		{
			$this->progressBar->finish();
			$this->ioStyle->warning("Joomla! is already installed and set up.");

			return self::JOOMLA_ALREADY_SETUP;
		}

		$this->progressBar->advance();
		$this->setup = new SetupModel;
		$this->check = new ChecksModel;

		$this->progressBar->setMessage("Running checks ...");
		$passed = $this->runChecks();

		if (!$passed)
		{
			$this->progressBar->finish();
			$this->ioStyle->warning('Some PHP options are not right. Consider making sure all these are OK before proceeding.');
			$this->ioStyle->table(['Label', 'State', 'Notice'], $this->envOptions);

			return self::PHP_OPTIONS_NOT_SET;
		}

		$this->progressBar->advance();
		$file = $this->cliInput->getOption('file');

		if ($file)
		{
			$this->progressBar->setMessage("Loading file ...");
			$result = $this->processUninteractiveInstallation($file);

			if (!is_array($result))
			{
				$this->progressBar->finish();

				return self::BAD_INPUT_FILE;
			}

			$this->progressBar->setMessage("File loaded");
			$this->progressBar->advance();
			$options = $result;
		}
		else
		{
			$this->progressBar->setMessage("Collecting options ...");
			$options = $this->collectOptions();
		}

		$this->progressBar->setMessage("Checking database connection ...");
		$this->progressBar->advance();
		$validConnection = $this->checkDatabaseConnection($options);
		$this->progressBar->advance();

		if ($validConnection)
		{
			$model = new ConfigurationModel;

			$this->progressBar->setMessage("Writing configuration ...");
			$completed = $model->setup($options);
			$this->progressBar->advance();

			if ($completed)
			{
				$this->progressBar->setMessage("Finishing installation ...");
				$this->progressBar->finish();
				$this->ioStyle->success("Joomla! installation completed successfully!");

				return self::INSTALLATION_SUCCESSFUL;
			}

			$this->progressBar->finish();
			$this->ioStyle->error("Joomla! installation was unsuccessful!");

			return self::INSTALLATION_UNSUCCESSFUL;
		}

		$this->progressBar->finish();

		return INSTALLATION_UNSUCCESSFUL;
	}


	/**
	* Verifies database connection
	*
	* @param   array  $options  Options array
	*
	* @return bool|\Joomla\Database\DatabaseInterface
	*
	* @throws \Exception
	*/
	public function checkDatabaseConnection($options)
	{
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
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_INVALID_TYPE'), 'warning');

			return false;
		}

		// Ensure that a hostname and user name were input.
		if (empty($options->db_host) || empty($options->db_user))
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_INVALID_DB_DETAILS'), 'warning');

			return false;
		}

		// Ensure that a database name was input.
		if (empty($options->db_name))
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_EMPTY_NAME'), 'warning');

			return false;
		}

		// Validate database table prefix.
		if (isset($options->db_prefix) && !preg_match('#^[a-zA-Z]+[a-zA-Z0-9_]*$#', $options->db_prefix))
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_PREFIX_MSG'), 'warning');

			return false;
		}

		// Validate length of database table prefix.
		if (isset($options->db_prefix) && strlen($options->db_prefix) > 15)
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_FIX_TOO_LONG'), 'warning');

			return false;
		}

		// Validate length of database name.
		if (strlen($options->db_name) > 64)
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_NAME_TOO_LONG'), 'warning');

			return false;
		}

		// Workaround for UPPERCASE table prefix for PostgreSQL
		if (in_array($options->db_type, ['pgsql', 'postgresql']))
		{
			if (isset($options->db_prefix) && strtolower($options->db_prefix) !== $options->db_prefix)
			{
				Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_FIX_LOWERCASE'), 'warning');

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

		try
		{
			return DatabaseDriver::getInstance($settings)->connect() !== false;
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage(
				Text::sprintf('Cannot connect to database, verify that you specified the correct database details', null),
				'error'
			);

			return false;
		}
	}

	/**
	 * Handles uninteractive installation
	 *
	 * @param   string   $file      Path to installation
	 * @param   boolean  $validate  Option to validate the data or not
	 *
	 * @since 4.0
	 *
	 * @return array | null
	 */
	public function processUninteractiveInstallation($file, $validate = true)
	{
		if (!File::exists($file))
		{
			$this->getApplication()->enqueueMessage('Unable to locate file specified', 'error');

			return;
		}

		$allowedExtension = ['json', 'ini'];
		$ext = File::getExt($file);

		if (!in_array($ext, $allowedExtension))
		{
			$this->getApplication()->enqueueMessage('The file type specified is not supported');

			return;
		}

		$options = $this->registry->loadFile($file, $ext)->toArray();
		$optionalKeys = ['language', 'helpurl', 'db_old', 'db_prefix'];
		$requiredKeys = array_diff(array_keys($this->getDummyOptions()), $optionalKeys);
		$providedKeys = array_diff(array_keys($options), $optionalKeys);
		sort($requiredKeys);
		sort($providedKeys);

		if ($requiredKeys != $providedKeys)
		{
			$diff = array_diff($requiredKeys, $providedKeys);
			$remainingKeys = implode(', ', $diff);
			$this->ioStyle->error("These options are required in your file: [$remainingKeys]");
			exit;
		}

		array_walk(
			$optionalKeys, function ($value, $key) use (&$options) {
				if (!isset($options[$value]))
				{
					switch ($value)
					{
						case 'db_prefix':
							$options[$value] = (new PrefixField)->getPrefix();
							break;
						case 'db_old':
							$options[$value] = 'backup';
							break;
						default:
							$options[$value] = '';
							break;
					}
				}
			}
		);

		if ($validate)
		{
			$validator = $this->validate($options);

			return $validator ? $options : null;
		}


		return $options;

	}

	/**
	 * Display enqueued messages by application
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function outputEnqueuedMessages()
	{
		$messages = $this->getApplication()->getMessageQueue();

		foreach ($messages as $k => $message)
		{
			$this->displayMessage($message[0]);
		}
	}

	/**
	 * Parse an INI file
	 *
	 * @param   string  $file  Path fo ini file
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	public function parseIniFile($file)
	{
		$disabledFunctions = explode(',', ini_get('disable_functions'));
		$isParseIniFileDisabled = in_array('parse_ini_file', array_map('trim', $disabledFunctions));

		if (!function_exists('parse_ini_file') || $isParseIniFileDisabled)
		{
			$contents = file_get_contents($file);
			$contents = str_replace('"_QQ_"', '\\"', $contents);
			$options  = @parse_ini_string($contents, INI_SCANNER_RAW);
		}
		else
		{
			$options = @parse_ini_file($file);
		}

		if (!is_array($options))
		{
			$options = array();
		}

		return $options;
	}

	/**
	 * Performs environment checks before installation
	 *
	 * @return boolean
	 *
	 * @since 4.0
	 */
	public function runChecks()
	{
		$pass = $this->check->getPhpOptionsSufficient();

		if ($pass)
		{
			return true;
		}

		$phpoptions = $this->check->getPhpOptions();

		foreach ($phpoptions as $option)
		{
			$option->notice = $option->notice ? $option->notice : "OK";
			$options[] = (array) $option;
		}

		$this->envOptions = $options;

		return false;
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

		$this->setDescription('Sets up the Joomla! CMS.');

		$this->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Type of the extension');

		$help = "The <info>%command.name%</info> is used for setting up the Joomla! CMS \n 
					<info>php %command.full_name%</info> --file=<path to config file> [JSON and INI supported]
					<info>php %command.full_name%</info> -f <path to config file> [JSON and INI supported]";

		$this->setHelp($help);
	}


	/**
	 * Retrieves options Template
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	public function getOptionsTemplate()
	{
		$drivers = array_map('strtolower', DatabaseDriver::getConnectors());
		$prefix = (new PrefixField)->getPrefix();

		return [
			'language' => [
				'question'      => "Site Language",
				'type'          => 'select',
				'optionData'    => ['en-GB', 'en-US'],
				'default'       => 'en-GB',
			],
			'site_name' => [
				'question'  => "What's the name of your website",
				'type'      => 'question',
			],
			'admin_email' => [
				'question'  => "Enter admin email",
				'type'      => 'question',
				'rules'     => 'isEmail',
			],
			'admin_user' => [
				'question'  => "Enter Admin username",
				'type'      => 'question',
				'rules'     => 'isAlphanumeric',
			],
			'admin_password' => [
				'question'  => "Enter admin password",
				'type'      => 'question',
			],
			'db_type' => [
				'question'  => "What's your connection type",
				'type'      => 'select',
				'optionData'    => $drivers,
				'default'   => 'mysqli',
			],
			'db_host' => [
				'question'  => "Enter database host",
				'type'      => 'question',
			],
			'db_user' => [
				'question'  => "Enter database user",
				'type'      => 'question',
			],
			'db_pass' => [
				'question'  => "Enter database password",
				'type'      => 'question',
				'default'   => null,
			],
			'db_name' => [
				'question'  => "Enter database name",
				'type'      => 'question',
			],
			'db_prefix' => [
				'question'  => "Database prefix",
				'type'      => 'question',
				'default'   => $prefix,
			],
			'db_old' => [
				'question'      => "What do you want to do about old DB",
				'type'          => 'select',
				'optionData'    => ['remove', 'backup'],
				'default'       => 'backup',
			],
			'helpurl' => [
				'question'  => "Help URL",
				'type'      => 'question',
				'default'   => 'https://joomla.org',
			],
		];
	}

	/**
	 * Defines dummy options
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	public function getDummyOptions()
	{
		return [
			'language' => 'en-GB',
			'site_name' => 'Joomla',
			'admin_email' => 'email@example.com',
			'admin_user' => 'user',
			'admin_password' => 'password',
			'db_type' => 'Mysql',
			'db_host' => 'localhost',
			'db_user' => 'root',
			'db_pass' => '',
			'db_name' => 'test',
			'db_prefix' => 'prefix_',
			'db_old' => 'remove',
			'helpurl' => 'https://joomla.org',
		];
	}

	/**
	 * Retrieves options from user inputs
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	private function collectOptions()
	{
		$data = $this->getOptionsTemplate();

		$options = $this->getDummyOptions();

		foreach ($data as $key => $value)
		{
			$valid = false;

			while (!$valid)
			{
				$val = $this->processType($value);
				$options[$key] = $val;

				$validator = $this->validate($options);

				if (!$validator)
				{
					$this->outputEnqueuedMessages();
				}
				else
				{
					$valid = true;
				}
			}
		}

		return $options;
	}

	/**
	 * Displays an error Message
	 *
	 * @param   string  $message  Message to be displayed
	 *
	 * @return void
	 *
	 * @since 4.0
	 */
	public function displayMessage($message)
	{
		$this->ioStyle->error(Text::_($message));
	}

	/**
	 * Process a console input type
	 *
	 * @param   array  $data  The option template
	 *
	 * @return mixed
	 *
	 * @since 4.0
	 */
	private function processType($data)
	{
		$default = $data['default'] ?? null;

		switch ($data['type'])
		{
			case 'question':
				$placeholder = \uniqid("placeholder");
				$value = $this->ioStyle->ask(
					$data['question'],
					$default,
					function ($string) use ($placeholder) {
						return (null == $string) ? $placeholder : $string;
					}
				);

				return str_replace($placeholder, null, $value);
				break;

			case 'select':
				return $this->ioStyle->choice($data['question'], $data['optionData'], $default);
				break;
		}
	}

	/**
	 * Validates the given Data
	 *
	 * @param   array  $data  Data to be validated
	 *
	 * @return array | boolean
	 *
	 * @since 4.0
	 */
	public function validate($data)
	{
		return $this->setup->validate($data);
	}
}
