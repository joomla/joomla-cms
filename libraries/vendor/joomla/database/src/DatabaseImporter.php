<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database;

/**
 * Joomla Framework Database Importer Class
 *
 * @since  1.0
 */
abstract class DatabaseImporter
{
	/**
	 * An array of cached data.
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $cache = ['columns' => [], 'keys' => []];

	/**
	 * The database connector to use for exporting structure and/or data.
	 *
	 * @var    DatabaseInterface
	 * @since  1.0
	 */
	protected $db;

	/**
	 * The input source.
	 *
	 * @var    mixed
	 * @since  1.0
	 */
	protected $from = [];

	/**
	 * The type of input format.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $asFormat = 'xml';

	/**
	 * An array of options for the exporter.
	 *
	 * @var    \stdClass
	 * @since  1.0
	 */
	protected $options;

	/**
	 * Constructor.
	 *
	 * Sets up the default options for the importer.
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		$this->options = new \stdClass;

		// Set up the class defaults:

		// Import with only structure
		$this->withStructure();

		// Export as XML.
		$this->asXml();

		// Default destination is a string using $output = (string) $importer;
	}

	/**
	 * Set the output option for the importer to XML format.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function asXml()
	{
		$this->asFormat = 'xml';

		return $this;
	}

	/**
	 * Checks if all data and options are in order prior to importer.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	abstract public function check();

	/**
	 * Specifies the data source to import.
	 *
	 * @param   \SimpleXMLElement|string  $from  The data source to import, either as a SimpleXMLElement object or XML string.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function from($from)
	{
		$this->from = $from;

		return $this;
	}

	/**
	 * Get the SQL syntax to add a column.
	 *
	 * @param   string             $table  The table name.
	 * @param   \SimpleXMLElement  $field  The XML field definition.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	protected function getAddColumnSql($table, \SimpleXMLElement $field)
	{
		return 'ALTER TABLE ' . $this->db->quoteName($table) . ' ADD COLUMN ' . $this->getColumnSQL($field);
	}

	/**
	 * Get alters for table if there is a difference.
	 *
	 * @param   \SimpleXMLElement  $structure  The XML structure of the table.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	abstract protected function getAlterTableSql(\SimpleXMLElement $structure);

	/**
	 * Get the syntax to alter a column.
	 *
	 * @param   string             $table  The name of the database table to alter.
	 * @param   \SimpleXMLElement  $field  The XML definition for the field.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	protected function getChangeColumnSql($table, \SimpleXMLElement $field)
	{
		return 'ALTER TABLE ' . $this->db->quoteName($table) . ' CHANGE COLUMN ' . $this->db->quoteName((string) $field['Field']) . ' '
			. $this->getColumnSQL($field);
	}

	/**
	 * Get the SQL syntax to drop a column.
	 *
	 * @param   string  $table  The table name.
	 * @param   string  $name   The name of the field to drop.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	protected function getDropColumnSql($table, $name)
	{
		return 'ALTER TABLE ' . $this->db->quoteName($table) . ' DROP COLUMN ' . $this->db->quoteName($name);
	}

	/**
	 * Get the details list of keys for a table.
	 *
	 * @param   array  $keys  An array of objects that comprise the keys for the table.
	 *
	 * @return  array  The lookup array. array({key name} => array(object, ...))
	 *
	 * @since   1.0
	 */
	protected function getKeyLookup($keys)
	{
		// First pass, create a lookup of the keys.
		$lookup = [];

		foreach ($keys as $key)
		{
			if ($key instanceof \SimpleXMLElement)
			{
				$kName = (string) $key['Key_name'];
			}
			else
			{
				$kName = $key->Key_name;
			}

			if (empty($lookup[$kName]))
			{
				$lookup[$kName] = [];
			}

			$lookup[$kName][] = $key;
		}

		return $lookup;
	}

	/**
	 * Get the real name of the table, converting the prefix wildcard string if present.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  string	The real name of the table.
	 *
	 * @since   1.0
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
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException on error.
	 */
	public function importData()
	{
		if ($this->from instanceof \SimpleXMLElement)
		{
			$xml = $this->from;
		}
		else
		{
			$xml = new \SimpleXMLElement($this->from);
		}

		// Get all the table definitions.
		$xmlTables = $xml->xpath('database/table_data');

		foreach ($xmlTables as $table)
		{
			// Convert the magic prefix into the real table name.
			$tableName = $this->getRealTableName((string) $table['name']);

			$rows = $table->children();

			foreach ($rows as $row)
			{
				if ($row->getName() == 'row')
				{
					$entry = new \stdClass;

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
	 * @since   1.0
	 * @throws  \RuntimeException on error.
	 */
	public function mergeStructure()
	{
		$tables = $this->db->getTableList();

		if ($this->from instanceof \SimpleXMLElement)
		{
			$xml = $this->from;
		}
		else
		{
			$xml = new \SimpleXMLElement($this->from);
		}

		// Get all the table definitions.
		$xmlTables = $xml->xpath('database/table_structure');

		foreach ($xmlTables as $table)
		{
			// Convert the magic prefix into the real table name.
			$tableName = $this->getRealTableName((string) $table['name']);

			if (\in_array($tableName, $tables, true))
			{
				// The table already exists. Now check if there is any difference.
				if ($queries = $this->getAlterTableSql($table))
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
				$queries = explode(';', (string) $sql);

				foreach ($queries as $query)
				{
					if (!empty($query))
					{
						$this->db->setQuery((string) $query);
						$this->db->execute();
					}
				}
			}
		}
	}

	/**
	 * Sets the database connector to use for exporting structure and/or data.
	 *
	 * @param   DatabaseInterface  $db  The database connector.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setDbo(DatabaseInterface $db)
	{
		$this->db = $db;

		return $this;
	}

	/**
	 * Sets an internal option to merge the structure based on the input data.
	 *
	 * @param   boolean  $setting  True to import the structure, false to not.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function withStructure($setting = true)
	{
		$this->options->withStructure = (boolean) $setting;

		return $this;
	}

	/**
	 * Get the SQL syntax to add a table.
	 *
	 * @param   \SimpleXMLElement  $table  The table information.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	abstract protected function xmlToCreate(\SimpleXMLElement $table);
}
