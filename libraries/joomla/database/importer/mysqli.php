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
 * MySQLi import driver.
 *
 * @since  11.1
 */
class JDatabaseImporterMysqli extends JDatabaseImporter
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
	 * Get the SQL syntax to add a table.
	 *
	 * @param   SimpleXMLElement  $table  The table information.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	protected function xmlToCreate(SimpleXMLElement $table)
	{
		$existingTables = $this->db->getTableList();
		$tableName = (string) $table['name'];

		if (in_array($tableName, $existingTables))
		{
			throw new RuntimeException('The table you are trying to create already exists');
		}

		$createTableStatement = 'CREATE TABLE ' . $this->db->quoteName($tableName) . ' (';

		foreach ($table->xpath('field') as $field)
		{
			$createTableStatement .= $this->getColumnSQL($field) . ', ';
		}

		$newLookup = $this->getKeyLookup($table->xpath('key'));

		// Loop through each key in the new structure.
		foreach ($newLookup as $key)
		{
			$createTableStatement .= $this->getKeySQL($key) . ', ';
		}

		// Remove the comma after the last key
		$createTableStatement = rtrim($createTableStatement, ', ');

		$createTableStatement .= ')';

		return $createTableStatement;
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
	protected function getAddColumnSql($table, SimpleXMLElement $field)
	{
		return 'ALTER TABLE ' . $this->db->quoteName($table) . ' ADD COLUMN ' . $this->getColumnSql($field);
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
	protected function getAddKeySql($table, $keys)
	{
		return 'ALTER TABLE ' . $this->db->quoteName($table) . ' ADD ' . $this->getKeySql($keys);
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
	protected function getAlterTableSql(SimpleXMLElement $structure)
	{
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
					$alters[] = $this->getChangeColumnSql($table, $field);
				}

				// Unset this field so that what we have left are fields that need to be removed.
				unset($oldFields[$fName]);
			}
			else
			{
				// The field is new.
				$alters[] = $this->getAddColumnSql($table, $field);
			}
		}

		// Any columns left are orphans
		foreach ($oldFields as $name => $column)
		{
			// Delete the column.
			$alters[] = $this->getDropColumnSql($table, $name);
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
							&& ((string) $newLookup[$name][$i]['Sub_part'] == $oldLookup[$name][$i]->Sub_part)
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
					$alters[] = $this->getDropKeySql($table, $name);
					$alters[] = $this->getAddKeySql($table, $keys);
				}

				// Unset this field so that what we have left are fields that need to be removed.
				unset($oldLookup[$name]);
			}
			else
			{
				// This is a new key.
				$alters[] = $this->getAddKeySql($table, $keys);
			}
		}

		// Any keys left are orphans.
		foreach ($oldLookup as $name => $keys)
		{
			if (strtoupper($name) == 'PRIMARY')
			{
				$alters[] = $this->getDropPrimaryKeySql($table);
			}
			else
			{
				$alters[] = $this->getDropKeySql($table, $name);
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
	protected function getChangeColumnSql($table, SimpleXMLElement $field)
	{
		return 'ALTER TABLE ' . $this->db->quoteName($table) . ' CHANGE COLUMN ' . $this->db->quoteName((string) $field['Field']) . ' '
			. $this->getColumnSql($field);
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
	protected function getColumnSql(SimpleXMLElement $field)
	{
		// TODO Incorporate into parent class and use $this.
		$blobs = array('text', 'smalltext', 'mediumtext', 'largetext');

		$fName = (string) $field['Field'];
		$fType = (string) $field['Type'];
		$fNull = (string) $field['Null'];
		$fDefault = isset($field['Default']) ? (string) $field['Default'] : null;
		$fExtra = (string) $field['Extra'];

		$query = $this->db->quoteName($fName) . ' ' . $fType;

		if ($fNull == 'NO')
		{
			if (in_array($fType, $blobs) || $fDefault === null)
			{
				$query .= ' NOT NULL';
			}
			else
			{
				// TODO Don't quote numeric values.
				if (strpos($fDefault, 'CURRENT') !== false)
				{
					$query .= ' NOT NULL DEFAULT CURRENT_TIMESTAMP()';
				}
				else
				{
					$query .= ' NOT NULL DEFAULT ' . $this->db->quote($fDefault);
				}
			}
		}
		else
		{
			if ($fDefault === null)
			{
				$query .= ' DEFAULT NULL';
			}
			else
			{
				// TODO Don't quote numeric values.
				$query .= ' DEFAULT ' . $this->db->quote($fDefault);
			}
		}

		if ($fExtra)
		{
			$query .= ' ' . strtoupper($fExtra);
		}

		return $query;
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
	protected function getDropKeySql($table, $name)
	{
		return 'ALTER TABLE ' . $this->db->quoteName($table) . ' DROP KEY ' . $this->db->quoteName($name);
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
	protected function getDropPrimaryKeySql($table)
	{
		return 'ALTER TABLE ' . $this->db->quoteName($table) . ' DROP PRIMARY KEY';
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
	protected function getKeySql($columns)
	{
		$kNonUnique = (string) $columns[0]['Non_unique'];
		$kName      = (string) $columns[0]['Key_name'];
		$prefix     = '';

		if ($kName == 'PRIMARY')
		{
			$prefix = 'PRIMARY ';
		}
		elseif ($kNonUnique == 0)
		{
			$prefix = 'UNIQUE ';
		}

		$kColumns = array();

		foreach ($columns as $column)
		{
			$kLength = '';

			if (!empty($column['Sub_part']))
			{
				$kLength = '(' . $column['Sub_part'] . ')';
			}

			$kColumns[] = $this->db->quoteName((string) $column['Column_name']) . $kLength;
		}

		return $prefix . 'KEY ' . ($kName != 'PRIMARY' ? $this->db->quoteName($kName) : '') . ' (' . implode(',', $kColumns) . ')';
	}
}
