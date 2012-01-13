<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * JDatabaseInstaller base class.
 *
 * @package  Joomla.Libraries
 * @since    ¿
 */
abstract class JDatabaseInstaller
{
	/**
	 * Installation options.
	 *
	 * @var JObject
	 */
	protected $options;

	/**
	 * Get a JDatabaseInstaller instance.
	 *
	 * @param   JObject  $options  Installation options
	 *
	 * @return JDatabaseInstaller
	 */
	public static function getInstance(JObject $options)
	{
		$type = $options->db_type;

		$type = ('mysqli' == $type) ? 'mysql' : $type;

		$className = 'JDatabaseInstaller' . ucfirst($type);

		if (!class_exists($className))
		{
			throw new JDatabaseInstallerException(sprintf('Required class %s not found', $className));
		}

		return new $className($options);
	}

	/**
	 * Constructor.
	 *
	 * @param   JObject  $options  Database install options.
	 */
	protected function __construct(JObject $options)
	{
		$this->options = $options;
	}

	/**
	 * Check the database.
	 *
	 * @return JDatabaseInstaller
	 *
	 * @throws JDatabaseInstallerException
	 */
	abstract public function check();

	/**
	 * Method to create a new database.
	 *
	 * @return JDatabaseInstallerMysql
	 */
	abstract public function createDatabase();

	/**
	 * Method to set the database character set to UTF-8.
	 *
	 * @return JDatabaseInstaller
	 */
	abstract public function setDatabaseCharset();

	/**
	 * Update the database.
	 *
	 * @return JDatabaseInstallerMysql
	 *
	 * @throws JDatabaseInstallerException
	 */
	abstract protected function updateDatabase();

	/**
	 * Get the type name.
	 *
	 * @return string
	 */
	protected function getType()
	{
		return $this->options->db_type;
	}

	/**
	 * Method to get a JDatabase object.
	 *
	 * @param   boolean  $select  Should the database be selected.
	 *
	 * @return  JDatabase object on success, JException on error.
	 *
	 * @since
	 */
	public function getDbo($select = false)
	{
		static $db = null;

		if (!is_null($db))
		{
			return $db;
		}

		// Get a database object.
		$db = JDatabase::getInstance(
			array(
				'driver' => $this->options->db_type,
				'host' => $this->options->db_host,
				'user' => $this->options->get('db_user'),
				'password' => $this->options->get('db_pass'),
				'database' => $this->options->db_name,
				'prefix' => $this->options->db_prefix,
				'select' => $select
			)
		);

		return $db;
	}

	/**
	 * Clean and backup the database.
	 *
	 * @return JDatabaseInstaller
	 */
	public function clean()
	{
		// Should any old database tables be removed or backed up?
		if ('remove' == $this->options->db_old)
		{
			// Attempt to delete the old database tables.
			$this->deleteDatabase();
		}
		else
		{
			// If the database isn't being deleted, back it up.
			$this->backupDatabase();
		}

		return $this;
	}

	/**
	 * Attempt to create the database.
	 *
	 * @return JDatabaseInstaller
	 */
	public function create()
	{
		// Get a database object.
		$db = $this->getDbo();

		try
		{
			$db->select($this->options->db_name);
		}
		catch (JDatabaseException $e)
		{
			$this->createDatabase();

			$db->select($this->options->db_name);
		}

		// Set the character set to UTF-8 for pre-existing databases.
		$this->setDatabaseCharset();

		return $this;
	}

	/**
	 * Method to backup all tables in a database with a given prefix.
	 *
	 * @return	JDatabaseInstaller
	 *
	 * @since
	 */
	public function backupDatabase()
	{
		$db = $this->getDbo();

		$backup = 'bak_' . $this->options->db_prefix;

		// Get the tables in the database.
		$tables = $db->getTableList();

		if (!$tables)
		{
			return $this;
		}

		foreach ($tables as $table)
		{
			// If the table uses the given prefix, back it up.
			if (strpos($table, $this->options->db_prefix) === 0)
			{
				// Backup table name.
				$backupTable = str_replace($this->options->db_prefix, $backup, $table);

				// Drop the backup table.
				$db->dropTable($backupTable, true);

				// Rename the current table to the backup table.
				$db->renameTable($table, $backupTable, $backup, $this->options->db_prefix);
			}
		}

		return $this;
	}

	/**
	 * Method to delete all tables in a database with a given prefix.
	 *
	 * @return	JDatabaseInstaller
	 */
	protected function deleteDatabase()
	{
		$db = $this->getDbo();

		// Get the tables in the database.
		$tables = $db->getTableList();

		if (!$tables)
		{
			return $this;
		}

		foreach ($tables as $table)
		{
			// If the table uses the given prefix, drop it.
			if (strpos($table, $this->options->db_prefix) === 0)
			{
				// Drop the table.
				$db->dropTable($table);
			}
		}

		return $this;
	}

	/**
	 * Populate the database.
	 *
	 * @return JDatabaseInstaller
	 */
	public function populate()
	{
		// Check utf8 support.
		$backward = ($this->getDbo()->hasUTF()) ? '' : '_backward';

		$schema = JPATH_INSTALLATION . '/sql/' . $this->getType() . '/joomla' . $backward . '.sql';

		// Attempt to import the database schema.
		$this->populateDatabase($schema);

		return $this;
	}

	/**
	 * Update the database.
	 *
	 * @return JDatabaseInstaller
	 *
	 * @throws JDatabaseInstallerException
	 */
	public function update()
	{
		$db = $this->getDbo();

		// Attempt to refresh manifest caches
		$query = $db->getQuery(true);

		$query->from('#__extensions');
		$query->select('*');

		$extensions = $db->setQuery($query)->loadObjectList();

		JFactory::$database = $db;

		$installer = JInstaller::getInstance();

		foreach ($extensions as $extension)
		{
			if (!$installer->refreshManifestCache($extension->extension_id))
			{
				throw new JDatabaseInstallerException(
					JText::sprintf('INSTL_DATABASE_COULD_NOT_REFRESH_MANIFEST_CACHE', $extension->name)
				);
			}
		}

		// Perform the update in extending classes
		$this->updateDatabase();

		return $this;
	}

	/**
	 * Install the sample data.
	 *
	 * @return JDatabaseInstaller
	 *
	 * @throws JDatabaseInstallerException
	 */
	public function installSampleData()
	{
		// Build the path to the sample data file.
		$data = JPATH_INSTALLATION . '/sql/' . $this->getType() . '/' . $this->options->sample_file;

		// Attempt to import the database schema.
		if (!file_exists($data))
		{
			// @todo filenotfoundexception
			throw new JDatabaseInstallerException(JText::sprintf('INSTL_DATABASE_FILE_DOES_NOT_EXIST', $data));
		}

		$this->populateDatabase($data);

		return $this;
	}

	/**
	 * Apply localization options.
	 *
	 * @return JDatabaseInstaller
	 */
	public function localize()
	{
		$db = $this->getDbo();

		// Load the localise.sql for translating the data in joomla.sql/joomla_backwards.sql
		$path = 'sql/' . $this->getType() . '/localise.sql';

		if (JFile::exists($path))
		{
			$this->populateDatabase($path);
		}

		// Handle default backend language setting. This feature is available for localized versions of Joomla 1.5.
		$languages = JFactory::getApplication()->getLocaliseAdmin($db);

		if (in_array($this->options->language, $languages['admin'])
			|| in_array($this->options->language, $languages['site']))
		{
			// Build the language parameters for the language manager.
			$params = array();

			// Set default administrator/site language to sample data values:
			$params['administrator'] = 'en-GB';
			$params['site'] = 'en-GB';

			if (in_array($this->options->language, $languages['admin']))
			{
				$params['administrator'] = $this->options->language;
			}

			if (in_array($this->options->language, $languages['site']))
			{
				$params['site'] = $this->options->language;
			}

			$params = json_encode($params);

			// Update the language settings in the language manager.
			$db->setQuery(
				'UPDATE ' . $db->quoteName('#__extensions') .
					' SET ' . $db->quoteName('params') . ' = ' . $db->Quote($params) .
					' WHERE ' . $db->quoteName('element') . '=\'com_languages\''
			)->query();
		}

		return $this;
	}

	/**
	 * Method to import a database schema from a file.
	 *
	 * @param   string  $schema  Path to the schema file.
	 *
	 * @return  boolean True on success.
	 *
	 * @since
	 */
	protected function populateDatabase($schema)
	{
		// Initialise variables.
		$db = $this->getDbo(true);

		if (!file_exists($schema))
		{
			// @todo filesystemexception
			throw new JDatabaseInstallerException(JText::_('INSTL_DATABASE_SCHEMA_FILE_NOT_FOUND'));
		}

		// Get the contents of the schema file.
		if (!$buffer = file_get_contents($schema))
		{
			// @todo filesystemexception
			throw new JDatabaseInstallerException(JText::_('INSTL_DATABASE_SCHEMA_FILE_READ_ERROR'));
		}

		// Get an array of queries from the schema and process them.
		foreach ($this->splitQueries($buffer) as $query)
		{
			// Trim any whitespace.
			$query = trim($query);

			// If the query isn't empty and is not a comment, execute it.
			if (!empty($query) && ($query{0} != '#'))
			{
				// Execute the query.
				$db->setQuery($query)->query();
			}
		}

		return $this;
	}

	/**
	 * Method to split up queries from a schema file into an array.
	 *
	 * @param   string  $sql  SQL schema.
	 *
	 * @return  array  Queries to perform.
	 *
	 * @since     1.0
	 *
	 * @todo      Can JDatabase::splitQuery() do the same ¿
	 */
	protected function splitQueries($sql)
	{
		// Initialise variables.
		$buffer = array();
		$queries = array();
		$in_string = false;

		// Trim any whitespace.
		$sql = trim($sql);

		// Remove comment lines.
		$sql = preg_replace("/\n\#[^\n]*/", '', "\n" . $sql);

		// Parse the schema file to break up queries.
		for ($i = 0; $i < strlen($sql) - 1; $i++)
		{
			if ($sql[$i] == ";" && !$in_string)
			{
				$queries[] = substr($sql, 0, $i);
				$sql = substr($sql, $i + 1);
				$i = 0;
			}

			if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\")
			{
				$in_string = false;
			}
			elseif (!$in_string && ($sql[$i] == '"' || $sql[$i] == "'")
				&& (!isset ($buffer[0]) || $buffer[0] != "\\")
			)
			{
				$in_string = $sql[$i];
			}

			if (isset ($buffer[1]))
			{
				$buffer[0] = $buffer[1];
			}

			$buffer[1] = $sql[$i];
		}

		// If the is anything left over, add it to the queries.
		if (!empty($sql))
		{
			$queries[] = $sql;
		}

		return $queries;
	}

}
