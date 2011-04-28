<?php
/**
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license    GNU General Public License
 */

require_once JPATH_PLATFORM.'/joomla/database/database/mysqlexporter.php';

/**
 * Class to expose protected properties and methods in JDatabaseMySqlExporter for testing purposes.
 *
 * @package    Joomla.UnitTest
 * @subpackage Database
 */
class JDatabaseMySqlExporterInspector extends JDatabaseExporterMySql
{
	/**
	 * Gets any property from the class.
	 *
	 * @param   string  $property  The name of the class property.
	 *
	 * @return  mixed   The value of the class property.
	 * @since   11.1
	 */
	public function __get($property)
	{
		return $this->$property;
	}

	/**
	 * Exposes the protected buildXml method.
	 *
	 * @return  string	An XML string
	 * @throws  Exception if an error occurs.
	 * @since   11.1
	 */
	public function buildXml()
	{
		return parent::buildXml();
	}

	/**
	 * Exposes the protected buildXmlStructure method.
	 *
	 * @return  array  An array of XML lines (strings).
	 * @throws  Exception if an error occurs.
	 * @since   11.1
	 */
	public function buildXmlStructure()
	{
		return parent::buildXmlStructure();
	}

	/**
	 * Exposes the protected check method.
	 *
	 * @return  void
	 * @since   11.1
	 */
	public function check()
	{
		return parent::check();
	}

	/**
	 * Exposes the protected getColumns method.
	 *
	 * @param   mixed  $table  The name of a table or an array of table names.
	 *
	 * @return  array  An array of column definitions.
	 * @since   11.1
	 */
	public function getColumns($table)
	{
		return parent::getColumns($table);
	}

	/**
	 * Exposes the protected getGenericTableName method.
	 *
	 * @param   string  $table  The name of a table.
	 *
	 * @return  string  The name of the table with the database prefix replaced with #__.
	 * @since   11.1
	 */
	public function getGenericTableName($table)
	{
		return parent::getGenericTableName($table);
	}

	/**
	 * Exposes the protected getKeys method.
	 *
	 * @param   mixed  $table  The name of a table or an array of table names.
	 *
	 * @return  array  An array of key definitions.
	 * @since   11.1
	 */
	public function getKeys($table)
	{
		return parent::getKeys($table);
	}

	/**
	 * Exposes the protected withStructure method.
	 *
	 * @param	boolean	$setting	True to export the structure, false to not.
	 *
	 * @return	void
	 * @since	11.1
	 */
	public function withStructure($setting = true)
	{
		return parent::withStructure($setting);
	}

}