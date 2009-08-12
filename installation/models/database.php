<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');
jimport('joomla.database.database');
require_once(JPATH_INSTALLATION.'/helpers/database.php');

/**
 * Database configuration model for the Joomla Core Installer.
 *
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationModelDatabase extends JModel
{

	function initialize($options)
	{
		// Get the options as a JObject for easier handling.
		$options = JArrayHelper::toObject($options, 'JObject');

		// Ensure a database type was selected.
		if (empty($options->db_type)) {
			$this->setError(JText::_('validType'));
			return false;
		}

		// Ensure that a valid hostname and user name were input.
		if (empty($options->db_host) || empty($options->db_user)) {
			$this->setError(JText::_('validDBDetails'));
			return false;
		}

		// Ensure that a database name was input.
		if (empty($options->db_name)) {
			$this->setError(JText::_('emptyDBName'));
			return false;
		}

		// Validate database table prefix.
		if (!preg_match('#^[a-zA-Z]+[a-zA-Z0-9_]*$#', $options->db_prefix)) {
			$this->setError(JText::_('MYSQLPREFIXINVALIDCHARS'));
			return false;
		}

		// Validate length of database table prefix.
		if (strlen($options->db_prefix) > 15) {
			$this->setError(JText::_('MYSQLPREFIXTOOLONG'));
			return false;
		}

		// Validate length of database name.
		if (strlen($options->db_name) > 64) {
			$this->setError(JText::_('MYSQLDBNAMETOOLONG'));
			return false;
		}

		// If the database is not yet created, create it.
		if (empty($options->db_created))
		{
			// Get a database object.
			$db = &$this->getDbo($options->db_type, $options->db_host, $options->db_user, $options->db_pass, null, $options->db_prefix, false);

			// Check for errors.
			if (JError::isError($db)) {
				$this->setError(JText::sprintf('WARNNOTCONNECTDB', $db->toString()));
				return false;
			}

			// Check for database errors.
			if ($err = $db->getErrorNum()) {
				$this->setError(JText::sprintf('WARNNOTCONNECTDB', $db->getErrorNum()));
				return false;
			}
			
			// Check database version.
			$db_version = $db->getVersion();
			if (($position = strpos($db_version, '-')) !== false) {
				$db_version = substr($db_version, 0, $position);
			}
			
			if (!version_compare($db_version, '5.0.4', '>=')) {
				$this->setError(JText::_('You need MySQL 5.0.4 or higher to continue the installation.'));
				return false;
			}

			// Check utf8 support.
			$utfSupport = $db->hasUTF();

			// Try to select the database
			if (!$db->select($options->db_name))
			{
				// If the database could not be selected, attempt to create it and then select it.
				if ($this->createDatabase($db, $options->db_name, $utfSupport)) {
					$db->select($options->db_name);
				}
				else {
					$this->setError(JText::sprintf('WARNCREATEDB', $options->db_name));
					return false;
				}
			}
			else {
				// Set the character set to UTF-8 for pre-existing databases.
				$this->setDatabaseCharset($db, $options->db_name);
			}

			// Should any old database tables be removed or backed up?
			if ($options->db_old == 'remove')
			{
				// Attempt to delete the old database tables.
				if (!$this->deleteDatabase($db, $options->db_name, $options->db_prefix)) {
					$this->setError(JText::_('WARNDELETEDB'));
					return false;
				}
			}
			else
			{
				// If the database isn't being deleted, back it up.
				if (!$this->backupDatabase($db, $options->db_name, $options->db_prefix)) {
					$this->setError(JText::_('WARNBACKINGUPDB'));
					return false;
				}
			}

			// Set the appropriate schema script based on UTF-8 support.
			$type = $options->db_type;
			if ($utfSupport) {
				$schema = 'sql/'.(($type == 'mysqli') ? 'mysql' : $type).'/joomla.sql';
			} else {
				$schema = 'sql/'.(($type == 'mysqli') ? 'mysql' : $type).'/joomla_backward.sql';
			}

			// Attempt to import the database schema.
			if (!$this->populateDatabase($db, $schema)) {
				$this->setError(JText::_('WARNPOPULATINGDB'));
				return false;
			}

			// Load the localise.sql for translating the data in joomla.sql/joomla_backwards.sql
			$dblocalise = 'sql/'.(($type == 'mysqli') ? 'mysql' : $type).'/localise.sql';
			if (JFile::exists($dblocalise)) {
				if (!$this->populateDatabase($db, $dblocalise)) {
					$this->setError(JText::_('WARNPOPULATINGDB'));
					return false;
				}
			}

			// Handle default backend language setting. This feature is available for localized versions of Joomla! 1.5.
			$app = & JFactory::getApplication();
			$languages = $app->getLocaliseAdmin();
			if (in_array($options->language, $languages['admin']) || in_array($options->language, $languages['site']))
			{
				// Build the language parameters for the language manager.
				$params = array();
				if (in_array($options->language, $languages['admin'])) {
					$params[] = 'administrator='.$options->language;
				}
				if (in_array($options->language, $languages['site'])) {
					$params[] = 'site='.$options->language;
				}
				$params = implode("\n", $params);

				// Update the language settings in the language manager.
				$db->setQuery(
					'UPDATE `#__components`' .
					' SET `params` = '.$db->Quote($params) .
					' WHERE `option`="com_languages"'
				);

				// Check for errors.
				if ($db->getErrorNum()) {
					$this->setError($db->getErrorMsg());
					$return = false;
				}
			}
		}

		return true;
	}

	function installSampleData($options)
	{
		// Get the options as a JObject for easier handling.
		$options = JArrayHelper::toObject($options, 'JObject');

		// Get a database object.
		$db = & JInstallationHelperDatabase::getDBO($options->db_type, $options->db_host, $options->db_user, $options->db_pass, $options->db_name, $options->db_prefix);

		// Check for errors.
		if (JError::isError($db)) {
			$this->setError(JText::sprintf('WARNNOTCONNECTDB', $db->toString()));
			return false;
		}

		// Check for database errors.
		if ($err = $db->getErrorNum()) {
			$this->setError(JText::sprintf('WARNNOTCONNECTDB', $db->getErrorNum()));
			return false;
		}

		// Build the path to the sample data file.
		$type = $options->db_type;
		if ($type == 'mysqli') {
			$type = 'mysql';
		}
		$data = JPATH_INSTALLATION.'/sql/'.$type.'/sample_data.sql';

		// Attempt to import the database schema.
		if (!$this->populateDatabase($db, $data)) {
			$this->setError(JText::sprintf('Install_Error_DB', $this->getError()));
			return false;
		}

		return true;
	}

	/**
	 * Method to get a JDatabase object.
	 *
	 * @access	public
	 * @param	string	The database driver to use.
	 * @param	string	The hostname to connect on.
	 * @param	string	The user name to connect with.
	 * @param	string	The password to use for connection authentication.
	 * @param	string	The database to use.
	 * @param	string	The table prefix to use.
	 * @param	boolean True if the database should be selected.
	 * @return	mixed	JDatabase object on success, JException on error.
	 * @since	1.0
	 */
	function & getDbo($driver, $host, $user, $password, $database, $prefix, $select = true)
	{
		static $db;

		if (!$db) {
			// Build the connection options array.
			$options = array (
				'driver' => $driver,
				'host' => $host,
				'user' => $user,
				'password' => $password,
				'database' => $database,
				'prefix' => $prefix,
				'select' => $select
			);

			// Get a database object.
			$db = & JDatabase::getInstance($options);
		}

		return $db;
	}

	/**
	 * Method to backup all tables in a database with a given prefix.
	 *
	 * @access	public
	 * @param	object	JDatabase object.
	 * @param	string	Name of the database to process.
	 * @param	string	Database table prefix.
	 * @return	boolean	True on success.
	 * @since	1.0
	 */
	function backupDatabase(& $db, $name, $prefix)
	{
		// Initialize variables.
		$return = true;
		$backup = 'bak_';

		// Get the tables in the database.
		$db->setQuery(
			'SHOW TABLES' .
			' FROM '.$db->nameQuote($name)
		);
		if ($tables = $db->loadResultArray())
		{
			foreach ($tables as $table)
			{
				// If the table uses the given prefix, back it up.
				if (strpos($table, $prefix) === 0)
				{
					// Backup table name.
					$backupTable = str_replace($prefix, $backup, $table);

					// Drop the backup table.
					$db->setQuery(
						'DROP TABLE IF EXISTS '.$db->nameQuote($backupTable)
					);
					$db->query();

					// Check for errors.
					if ($db->getErrorNum()) {
						$this->setError($db->getErrorMsg());
						$return = false;
					}

					// Rename the current table to the backup table.
					$db->setQuery(
						'RENAME TABLE '.$db->nameQuote($table).' TO '.$db->nameQuote($backupTable)
					);
					$db->query();

					// Check for errors.
					if ($db->getErrorNum()) {
						$this->setError($db->getErrorMsg());
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
	 * @access	public
	 * @param	object	JDatabase object.
	 * @param	string	Name of the database to create.
	 * @param	boolean True if the database supports the UTF-8 character set.
	 * @return	boolean	True on success.
	 * @since	1.0
	 */
	function createDatabase(& $db, $name, $utf)
	{
		// Build the create database query.
		if ($utf) {
			$query = 'CREATE DATABASE '.$db->nameQuote($name).' CHARACTER SET `utf8`';
		}
		else {
			$query = 'CREATE DATABASE '.$db->nameQuote($name);
		}

		// Run the create database query.
		$db->setQuery($query);
		$db->query();

		// If an error occurred return false.
		if ($db->getErrorNum()) {
			return false;
		}

		return true;
	}

	/**
	 * Method to delete all tables in a database with a given prefix.
	 *
	 * @access	public
	 * @param	object	JDatabase object.
	 * @param	string	Name of the database to process.
	 * @param	string	Database table prefix.
	 * @return	boolean	True on success.
	 * @since	1.0
	 */
	function deleteDatabase(& $db, $name, $prefix)
	{
		// Initialize variables.
		$return = true;

		// Get the tables in the database.
		$db->setQuery(
			'SHOW TABLES FROM '.$db->nameQuote($name)
		);
		if ($tables = $db->loadResultArray())
		{
			foreach ($tables as $table)
			{
				// If the table uses the given prefix, drop it.
				if (strpos($table, $prefix) === 0)
				{
					// Drop the table.
					$db->setQuery(
						'DROP TABLE IF EXISTS '.$db->nameQuote($table)
					);
					$db->query();

					// Check for errors.
					if ($db->getErrorNum()) {
						$this->setError($db->getErrorMsg());
						$return = false;
					}
				}
			}
		}

		return $return;
	}

	/**
	 * Method to import a database schema from a file.
	 *
	 * @access	public
	 * @param	object	JDatabase object.
	 * @param	string	Path to the schema file.
	 * @return	boolean	True on success.
	 * @since	1.0
	 */
	function populateDatabase(& $db, $schema)
	{
		// Initialize variables.
		$return = true;

		// Get the contents of the schema file.
		if (!($buffer = file_get_contents($schema))) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Get an array of queries from the schema and process them.
		$queries = $this->_splitQueries($buffer);
		foreach ($queries as $query)
		{
			// Trim any whitespace.
			$query = trim($query);

			// If the query isn't empty and is not a comment, execute it.
			if (!empty($query) && ($query{0} != '#'))
			{
				// Execute the query.
				$db->setQuery($query);
				$db->query();

				// Check for errors.
				if ($db->getErrorNum()) {
					$this->setError($db->getErrorMsg());
					$return = false;
				}
			}
		}

		return $return;
	}

	/**
	 * Method to set the database character set to UTF-8.
	 *
	 * @access	public
	 * @param	object	JDatabase object.
	 * @param	string	Name of the database to process.
	 * @return	boolean	True on success.
	 * @since	1.0
	 */
	function setDatabaseCharset(& $db, $name)
	{
		// Only alter the database if it supports the character set.
		if ($db->hasUTF())
		{
			// Run the create database query.
			$db->setQuery(
				'ALTER DATABASE '.$db->nameQuote($name).' CHARACTER' .
				' SET `utf8`'
			);
			$db->query();

			// If an error occurred return false.
			if ($db->getErrorNum()) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to split up queries from a schema file into an array.
	 *
	 * @access	protected
	 * @param	string	SQL schema.
	 * @return	array	Queries to perform.
	 * @since	1.0
	 */
	function _splitQueries($sql)
	{
		// Initialize variables.
		$buffer		= array();
		$queries	= array();
		$in_string	= false;

		// Trim any whitespace.
		$sql = trim($sql);

		// Remove comment lines.
		$sql = preg_replace("/\n\#[^\n]*/", '', "\n".$sql);

		// Parse the schema file to break up queries.
		for ($i = 0; $i < strlen($sql) - 1; $i ++)
		{
			if ($sql[$i] == ";" && !$in_string) {
				$queries[] = substr($sql, 0, $i);
				$sql = substr($sql, $i +1);
				$i = 0;
			}

			if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\") {
				$in_string = false;
			}
			elseif (!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset ($buffer[0]) || $buffer[0] != "\\")) {
				$in_string = $sql[$i];
			}
			if (isset ($buffer[1])) {
				$buffer[0] = $buffer[1];
			}
			$buffer[1] = $sql[$i];
		}

		// If the is anything left over, add it to the queries.
		if (!empty($sql)) {
			$queries[] = $sql;
		}

		return $queries;
	}
}
