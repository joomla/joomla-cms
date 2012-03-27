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
class JDatabaseImporterMysql extends JDatabaseImporter
{
	/**
	 * @var    array  An array of cached data.
	 * @since  11.1
	 */
	protected $cache = array();

	/**
	 * The database connector to use for exporting structure and/or data.
	 *
	 * @var    JDatabaseDriverMysql
	 * @since  11.1
	 */
	protected $db = null;

	/**
	 * The input source.
	 *
	 * @var    mixed
	 * @since  11.1
	 */
	protected $from = array();

	/**
	 * The type of input format (XML).
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $asFormat = 'xml';

	/**
	 * An array of options for the exporter.
	 *
	 * @var    object
	 * @since  11.1
	 */
	protected $options = null;

	/**
	 * Constructor.
	 *
	 * Sets up the default options for the exporter.
	 *
	 * @since   11.1
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
	 * @return  JDatabaseImporterMysql  Method supports chaining.
	 *
	 * @since   11.1
	 */
	public function asXml()
	{
		$this->asFormat = 'xml';

		return $this;
	}

	/**
	 * Checks if all data and options are in order prior to exporting.
	 *
	 * @return  JDatabaseImporterMysql  Method supports chaining.
	 *
	 * @since   11.1
	 * @throws  Exception if an error is encountered.
	 */
	public function check()
	{
		// Check if the db connector has been set.
		if (!($this->db instanceof JDatabaseDriverMysql))
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
	 * Specifies the data source to import.
	 *
	 * @param   mixed  $from  The data source to import.
	 *
	 * @return  JDatabaseImporterMysql  Method supports chaining.
	 *
	 * @since   11.1
	 */
	public function from($from)
	{
		$this->from = $from;

		return $this;
	}

	/**
	 * Get the SQL syntax to add a column.
	 *
	 * @param   string            $table  The table name.
	 * @param   SimpleXMLElement  $field  The XML field definition.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	protected function getAddColumnSQL($table, SimpleXMLElement $field)
	{
		$sql = 'ALTER TABLE ' . $this->db->quoteName($table) . ' ADD COLUMN ' . $this->getColumnSQL($field);

		return $sql;
	}

	/**
	 * Get the SQL syntax to add a key.
	 *
	 * @param   string  $table  The table name.
	 * @param   array   $keys   An array of the fields pertaining to this key.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	protected function getAddKeySQL($table, $keys)
	{
		$sql = 'ALTER TABLE ' . $this->db->quoteName($table) . ' ADD ' . $this->getKeySQL($keys);

		return $sql;
	}

	/**
	 * Get alters for table if there is a difference.
	 *
	 * @param   SimpleXMLElement  $structure  The XML structure pf the table.
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	protected function getAlterTableSQL(SimpleXMLElement $structure)
	{
		// Initialise variables.
		$table = $this->getRealTableName($structure['name']);
		$oldFields = $this->db->getTableColumns($table);
		$oldKeys = $this->db->getTableKeys($table);
		$alters = array();

		// Get the fields and keys from the XML that we are aiming for.
		$newFields = $structure->xpath('field');
		$newKeys = $structure->xpath('key');

		// Loop through each field in the new structure.
		foreach ($newFields as $field)
		{
			$fName = (string) $field['Field'];

			if (isset($oldFields[$fName]))
			{
				// The field exists, check it's the same.
				$column = $oldFields[$fName];

				// Test whether there is a change.
				$change = ((string) $field['Type'] != $column->Type) || ((string) $field['Null'] != $column->Null)
					|| ((string) $field['Default'] != $column->Default) || ((string) $field['Extra'] != $column->Extra);

				if ($change)
				{
					$alters[] = $this->getChangeColumnSQL($table, $field);
				}

				// Unset this field so that what we have left are fields that need to be removed.
				unset($oldFields[$fName]);
			}
			else
			{
				// The field is new.
				$alters[] = $this->getAddColumnSQL($table, $field);
			}
		}

		// Any columns left are orphans
		foreach ($oldFields as $name => $column)
		{
			// Delete the column.
			$alters[] = $this->getDropColumnSQL($table, $name);
		}

		// Get the lookups for the old and new keys.
		$oldLookup = $this->getKeyLookup($oldKeys);
		$newLookup = $this->getKeyLookup($newKeys);

		// Loop through each key in the new structure.
		foreach ($newLookup as $name => $keys)
		{
			// Check if there are keys on this field in the existing table.
			if (isset($oldLookup[$name]))
			{
				$same = true;
				$newCount = count($newLookup[$name]);
				$oldCount = count($oldLookup[$name]);

				// There is a key on this field in the old and new tables. Are they the same?
				if ($newCount == $oldCount)
				{
					// Need to loop through each key and do a fine grained check.
					for ($i = 0; $i < $newCount; $i++)
					{
						$same = (((string) $newLookup[$name][$i]['Non_unique'] == $oldLookup[$name][$i]->Non_unique)
							&& ((string) $newLookup[$name][$i]['Column_name'] == $oldLookup[$name][$i]->Column_name)
							&& ((string) $newLookup[$name][$i]['Seq_in_index'] == $oldLookup[$name][$i]->Seq_in_index)
							&& ((string) $newLookup[$name][$i]['Collation'] == $oldLookup[$name][$i]->Collation)
							&& ((string) $newLookup[$name][$i]['Index_type'] == $oldLookup[$name][$i]->Index_type));

						/*
						Debug.
						echo '<pre>';
						echo '<br />Non_unique:   '.
							((string) $newLookup[$name][$i]['Non_unique'] == $oldLookup[$name][$i]->Non_unique ? 'Pass' : 'Fail').' '.
							(string) $newLookup[$name][$i]['Non_unique'].' vs '.$oldLookup[$name][$i]->Non_unique;
						echo '<br />Column_name:  '.
							((string) $newLookup[$name][$i]['Column_name'] == $oldLookup[$name][$i]->Column_name ? 'Pass' : 'Fail').' '.
							(string) $newLookup[$name][$i]['Column_name'].' vs '.$oldLookup[$name][$i]->Column_name;
						echo '<br />Seq_in_index: '.
							((string) $newLookup[$name][$i]['Seq_in_index'] == $oldLookup[$name][$i]->Seq_in_index ? 'Pass' : 'Fail').' '.
							(string) $newLookup[$name][$i]['Seq_in_index'].' vs '.$oldLookup[$name][$i]->Seq_in_index;
						echo '<br />Collation:    '.
							((string) $newLookup[$name][$i]['Collation'] == $oldLookup[$name][$i]->Collation ? 'Pass' : 'Fail').' '.
							(string) $newLookup[$name][$i]['Collation'].' vs '.$oldLookup[$name][$i]->Collation;
						echo '<br />Index_type:   '.
							((string) $newLookup[$name][$i]['Index_type'] == $oldLookup[$name][$i]->Index_type ? 'Pass' : 'Fail').' '.
							(string) $newLookup[$name][$i]['Index_type'].' vs '.$oldLookup[$name][$i]->Index_type;
						echo '<br />Same = '.($same ? 'true' : 'false');
						echo '</pre>';
						 */

						if (!$same)
						{
							// Break out of the loop. No need to check further.
							break;
						}
					}
				}
				else
				{
					// Count is different, just drop and add.
					$same = false;
				}

				if (!$same)
				{
					$alters[] = $this->getDropKeySQL($table, $name);
					$alters[] = $this->getAddKeySQL($table, $keys);
				}

				// Unset this field so that what we have left are fields that need to be removed.
				unset($oldLookup[$name]);
			}
			else
			{
				// This is a new key.
				$alters[] = $this->getAddKeySQL($table, $keys);
			}
		}

		// Any keys left are orphans.
		foreach ($oldLookup as $name => $keys)
		{
			if (strtoupper($name) == 'PRIMARY')
			{
				$alters[] = $this->getDropPrimaryKeySQL($table);
			}
			else
			{
				$alters[] = $this->getDropKeySQL($table, $name);
			}
		}

		return $alters;
	}

	/**
	 * Get the syntax to alter a column.
	 *
	 * @param   string            $table  The name of the database table to alter.
	 * @param   SimpleXMLElement  $field  The XML definition for the field.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	protected function getChangeColumnSQL($table, SimpleXMLElement $field)
	{
		$sql = 'ALTER TABLE ' . $this->db->quoteName($table) . ' CHANGE COLUMN ' . $this->db->quoteName((string) $field['Field']) . ' '
			. $this->getColumnSQL($field);

		return $sql;
	}

	/**
	 * Get the SQL syntax for a single column that would be included in a table create or alter statement.
	 *
	 * @param   SimpleXMLElement  $field  The XML field definition.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	protected function getColumnSQL(SimpleXMLElement $field)
	{
		// Initialise variables.
		// TODO Incorporate into parent class and use $this.
		$blobs = array('text', 'smalltext', 'mediumtext', 'largetext');

		$fName = (string) $field['Field'];
		$fType = (string) $field['Type'];
		$fNull = (string) $field['Null'];
		$fDefault = isset($field['Default']) ? (string) $field['Default'] : null;
		$fExtra = (string) $field['Extra'];

		$sql = $this->db->quoteName($fName) . ' ' . $fType;

		if ($fNull == 'NO')
		{
			if (in_array($fType, $blobs) || $fDefault === null)
			{
				$sql .= ' NOT NULL';
			}
			else
			{
				// TODO Don't quote numeric values.
				$sql .= ' NOT NULL DEFAULT ' . $this->db->quote($fDefault);
			}
		}
		else
		{
			if ($fDefault === null)
			{
				$sql .= ' DEFAULT NULL';
			}
			else
			{
				// TODO Don't quote numeric values.
				$sql .= ' DEFAULT ' . $this->db->quote($fDefault);
			}
		}

		if ($fExtra)
		{
			$sql .= ' ' . strtoupper($fExtra);
		}

		return $sql;
	}

	/**
	 * Get the SQL syntax to drop a column.
	 *
	 * @param   string  $table  The table name.
	 * @param   string  $name   The name of the field to drop.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	protected function getDropColumnSQL($table, $name)
	{
		$sql = 'ALTER TABLE ' . $this->db->quoteName($table) . ' DROP COLUMN ' . $this->db->quoteName($name);

		return $sql;
	}

	/**
	 * Get the SQL syntax to drop a key.
	 *
	 * @param   string  $table  The table name.
	 * @param   string  $name   The name of the key to drop.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	protected function getDropKeySQL($table, $name)
	{
		$sql = 'ALTER TABLE ' . $this->db->quoteName($table) . ' DROP KEY ' . $this->db->quoteName($name);

		return $sql;
	}

	/**
	 * Get the SQL syntax to drop a key.
	 *
	 * @param   string  $table  The table name.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	protected function getDropPrimaryKeySQL($table)
	{
		$sql = 'ALTER TABLE ' . $this->db->quoteName($table) . ' DROP PRIMARY KEY';

		return $sql;
	}

	/**
	 * Get the details list of keys for a table.
	 *
	 * @param   array  $keys  An array of objects that comprise the keys for the table.
	 *
	 * @return  array  The lookup array. array({key name} => array(object, ...))
	 *
	 * @since   11.1
	 * @throws  Exception
	 */
	protected function getKeyLookup($keys)
	{
		// First pass, create a lookup of the keys.
		$lookup = array();
		foreach ($keys as $key)
		{
			if ($key instanceof SimpleXMLElement)
			{
				$kName = (string) $key['Key_name'];
			}
			else
			{
				$kName = $key->Key_name;
			}
			if (empty($lookup[$kName]))
			{
				$lookup[$kName] = array();
			}
			$lookup[$kName][] = $key;
		}

		return $lookup;
	}

	/**
	 * Get the SQL syntax for a key.
	 *
	 * @param   array  $columns  An array of SimpleXMLElement objects comprising the key.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	protected function getKeySQL($columns)
	{
		// TODO Error checking on array and element types.

		$kNonUnique = (string) $columns[0]['Non_unique'];
		$kName = (string) $columns[0]['Key_name'];
		$kColumn = (string) $columns[0]['Column_name'];

		$prefix = '';
		if ($kName == 'PRIMARY')
		{
			$prefix = 'PRIMARY ';
		}
		elseif ($kNonUnique == 0)
		{
			$prefix = 'UNIQUE ';
		}

		$nColumns = count($columns);
		$kColumns = array();

		if ($nColumns == 1)
		{
			$kColumns[] = $this->db->quoteName($kColumn);
		}
		else
		{
			foreach ($columns as $column)
			{
				$kColumns[] = (string) $column['Column_name'];
			}
		}

		$sql = $prefix . 'KEY ' . ($kName != 'PRIMARY' ? $this->db->quoteName($kName) : '') . ' (' . implode(',', $kColumns) . ')';

		return $sql;
	}

	/**
	 * Get the real name of the table, converting the prefix wildcard string if present.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  string	The real name of the table.
	 *
	 * @since   11.1
	 */
	protected function getRealTableName($table)
	{
		// TODO Incorporate into parent class and use $this.
		$prefix = $this->db->getPrefix();

		// Replace the magic prefix if found.
		$table = preg_replace('|^#__|', $prefix, $table);

		return $table;
	}

	/**
	 * Merges the incoming structure definition with the existing structure.
	 *
	 * @return  void
	 *
	 * @note    Currently only supports XML format.
	 * @since   11.1
	 * @throws  Exception on error.
	 * @todo    If it's not XML convert to XML first.
	 */
	protected function mergeStructure()
	{
		// Initialise variables.
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
				if ($queries = $this->getAlterTableSQL($xml->database->table_structure))
				{
					// Run the queries to upgrade the data structure.
					foreach ($queries as $query)
					{
						$this->db->setQuery((string) $query);
						if (!$this->db->execute())
						{
							$this->addLog('Fail: ' . $this->db->getQuery());
							throw new Exception($this->db->getErrorMsg());
						}
						else
						{
							$this->addLog('Pass: ' . $this->db->getQuery());
						}
					}

				}
			}
			else
			{
				// This is a new table.
				$sql = $this->xmlToCreate($table);

				$this->db->setQuery((string) $sql);
				if (!$this->db->execute())
				{
					$this->addLog('Fail: ' . $this->db->getQuery());
					throw new Exception($this->db->getErrorMsg());
				}
				else
				{
					$this->addLog('Pass: ' . $this->db->getQuery());
				}
			}
		}
	}

	/**
	 * Sets the database connector to use for exporting structure and/or data from MySQL.
	 *
	 * @param   JDatabaseDriverMysql  $db  The database connector.
	 *
	 * @return  JDatabaseImporterMysql  Method supports chaining.
	 *
	 * @since   11.1
	 */
	public function setDbo(JDatabaseDriverMysql $db)
	{
		$this->db = $db;

		return $this;
	}

	/**
	 * Sets an internal option to merge the structure based on the input data.
	 *
	 * @param   boolean  $setting  True to export the structure, false to not.
	 *
	 * @return  JDatabaseImporterMysql  Method supports chaining.
	 *
	 * @since   11.1
	 */
	public function withStructure($setting = true)
	{
		$this->options->withStructure = (boolean) $setting;

		return $this;
	}
}
