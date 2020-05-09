<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installation\Helper\DatabaseHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\UTF8MB4SupportInterface;
use Joomla\Utilities\ArrayHelper;

/**
 * Database configuration model for the Joomla Core Installer.
 *
 * @since  3.1
 */
class DatabaseModel extends BaseInstallationModel
{
	/**
	 * Get the current setup options from the session.
	 *
	 * @return  array  An array of options from the session.
	 *
	 * @since   4.0.0
	 */
	public function getOptions()
	{
		return Factory::getSession()->get('setup.options', array());
	}

	/**
	 * Method to initialise the database.
	 *
	 * @param   boolean  $select  Select the database when creating the connections.
	 *
	 * @return  DatabaseInterface|boolean  Database object on success, boolean false on failure
	 *
	 * @since   3.1
	 */
	public function initialise($select = true)
	{
		$options = $this->getOptions();

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

		// Validate database name.
		if (in_array($options->db_type, ['pgsql', 'postgresql'], true) && !preg_match('#^[a-zA-Z_][0-9a-zA-Z_$]*$#', $options->db_name))
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_NAME_MSG_POSTGRESQL'), 'warning');

			return false;
		}

		if (in_array($options->db_type, ['mysql', 'mysqli']) && preg_match('#[\\\\\/\.]#', $options->db_name))
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_NAME_MSG_MYSQL'), 'warning');

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

		// Security check for remote db hosts: Check env var if disabled
		$shouldCheckLocalhost = getenv('JOOMLA_INSTALLATION_DISABLE_LOCALHOST_CHECK') !== '1';

		// Per Default allowed DB Hosts
		$localhost = array(
			'localhost',
			'127.0.0.1',
			'::1',
		);

		// Check the security file if the db_host is not localhost / 127.0.0.1 / ::1
		if ($shouldCheckLocalhost && !in_array($options->db_host, $localhost))
		{
			$remoteDbFileTestsPassed = Factory::getSession()->get('remoteDbFileTestsPassed', false);

			// When all checks have been passed we don't need to do this here again.
			if ($remoteDbFileTestsPassed === false)
			{
				$generalRemoteDatabaseMessage = Text::sprintf(
					'INSTL_DATABASE_HOST_IS_NOT_LOCALHOST_GENERAL_MESSAGE',
					'https://docs.joomla.org/Special:MyLanguage/J3.x:Secured_procedure_for_installing_Joomla_with_a_remote_database'
				);

				$remoteDbFile = Factory::getSession()->get('remoteDbFile', false);

				if ($remoteDbFile === false)
				{
					// Add the general message
					Factory::getApplication()->enqueueMessage($generalRemoteDatabaseMessage, 'warning');

					// This is the file you need to remove if you want to use a remote database
					$remoteDbFile = '_Joomla' . UserHelper::genRandomPassword(21) . '.txt';
					Factory::getSession()->set('remoteDbFile', $remoteDbFile);

					// Get the path
					$remoteDbPath = JPATH_INSTALLATION . '/' . $remoteDbFile;

					// When the path is not writable the user needs to create the file manually
					if (!File::write($remoteDbPath, ''))
					{
						// Request to create the file manually
						Factory::getApplication()->enqueueMessage(
							Text::sprintf(
								'INSTL_DATABASE_HOST_IS_NOT_LOCALHOST_CREATE_FILE',
								$remoteDbFile,
								'installation',
								Text::_('INSTL_INSTALL_JOOMLA')
							),
							'notice'
						);

						Factory::getSession()->set('remoteDbFileUnwritable', true);

						return false;
					}

					// Save the file name to the session
					Factory::getSession()->set('remoteDbFileWrittenByJoomla', true);

					// Request to delete that file
					Factory::getApplication()->enqueueMessage(
						Text::sprintf(
							'INSTL_DATABASE_HOST_IS_NOT_LOCALHOST_DELETE_FILE',
							$remoteDbFile,
							'installation',
							Text::_('INSTL_INSTALL_JOOMLA')
						),
						'notice'
					);

					return false;
				}

				if (Factory::getSession()->get('remoteDbFileWrittenByJoomla', false) === true
					&& File::exists(JPATH_INSTALLATION . '/' . $remoteDbFile))
				{
					// Add the general message
					Factory::getApplication()->enqueueMessage($generalRemoteDatabaseMessage, 'warning');

					// Request to delete the file
					Factory::getApplication()->enqueueMessage(
						Text::sprintf(
							'INSTL_DATABASE_HOST_IS_NOT_LOCALHOST_DELETE_FILE',
							$remoteDbFile,
							'installation',
							Text::_('INSTL_INSTALL_JOOMLA')
						),
						'notice'
					);

					return false;
				}

				if (Factory::getSession()->get('remoteDbFileUnwritable', false) === true && !File::exists(JPATH_INSTALLATION . '/' . $remoteDbFile))
				{
					// Add the general message
					Factory::getApplication()->enqueueMessage($generalRemoteDatabaseMessage, 'warning');

					// Request to create the file manually
					Factory::getApplication()->enqueueMessage(
						Text::sprintf(
							'INSTL_DATABASE_HOST_IS_NOT_LOCALHOST_CREATE_FILE',
							$remoteDbFile,
							'installation',
							Text::_('INSTL_INSTALL_JOOMLA')
						),
						'notice'
					);

					return false;
				}

				// All tests for this session passed set it to the session
				Factory::getSession()->set('remoteDbFileTestsPassed', true);
			}
		}

		// Get a database object.
		try
		{
			return DatabaseHelper::getDbo(
				$options->db_type,
				$options->db_host,
				$options->db_user,
				$options->db_pass_plain,
				$options->db_name,
				$options->db_prefix,
				$select,
				DatabaseHelper::getEncryptionSettings($options)
			);
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('INSTL_DATABASE_COULD_NOT_CONNECT', $e->getMessage()), 'error');

			return false;
		}
	}

	/**
	 * Method to create a new database.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 * @throws  \RuntimeException
	 */
	public function createDatabase()
	{
		$options = (object) $this->getOptions();

		$db = $this->initialise(false);

		if ($db === false)
		{
			// Error messages are enqueued by the initialise function, we just need to tell the controller how to redirect
			return false;
		}

		// Check database version.
		$type = $options->db_type;

		try
		{
			$db_version = $db->getVersion();
		}
		catch (\RuntimeException $e)
		{
			/*
			 * We may get here if the database doesn't exist, if so then explain that to users instead of showing the database connector's error
			 * This only supports PDO PostgreSQL and the PDO MySQL drivers presently
			 *
			 * Error Messages:
			 * PDO MySQL: [1049] Unknown database 'database_name'
			 * PDO PostgreSQL: database "database_name" does not exist
			 */
			if ($type === 'mysql' && strpos($e->getMessage(), '[1049] Unknown database') === 42
				|| $type === 'pgsql' && strpos($e->getMessage(), 'database "' . $options->db_name . '" does not exist'))
			{
				/*
				 * Now we're really getting insane here; we're going to try building a new JDatabaseDriver instance
				 * in order to trick the connection into creating the database
				 */
				if ($type === 'mysql')
				{
					// MySQL (PDO): Don't specify database name
					$altDBoptions = array(
						'driver'   => $options->db_type,
						'host'     => $options->db_host,
						'user'     => $options->db_user,
						'password' => $options->db_pass_plain,
						'prefix'   => $options->db_prefix,
						'select'   => $options->db_select,
						DatabaseHelper::getEncryptionSettings($options),
					);
				}
				else
				{
					// PostgreSQL (PDO): Use 'postgres'
					$altDBoptions = array(
						'driver'   => $options->db_type,
						'host'     => $options->db_host,
						'user'     => $options->db_user,
						'password' => $options->db_pass_plain,
						'database' => 'postgres',
						'prefix'   => $options->db_prefix,
						'select'   => $options->db_select,
						DatabaseHelper::getEncryptionSettings($options),
					);
				}

				$altDB = DatabaseDriver::getInstance($altDBoptions);

				// Try to create the database now using the alternate driver
				try
				{
					$this->createDb($altDB, $options, $altDB->hasUTFSupport());
				}
				catch (\RuntimeException $e)
				{
					// We did everything we could
					throw new \RuntimeException(Text::_('INSTL_DATABASE_COULD_NOT_CREATE_DATABASE'), 500, $e);
				}

				// If we got here, the database should have been successfully created, now try one more time to get the version
				try
				{
					$db_version = $db->getVersion();
				}
				catch (\RuntimeException $e)
				{
					// We did everything we could
					throw new \RuntimeException(Text::sprintf('INSTL_DATABASE_COULD_NOT_CONNECT', $e->getMessage()), 500, $e);
				}
			}
			// Anything getting into this part of the conditional either doesn't support manually creating the database or isn't that type of error
			else
			{
				throw new \RuntimeException(Text::sprintf('INSTL_DATABASE_COULD_NOT_CONNECT', $e->getMessage()), 500, $e);
			}
		}

		// Get required database version
		$minDbVersionRequired = DatabaseHelper::getMinimumServerVersion($db, $options);

		// Check minimum database version
		if (version_compare($db_version, $minDbVersionRequired) < 0)
		{
			if (in_array($type, ['mysql', 'mysqli']) && $db->isMariaDb())
			{
				throw new \RuntimeException(
					Text::sprintf(
						'INSTL_DATABASE_INVALID_MARIADB_VERSION',
						$minDbVersionRequired,
						$db_version
					)
				);
			}
			else
			{
				throw new \RuntimeException(
					Text::sprintf(
						'INSTL_DATABASE_INVALID_' . strtoupper($type) . '_VERSION',
						$minDbVersionRequired,
						$db_version
					)
				);
			}
		}

		// Check database connection encryption
		if ($options->db_encryption !== 0 && empty($db->getConnectionEncryption()))
		{
			if ($db->isConnectionEncryptionSupported())
			{
				throw new \RuntimeException(Text::_('INSTL_DATABASE_ENCRYPTION_MSG_CONN_NOT_ENCRYPT'));
			}
			else
			{
				throw new \RuntimeException(Text::_('INSTL_DATABASE_ENCRYPTION_MSG_SRV_NOT_SUPPORTS'));
			}
		}

		// @internal Check for spaces in beginning or end of name.
		if (strlen(trim($options->db_name)) <> strlen($options->db_name))
		{
			throw new \RuntimeException(Text::_('INSTL_DATABASE_NAME_INVALID_SPACES'));
		}

		// @internal Check for asc(00) Null in name.
		if (strpos($options->db_name, chr(00)) !== false)
		{
			throw new \RuntimeException(Text::_('INSTL_DATABASE_NAME_INVALID_CHAR'));
		}

		// Get database's UTF support.
		$utfSupport = $db->hasUTFSupport();

		// Try to select the database.
		try
		{
			$db->select($options->db_name);
		}
		catch (\RuntimeException $e)
		{
			// If the database could not be selected, attempt to create it and then select it.
			if (!$this->createDb($db, $options, $utfSupport))
			{
				throw new \RuntimeException(Text::sprintf('INSTL_DATABASE_ERROR_CREATE', $options->db_name), 500, $e);
			}

			$db->select($options->db_name);
		}

		// Set the character set to UTF-8 for pre-existing databases.
		try
		{
			$db->alterDbCharacterSet($options->db_name);
		}
		catch (\RuntimeException $e)
		{
			// Continue Anyhow
		}

		$options = (array) $options;

		// Remove *_errors value.
		foreach ($options as $i => $option)
		{
			if (isset($i['1']) && $i['1'] == '*')
			{
				unset($options[$i]);

				break;
			}
		}

		$options = array_merge(['db_created' => 1], $options);

		Factory::getSession()->set('setup.options', $options);

		return true;
	}

	/**
	 * Method to process the old database.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 */
	public function handleOldDatabase()
	{
		$options = $this->getOptions();

		if (!isset($options['db_created']) || !$options['db_created'])
		{
			return $this->createDatabase($options);
		}

		// Get the options as an object for easier handling.
		$options = ArrayHelper::toObject($options);

		if (!$db = $this->initialise())
		{
			return false;
		}

		// Set the character set to UTF-8 for pre-existing databases.
		try
		{
			$db->alterDbCharacterSet($options->db_name);
		}
		catch (\RuntimeException $e)
		{
			// Continue Anyhow
		}

		// Backup any old database.
		if (!$this->backupDatabase($db, $options->db_prefix))
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to create the database tables.
	 *
	 * @param   string  $schema  The SQL schema file to apply.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 */
	public function createTables($schema)
	{
		if (!$db = $this->initialise())
		{
			return false;
		}

		$serverType = $db->getServerType();

		// Set the appropriate schema script based on UTF-8 support.
		$schemaFile = 'sql/' . $serverType . '/' . $schema . '.sql';

		// Check if the schema is a valid file
		if (!is_file($schemaFile))
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('INSTL_ERROR_DB', Text::_('INSTL_DATABASE_NO_SCHEMA')), 'error');

			return false;
		}

		// Attempt to import the database schema.
		if (!$this->populateDatabase($db, $schemaFile))
		{
			return false;
		}

		// MySQL only: Attempt to update the table #__utf8_conversion.
		if ($serverType === 'mysql')
		{
			$query = $db->getQuery(true);
			$query->clear()
				->update($db->quoteName('#__utf8_conversion'))
				->set($db->quoteName('converted') . ' = ' . ($db->hasUTF8mb4Support() ? 2 : 1));
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (\RuntimeException $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to backup all tables in a database with a given prefix.
	 *
	 * @param   DatabaseDriver  $db      JDatabaseDriver object.
	 * @param   string          $prefix  Database table prefix.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since    3.1
	 */
	public function backupDatabase($db, $prefix)
	{
		$return = true;
		$backup = 'bak_' . $prefix;

		// Get the tables in the database.
		$tables = $db->getTableList();

		if ($tables)
		{
			foreach ($tables as $table)
			{
				// If the table uses the given prefix, back it up.
				if (strpos($table, $prefix) === 0)
				{
					// Backup table name.
					$backupTable = str_replace($prefix, $backup, $table);

					// Drop the backup table.
					try
					{
						$db->dropTable($backupTable, true);
					}
					catch (\RuntimeException $e)
					{
						Factory::getApplication()->enqueueMessage(Text::sprintf('INSTL_DATABASE_ERROR_BACKINGUP', $e->getMessage()), 'error');

						$return = false;
					}

					// Rename the current table to the backup table.
					try
					{
						$db->renameTable($table, $backupTable, $backup, $prefix);
					}
					catch (\RuntimeException $e)
					{
						Factory::getApplication()->enqueueMessage(Text::sprintf('INSTL_DATABASE_ERROR_BACKINGUP', $e->getMessage()), 'error');

						$return = false;
					}
				}
			}
		}

		return $return;
	}

	/**
	 * Method to create a new database.
	 *
	 * @param   DatabaseDriver  $db       Database object.
	 * @param   CMSObject       $options  CMSObject coming from "initialise" function to pass user
	 *                                    and database name to database driver.
	 * @param   boolean         $utf      True if the database supports the UTF-8 character set.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 */
	public function createDb($db, $options, $utf)
	{
		// Build the create database query.
		try
		{
			// Run the create database query.
			$db->createDatabase($options, $utf);
		}
		catch (\RuntimeException $e)
		{
			// If an error occurred return false.
			return false;
		}

		return true;
	}

	/**
	 * Method to import a database schema from a file.
	 *
	 * @param   \Joomla\Database\DatabaseInterface  $db      JDatabase object.
	 * @param   string                              $schema  Path to the schema file.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 */
	public function populateDatabase($db, $schema)
	{
		$return = true;

		// Get the contents of the schema file.
		if (!($buffer = file_get_contents($schema)))
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_SAMPLE_DATA_NOT_FOUND'), 'error');

			return false;
		}

		// Get an array of queries from the schema and process them.
		$queries = $this->splitQueries($buffer);

		foreach ($queries as $query)
		{
			// Trim any whitespace.
			$query = trim($query);

			// If the query isn't empty and is not a MySQL or PostgreSQL comment, execute it.
			if (!empty($query) && ($query[0] != '#') && ($query[0] != '-'))
			{
				/**
				 * If we don't have UTF-8 Multibyte support we'll have to convert queries to plain UTF-8
				 *
				 * Note: the JDatabaseDriver::convertUtf8mb4QueryToUtf8 performs the conversion ONLY when
				 * necessary, so there's no need to check the conditions in JInstaller.
				 */
				if ($db instanceof UTF8MB4SupportInterface)
				{
					$query = $db->convertUtf8mb4QueryToUtf8($query);

					/**
					 * This is a query which was supposed to convert tables to utf8mb4 charset but the server doesn't
					 * support utf8mb4. Therefore we don't have to run it, it has no effect and it's a mere waste of time.
					 */
					if (!$db->hasUTF8mb4Support() && stristr($query, 'CONVERT TO CHARACTER SET utf8 '))
					{
						continue;
					}
				}

				// Execute the query.
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (\RuntimeException $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

					$return = false;
				}
			}
		}

		return $return;
	}

	/**
	 * Method to split up queries from a schema file into an array.
	 *
	 * @param   string  $query  SQL schema.
	 *
	 * @return  array  Queries to perform.
	 *
	 * @since   3.1
	 */
	protected function splitQueries($query)
	{
		$buffer    = array();
		$queries   = array();
		$in_string = false;

		// Trim any whitespace.
		$query = trim($query);

		// Remove comment lines.
		$query = preg_replace("/\n\#[^\n]*/", '', "\n" . $query);

		// Remove PostgreSQL comment lines.
		$query = preg_replace("/\n\--[^\n]*/", '', "\n" . $query);

		// Find function.
		$funct = explode('CREATE OR REPLACE FUNCTION', $query);

		// Save sql before function and parse it.
		$query = $funct[0];

		// Parse the schema file to break up queries.
		for ($i = 0; $i < strlen($query) - 1; $i++)
		{
			if ($query[$i] == ';' && !$in_string)
			{
				$queries[] = substr($query, 0, $i);
				$query     = substr($query, $i + 1);
				$i         = 0;
			}

			if ($in_string && ($query[$i] == $in_string) && $buffer[1] != "\\")
			{
				$in_string = false;
			}
			elseif (!$in_string && ($query[$i] == '"' || $query[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\"))
			{
				$in_string = $query[$i];
			}

			if (isset($buffer[1]))
			{
				$buffer[0] = $buffer[1];
			}

			$buffer[1] = $query[$i];
		}

		// If the is anything left over, add it to the queries.
		if (!empty($query))
		{
			$queries[] = $query;
		}

		// Add function part as is.
		for ($f = 1, $fMax = count($funct); $f < $fMax; $f++)
		{
			$queries[] = 'CREATE OR REPLACE FUNCTION ' . $funct[$f];
		}

		return $queries;
	}
}
