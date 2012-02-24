<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * MySQL import driver.
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @since       11.1
 */
class JDatabaseImporterMysqli extends JDatabaseImporterMysql
{
	/**
	 * Checks if all data and options are in order prior to exporting.
	 *
	 * @return  JDatabaseImporterMysqli  Method supports chaining.
	 *
	 * @since   11.1
	 * @throws  Exception if an error is encountered.
	 */
	public function check()
	{
		// Check if the db connector has been set.
		if (!($this->db instanceof JDatabaseDriverMysqli))
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
	 * @param   JDatabaseDriverMysqli  $db  The database connector.
	 *
	 * @return  JDatabaseImporterMysqli  Method supports chaining.
	 *
	 * @since   11.1
	 */
	public function setDbo(JDatabaseDriverMysqli $db)
	{
		$this->db = $db;

		return $this;
	}
}
