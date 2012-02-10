<?php
/**
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');

/**
 * Database configuration model for the Joomla Core Installer.
 *
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationModelDatabase extends JModel
{

	function initialise($options)
	{
		// Get the options as a JObject for easier handling.
		$options = JArrayHelper::toObject($options, 'JObject');

		// Load the back-end language files so that the DB error messages work
		$jlang = JFactory::getLanguage();

		// Pre-load en-GB in case the chosen language files do not exist
		$jlang->load('joomla', JPATH_ADMINISTRATOR, 'en-GB', true);

		// Load the selected language
		$jlang->load('joomla', JPATH_ADMINISTRATOR, $options->language, true);

		// Ensure a database type was selected.
		if (empty($options->db_type))
		{
			$this->setError(JText::_('INSTL_DATABASE_INVALID_TYPE'));

			return false;
		}

		try
		{
			// @todo remove deprecated
			JError::$legacy = false;

			JDatabaseInstaller::getInstance($options)
				->check()
				->create()
				->clean()
				->populate()
				->update()
				->localize();
		}
		catch(JDatabaseInstallerException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
		catch(JDatabaseException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	function installSampleData($options)
	{
		// @todo remove deprecated
		JError::$legacy = false;

		// Get the options as a JObject for easier handling.
		$options = JArrayHelper::toObject($options, 'JObject');

		JDatabaseInstaller::getInstance($options)->installSampleData();

		return true;
	}

	/**
	 * Method to backup all tables in a database with a given prefix.
	 *
	 * @param	JDatabase	&$db	JDatabase object.
	 * @param	string		$name	Name of the database to process.
	 * @param	string		$prefix	Database table prefix.
	 *
	 * @return	boolean	True on success.
	 * @since	1.0
	 */
	public function backupDatabase(& $db, $name, $prefix)
	{
		// Initialise variables.
		$return = true;
		$backup = 'bak_' . $prefix;

		// Get the tables in the database.
		//sqlsrv change
		$tables = $db->getTableList();
		if ($tables)
		{
			foreach ($tables as $table)
			{
				// If the table uses the given prefix, back it up.
				if (strpos($table, $prefix) === 0) {
					// Backup table name.
					$backupTable = str_replace($prefix, $backup, $table);

					// Drop the backup table.
					//sqlsrv change
					$query = $db->dropTable($backupTable, true);

					// Check for errors.
					if ($db->getErrorNum()) {
						$this->setError($db->getErrorMsg());
						$return = false;
					}
					// Rename the current table to the backup table.
			        //sqlsrv change
			        $db->renameTable($table, $backupTable, $backup, $prefix);

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
	 * @param	JDatabase	&$db	JDatabase object.
	 * @param	string		$name	Name of the database to create.
	 * @param	boolean 	$utf	True if the database supports the UTF-8 character set.
	 *
	 * @return	boolean	True on success.
	 * @since	1.0
	 */
	public function createDatabase(& $db, $name, $utf)
	{
		// Build the create database query.
		if ($utf) {
			$query = 'CREATE DATABASE '.$db->quoteName($name).' CHARACTER SET utf8';
		}
		else {
			$query = 'CREATE DATABASE '.$db->quoteName($name);
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
	 * @param	JDatabase	&$db	JDatabase object.
	 * @param	string		$name	Name of the database to process.
	 * @param	string		$prefix	Database table prefix.
	 *
	 * @return	boolean	True on success.
	 * @since	1.0
	 */
	public function deleteDatabase(& $db, $name, $prefix)
	{
		// Initialise variables.
		$return = true;

		// Get the tables in the database.
	  	//sqlsrv change
	    $tables = $db->getTableList();
		if ($tables)
		{
			foreach ($tables as $table)
			{
				// If the table uses the given prefix, drop it.
				if (strpos($table, $prefix) === 0) {
					// Drop the table.
					//sqlsrv change
		            $db->dropTable($table);

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
	 * @param	JDatabase	&$db	JDatabase object.
	 * @param	string		$schema	Path to the schema file.
	 *
	 * @return	boolean	True on success.
	 * @since	1.0
	 */
	public function populateDatabase(& $db, $schema)
	{
		// Initialise variables.
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
			if (!empty($query) && ($query{0} != '#')) {
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
	 * @param	JDatabase	&$db	JDatabase object.
	 * @param	string		$name	Name of the database to process.
	 *
	 * @return	boolean	True on success.
	 * @since	1.0
	 */
	public function setDatabaseCharset(& $db, $name)
	{
		// Only alter the database if it supports the character set.
		if ($db->hasUTF()) {
			// Run the create database query.
			$db->setQuery(
				'ALTER DATABASE '.$db->quoteName($name).' CHARACTER' .
				' SET utf8'
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
	 * @param	string	$sql SQL schema.
	 *
	 * @return	array	Queries to perform.
	 * @since	1.0
	 * @access	protected
	 */
	function _splitQueries($sql)
	{
		// Initialise variables.
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
