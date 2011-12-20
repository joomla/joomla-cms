<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

require_once dirname(__FILE__) . '/mysqlimporter.php';

/**
 * MySQL import driver.
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @since       11.1
 */
class JDatabaseImporterMySQLi extends JDatabaseImporterMySQL
{
	/**
	 * Checks if all data and options are in order prior to exporting.
	 *
	 * @return  JDatabaseImporterMySQLi  Method supports chaining.
	 *
	 * @since   11.1
	 * @throws  Exception if an error is encountered.
	 */
	public function check()
	{
		// Check if the db connector has been set.
		if (!($this->db instanceof JDatabaseMySqli))
		{
			throw new Exception('JPLATFORM_ERROR_DATABASE_CONNECTOR_WRONG_TYPE');
		}

		// Check if the tables have been specified.
		if (empty($this->from))
		{
			throw new Exception('JPLATFORM_ERROR_NO_TABLES_SPECIFIED');
		}

		return $this;
	}

	/**
	 * Sets the database connector to use for exporting structure and/or data from MySQL.
	 *
	 * @param   JDatabaseMySQLi  $db  The database connector.
	 *
	 * @return  JDatabaseImporterMySQLi  Method supports chaining.
	 *
	 * @since   11.1
	 */
	public function setDbo(JDatabaseMySQLi $db)
	{
		$this->db = $db;

		return $this;
	}
}
