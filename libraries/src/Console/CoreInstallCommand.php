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
use Joomla\CMS\Installation\Form\Field\Installation\PrefixField;
use Joomla\CMS\Installation\Helper\DatabaseHelper;
use Joomla\CMS\Installation\Model\ChecksModel;
use Joomla\CMS\Installation\Model\ConfigurationModel;
use Joomla\CMS\Installation\Model\SetupModel;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
	 * The default command name
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected static $defaultName = 'core:install';

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
	const BAD_INPUT_FILE = 3;

	/**
	 * Return code for unsuccessful installation
	 * @since 4.0
	 */
	const INSTALLATION_UNSUCCESSFUL = 4;

	/**
	 * Return code when installation directory cannot be found
	 * @since 4.0
	 */
	const INSTALLATION_DIRECTORY_NOT_FOUND = 5;

	/**
	 * Return code when required options are missing
	 * @since 4.0
	 */
	const INSTALLATION_REQUIRED_OPTION_MISSING = 6;

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

		$this->registry = new Registry;
		$this->cliInput = $input;

		ProgressBar::setFormatDefinition('custom', ' %current%/%max% -- %message%');
		$this->progressBar = new ProgressBar($output, 7);
		$this->progressBar->setFormat('custom');

		$this->ioStyle = new SymfonyStyle($input, $output);
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
			'password' => $options->db_pass_plain,
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
			$this->getApplication()->enqueueMessage(
				Text::sprintf(
					'Check your database credentials, database type, database name or hostname. 
					If you have MySQL 8 installed then please read 
					https://docs.joomla.org/Joomla_and_MySQL_8#Workaround_to_get_Joomla_working_with_MySQL_8 
					for more information.',
					null
				),
				'error'
			);

			return false;
		}
	}

	/**
	 * Handles non-interactive installation
	 *
	 * @param   string   $file      Path to installation
	 * @param   boolean  $validate  Option to validate the data or not
	 *
	 * @since 4.0
	 *
	 * @return array | null
	 */
	public function processNonInteractiveInstallation($file, $validate = true)
	{
		if (!File::exists($file))
		{
			$this->getApplication()->enqueueMessage('Unable to locate the specified file', 'error');

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
		$requiredKeys = array_diff(array_keys($this->getDefaultOptions()), $optionalKeys);
		$providedKeys = array_diff(array_keys($options), $optionalKeys);
		sort($requiredKeys);
		sort($providedKeys);

		if ($requiredKeys != $providedKeys)
		{
			$diff = array_diff($requiredKeys, $providedKeys);
			$remainingKeys = implode(', ', $diff);
			$this->ioStyle->error("These options are required in your file: [$remainingKeys]");

			return;
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
	 * @param   string  $file  Path to ini file
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
	protected function configure(): void
	{
		$this->setDescription('Sets up the Joomla! CMS.');

		$this->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Type of the extension');

		$help = <<<'EOF'
The <info>%command.name%</info> is used for setting up the Joomla! CMS

  <info>php %command.full_name%</info>

To set up Joomla! using an existing configuration file, use the <info>--file</info> option. This may be either a JSON or INI file.

  <info>php %command.full_name% --file=<path></info>
EOF;
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
		$prefix = DatabaseHelper::getPrefix(8);

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
				'question'  => "Enter admin username",
				'type'      => 'question',
				'rules'     => 'isAlphanumeric',
			],
			'admin_password_plain' => [
				'question'  => "Enter admin password",
				'type'      => 'question',
			],
			'db_type' => [
				'question'  => "Select your connection type",
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
			'db_pass_plain' => [
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
				'question'      => "Remove or backup old database",
				'type'          => 'select',
				'optionData'    => ['remove', 'backup'],
				'default'       => 'backup',
			],
			'helpurl' => [
				'question'  => "Help URL",
				'type'      => 'question',
				'default'   => '',
			],
		];
	}

	/**
	 * Defines default options
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	public function getDefaultOptions()
	{
		return [
			'language' => 'en-GB',
			'site_name' => 'Joomla',
			'admin_email' => 'email@example.com',
			'admin_user' => 'user',
			'admin_password' => 'password',
			'admin_password_plain' => 'password',
			'db_type' => 'Mysql',
			'db_host' => 'localhost',
			'db_user' => 'root',
			'db_pass_plain' => '',
			'db_name' => 'test',
			'db_prefix' => 'prefix_',
			'db_old' => 'remove',
			'helpurl' => '',
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

		$options = $this->getDefaultOptions();

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
		$this->progressBar->setMessage("Starting Joomla! installation ...");
		$this->progressBar->start();
		define('JPATH_COMPONENT', JPATH_BASE . '/installation');

		if (file_exists(JPATH_CONFIGURATION . '/configuration.php'))
		{
			$this->progressBar->finish();
			$this->ioStyle->warning("Joomla! is already installed and set up.");

			return self::JOOMLA_ALREADY_SETUP;
		}

		if (!Folder::exists(JPATH_INSTALLATION))
		{
			$this->ioStyle->warning("Installation directory cannot be found.");

			return self::INSTALLATION_DIRECTORY_NOT_FOUND;
		}

		$this->progressBar->advance();
		$this->setup = new SetupModel;
		$this->check = new ChecksModel;

		$this->progressBar->setMessage("Running checks ...");
		$passed = $this->runChecks();

		if (!$passed)
		{
			$this->progressBar->finish();
			$this->ioStyle->warning('These settings are recommended for PHP to ensure full compatibility with Joomla.');
			$this->ioStyle->table(['Label', 'State', 'Notice'], $this->envOptions);

			return self::PHP_OPTIONS_NOT_SET;
		}

		$this->progressBar->advance();
		$file = $this->cliInput->getOption('file');

		if ($file)
		{
			$this->progressBar->setMessage("Loading file ...");
			$result = $this->processNonInteractiveInstallation($file);

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
			$this->getApplication()->getSession()->set('setup.options', $options);

			$this->getApplication()->getSession()->set('setup.options', $options);
			$completed = $model->setup($options);
			$this->progressBar->advance();

			if ($completed)
			{
				$this->progressBar->setMessage("Finishing installation ..." . PHP_EOL);
				$this->progressBar->finish();
				$this->ioStyle->success("Joomla! installation completed successfully!");

				return self::INSTALLATION_SUCCESSFUL;
			}

			$this->progressBar->finish();
			$this->ioStyle->error("Joomla! installation was unsuccessful!");

			return self::INSTALLATION_UNSUCCESSFUL;
		}

		$this->progressBar->finish();

		return self::INSTALLATION_UNSUCCESSFUL;
	}
}
