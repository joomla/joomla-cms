<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla Platform Database Importer Class
 *
 * @since  12.1
 */
abstract class JDatabaseImporter
{
	/**
	 * @var    array  An array of cached data.
	 * @since  13.1
	 */
	protected $cache = array();

	/**
	 * The database connector to use for exporting structure and/or data.
	 *
	 * @var    JDatabaseDriver
	 * @since  13.1
	 */
	protected $db = null;

	/**
	 * The input source.
	 *
	 * @var    mixed
	 * @since  13.1
	 */
	protected $from = array();

	/**
	 * The type of input format (XML).
	 *
	 * @var    string
	 * @since  13.1
	 */
	protected $asFormat = 'xml';

	/**
	 * An array of options for the exporter.
	 *
	 * @var    object
	 * @since  13.1
	 */
	protected $options = null;

	/**
	 * Constructor.
	 *
	 * Sets up the default options for the exporter.
	 *
	 * @since   13.1
	 */
	public function __construct()
	{
		$this->options = new stdClass;

		$this->cache = array('columns' => array(), 'keys' => array());

		// Set up the class defaults:

		// Import with only structure
		$this->withStructure();

		// Export as XML.
		$this->asXml();

		// Default destination is a string using $output = (string) $exporter;
	}

	/**
	 * Set the output option for the exporter to XML format.
	 *
	 * @return  JDatabaseImporter  Method supports chaining.
	 *
	 * @since   13.1
	 */
	public function asXml()
	{
		$this->asFormat = 'xml';

		return $this;
	}

	/**
	 * Checks if all data and options are in order prior to exporting.
	 *
	 * @return  JDatabaseImporter  Method supports chaining.
	 *
	 * @since   13.1
	 * @throws  Exception if an error is encountered.
	 */
	abstract public function check();

	/**
	 * Specifies the data source to import.
	 *
	 * @param   mixed  $from  The data source to import.
	 *
	 * @return  JDatabaseImporter  Method supports chaining.
	 *
	 * @since   13.1
	 */
	public function from($from)
	{
		$this->from = $from;

		return $this;
	}

	/**
	 * Get the SQL syntax to drop a column.
	 *
	 * @param   string  $table  The table name.
	 * @param   string  $name   The name of the field to drop.
	 *
	 * @return  string
	 *
	 * @since   13.1
	 */
	protected function getDropColumnSql($table, $name)
	{
		return 'ALTER TABLE ' . $this->db->quoteName($table) . ' DROP COLUMN ' . $this->db->quoteName($name);
	}

	/**
	 * Get the real name of the table, converting the prefix wildcard string if present.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  string	The real name of the table.
	 *
	 * @since   13.1
	 */
	protected function getRealTableName($table)
	{
		$prefix = $this->db->getPrefix();

		// Replace the magic prefix if found.
		$table = preg_replace('|^#__|', $prefix, $table);

		return $table;
	}

	/**
	 * Import the data from the source into the existing tables.
	 *
	 * @return  void
	 *
	 * @note    Currently only supports XML format.
	 * @since   3.6
	 * @throws  RuntimeException on error.
	 */
	public function importData()
	{
		if ($this->from instanceof SimpleXMLElement)
		{
			$xml = $this->from;
		}
		else
		{
			$xml = new SimpleXMLElement($this->from);
		}

		// Get all the table definitions.
		$xmlTables = $xml->xpath('database/table_data');

		foreach ($xmlTables as $table)
		{
			$tableName = (string) $table['name'];
			$rows = $table->children();

			foreach ($rows as $row)
			{
				if ($row->getName() == 'row')
				{
					$entry = new stdClass;

					foreach ($row->children() as $data)
					{
						$entry->{(string) $data['name']} = (string) $data;
					}

					$this->db->insertObject($tableName, $entry);
				}
			}
		}
	}

	/**
	 * Merges the incoming structure definition with the existing structure.
	 *
	 * @return  void
	 *
	 * @note    Currently only supports XML format.
	 * @since   13.1
	 * @throws  RuntimeException on error.
	 */
	public function mergeStructure()
	{
		$prefix = $this->db->getPrefix();
		$tables = $this->db->getTableList();

		if ($this->from instanceof SimpleXMLElement)
		{
			$xml = $this->from;
		}
		else
		{
			$xml = new SimpleXMLElement($this->from);
		}

		// Get all the table definitions.
		$xmlTables = $xml->xpath('database/table_structure');

		foreach ($xmlTables as $table)
		{
			// Convert the magic prefix into the real table name.
			$tableName = (string) $table['name'];
			$tableName = preg_replace('|^#__|', $prefix, $tableName);

			if (in_array($tableName, $tables))
			{
				// The table already exists. Now check if there is any difference.
				if ($queries = $this->getAlterTableSql($xml->database->table_structure))
				{
					// Run the queries to upgrade the data structure.
					foreach ($queries as $query)
					{
						$this->db->setQuery((string) $query);
						$this->db->execute();
					}
				}
			}
			else
			{
				// This is a new table.
				$sql = $this->xmlToCreate($table);

				$this->db->setQuery((string) $sql);
				$this->db->execute();
			}
		}
	}

	/**
	 * Sets the database connector to use for exporting structure and/or data.
	 *
	 * @param   JDatabaseDriver  $db  The database connector.
	 *
	 * @return  JDatabaseImporter  Method supports chaining.
	 *
	 * @since   13.1
	 */
	public function setDbo(JDatabaseDriver $db)
	{
		$this->db = $db;

		return $this;
	}

	/**
	 * Sets an internal option to merge the structure based on the input data.
	 *
	 * @param   boolean  $setting  True to export the structure, false to not.
	 *
	 * @return  JDatabaseImporter  Method supports chaining.
	 *
	 * @since   13.1
	 */
	public function withStructure($setting = true)
	{
		$this->options->withStructure = (boolean) $setting;

		return $this;
	}
}
