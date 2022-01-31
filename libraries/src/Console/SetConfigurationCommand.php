<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
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
	 * @since  4.0.0
	 */
	protected static $defaultName = 'config:set';

	/**
	 * Stores the Input Object
	 * @var Input
	 * @since 4.0.0
	 */
	private $cliInput;

	/**
	 * SymfonyStyle Object
	 * @var SymfonyStyle
	 * @since 4.0.0
	 */
	private $ioStyle;

	/**
	 * Options Array
	 * @var array
	 * @since 4.0.0
	 */
	private $options;


	/**
	 * Return code if configuration is set successfully
	 * @since 4.0.0
	 */
	public const CONFIG_SET_SUCCESSFUL = 0;

	/**
	 * Return code if configuration set failed
	 * @since 4.0.0
	 */
	public const CONFIG_SET_FAILED = 1;

	/**
	 * Return code if config validation failed
	 * @since 4.0.0
	 */
	public const CONFIG_VALIDATION_FAILED = 2;

	/**
	 * Return code if options are wrong
	 * @since 4.0.0
	 */
	public const CONFIG_OPTIONS_WRONG = 3;

	/**
	 * Return code if database validation failed
	 * @since 4.0.0
	 */
	public const DB_VALIDATION_FAILED = 4;

	/**
	 * Configures the IO
	 *
	 * @param   InputInterface   $input   Console Input
	 * @param   OutputInterface  $output  Console Output
	 *
	 * @return void
	 *
	 * @since 4.0.0
	 *
	 */
	private function configureIO(InputInterface $input, OutputInterface $output)
	{
		$language = Factory::getLanguage();
		$language->load('', JPATH_INSTALLATION, null, false, false) ||
		$language->load('', JPATH_INSTALLATION, null, true);
		$language->load('com_config', JPATH_ADMINISTRATOR, null, false, false)||
		$language->load('com_config', JPATH_ADMINISTRATOR, null, true);
		$this->cliInput = $input;
		$this->ioStyle = new SymfonyStyle($input, $output);
	}

	/**
	 * Collects options from user input
	 *
	 * @param   array  $options  Options input by users
	 *
	 * @return boolean
	 *
	 * @since 4.0.0
	 */
	private function retrieveOptionsFromInput(array $options): bool
	{
		$collected = [];

		foreach ($options as $option)
		{
			if (strpos($option, '=') === false)
			{
				$this->ioStyle->error('Options and values should be separated by "="');

				return false;
			}

			list($option, $value) = explode('=', $option);

			$collected[$option] = $value;
		}

		$this->options = $collected;

		return true;
	}

	/**
	 * Validates the options provided
	 *
	 * @return boolean
	 *
	 * @since 4.0.0
	 */
	private function validateOptions(): bool
	{
		$config = $this->getInitialConfigurationOptions();

		$configs = $config->toArray();

		$valid = true;
		array_walk(
			$this->options, function ($value, $key) use ($configs, &$valid) {
				if (!array_key_exists($key, $configs))
				{
					$this->ioStyle->error("Can't find option *$key* in configuration list");
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
	 * @since 4.0.0
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
	 * @since 4.0.0
	 */
	public function getOptions()
	{
		return $this->cliInput->getArgument('options');
	}

	/**
	 * Returns Default configuration Object
	 *
	 * @return Registry
	 *
	 * @since 4.0.0
	 */
	public function getInitialConfigurationOptions(): Registry
	{
		return (new Registry(new \JConfig));
	}


	/**
	 * Save the configuration file
	 *
	 * @param   array  $options  Options array
	 *
	 * @return boolean
	 *
	 * @since 4.0.0
	 */
	public function saveConfiguration($options): bool
	{
		$app = $this->getApplication();

		// Check db connection encryption properties
		$model = $app->bootComponent('com_config')->getMVCFactory($app)->createModel('Application', 'Administrator');

		if (!$model->save($options))
		{
			$this->ioStyle->error(Text::_('Failed to save properties'));

			return false;
		}

		return true;
	}

	/**
	 * Initialise the command.
	 *
	 * @return void
	 *
	 * @since 4.0.0
	 */
	protected function configure(): void
	{
		$this->addArgument(
			'options',
			InputArgument::REQUIRED | InputArgument::IS_ARRAY,
			'All options you want to set'
		);

		$help = "<info>%command.name%</info> sets the value for a configuration option
				\nUsage: <info>php %command.full_name%</info> <option>=<value>";

		$this->setDescription('Set a value for a configuration option');
		$this->setHelp($help);
	}

	/**
	 * Verifies database connection
	 *
	 * @param   array  $options  Options array
	 *
	 * @return boolean|\Joomla\Database\DatabaseInterface
	 *
	 * @since 4.0.0
	 * @throws \Exception
	 */
	public function checkDb($options): bool
	{
		// Ensure a database type was selected.
		if (empty($options['dbtype']))
		{
			$this->ioStyle->error(Text::_('INSTL_DATABASE_INVALID_TYPE'));

			return false;
		}

		// Ensure that a hostname and user name were input.
		if (empty($options['host']) || empty($options['user']))
		{
			$this->ioStyle->error(Text::_('INSTL_DATABASE_INVALID_DB_DETAILS'));

			return false;
		}

		// Validate database table prefix.
		if (isset($options['dbprefix']) && !preg_match('#^[a-zA-Z]+[a-zA-Z0-9_]*$#', $options['dbprefix']))
		{
			$this->ioStyle->error(Text::_('INSTL_DATABASE_PREFIX_MSG'));

			return false;
		}

		// Validate length of database table prefix.
		if (isset($options['dbprefix']) && strlen($options['dbprefix']) > 15)
		{
			$this->ioStyle->error(Text::_('INSTL_DATABASE_FIX_TOO_LONG'), 'warning');

			return false;
		}

		// Validate length of database name.
		if (strlen($options['db']) > 64)
		{
			$this->ioStyle->error(Text::_('INSTL_DATABASE_NAME_TOO_LONG'));

			return false;
		}

		// Validate database name.
		if (in_array($options['dbtype'], ['pgsql', 'postgresql'], true) && !preg_match('#^[a-zA-Z_][0-9a-zA-Z_$]*$#', $options['db']))
		{
			$this->ioStyle->error(Text::_('INSTL_DATABASE_NAME_MSG_POSTGRES'));

			return false;
		}

		if (in_array($options['dbtype'], ['mysql', 'mysqli']) && preg_match('#[\\\\\/]#', $options['db']))
		{
			$this->ioStyle->error(Text::_('INSTL_DATABASE_NAME_MSG_MYSQL'));

			return false;
		}

		// Workaround for UPPERCASE table prefix for PostgreSQL
		if (in_array($options['dbtype'], ['pgsql', 'postgresql']))
		{
			if (isset($options['dbprefix']) && strtolower($options['dbprefix']) !== $options['dbprefix'])
			{
				$this->ioStyle->error(Text::_('INSTL_DATABASE_FIX_LOWERCASE'));

				return false;
			}
		}

		$app = $this->getApplication();

		// Check db connection encryption properties
		$model = $app->bootComponent('com_config')->getMVCFactory($app)->createModel('Application', 'Administrator');

		if (!$model->validateDbConnection($options))
		{
			$this->ioStyle->error(Text::_('Failed to validate the db connection encryption properties'));

			return false;
		}

		// Build the connection options array.
		$settings = [
			'driver'   => $options['dbtype'],
			'host'     => $options['host'],
			'user'     => $options['user'],
			'password' => $options['password'],
			'database' => $options['db'],
			'prefix'   => $options['dbprefix'],
		];

		if ((int) $options['dbencryption'] !== 0)
		{
			$settings['ssl'] = [
				'enable'             => true,
				'verify_server_cert' => (bool) $options['dbsslverifyservercert'],
			];

			foreach (['cipher', 'ca', 'key', 'cert'] as $value)
			{
				$confVal = trim($options['dbssl' . $value]);

				if ($confVal !== '')
				{
					$settings['ssl'][$value] = $confVal;
				}
			}
		}

		// Get a database object.
		try
		{
			$db = DatabaseDriver::getInstance($settings);
			$db->getVersion();
		}
		catch (\Exception $e)
		{
			$this->ioStyle->error(
				Text::sprintf(
					'Cannot connect to database, verify that you specified the correct database details %s',
					$e->getMessage()
				)
			);

			return false;
		}

		if ((int) $options['dbencryption'] !== 0 && empty($db->getConnectionEncryption()))
		{
			if ($db->isConnectionEncryptionSupported())
			{
				$this->ioStyle->error(Text::_('COM_CONFIG_ERROR_DATABASE_ENCRYPTION_CONN_NOT_ENCRYPT'));
			}
			else
			{
				$this->ioStyle->error(Text::_('COM_CONFIG_ERROR_DATABASE_ENCRYPTION_SRV_NOT_SUPPORTS'));
			}

			return false;
		}

		return true;
	}

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @since   4.0.0
	 * @throws \Exception
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$this->configureIO($input, $output);

		$options = $this->getOptions();

		if (!$this->retrieveOptionsFromInput($options))
		{
			return self::CONFIG_OPTIONS_WRONG;
		}

		if (!$this->validateOptions())
		{
			return self::CONFIG_VALIDATION_FAILED;
		}

		$initialOptions = $this->getInitialConfigurationOptions()->toArray();

		$combinedOptions = $this->sanitizeOptions(array_merge($initialOptions, $this->options));

		if (!$this->checkDb($combinedOptions))
		{
			return self::DB_VALIDATION_FAILED;
		}

		if ($this->saveConfiguration($combinedOptions))
		{
			$this->ioStyle->success('Configuration set');

			return self::CONFIG_SET_SUCCESSFUL;
		}

		return self::CONFIG_SET_FAILED;
	}

	/**
	 * Sanitize the options array for boolean
	 *
	 * @param   array  $options  Options array
	 *
	 * @return array
	 *
	 * @since 4.0.0
	 */
	public function sanitizeOptions(Array $options): array
	{
		foreach ($options as $key => $value)
		{
			$value = $value === 'false' ? false : $value;
			$value = $value === 'true' ? true : $value;

			$options[$key] = $value;
		}

		return $options;
	}
}
