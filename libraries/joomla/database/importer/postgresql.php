<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * PostgreSQL import driver.
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @since       12.1
 */
class JDatabaseImporterPostgresql extends JDatabaseImporter
{
	/**
	 * @var    array  An array of cached data.
	 * @since  12.1
	 */
	protected $cache = array();

	/**
	 * The database connector to use for exporting structure and/or data.
	 *
	 * @var    JDatabaseDriverPostgresql
	 * @since  12.1
	 */
	protected $db = null;

	/**
	 * The input source.
	 *
	 * @var    mixed
	 * @since  12.1
	 */
	protected $from = array();

	/**
	 * The type of input format (XML).
	 *
	 * @var    string
	 * @since  12.1
	 */
	protected $asFormat = 'xml';

	/**
	 * An array of options for the exporter.
	 *
	 * @var    object
	 * @since  12.1
	 */
	protected $options = null;

	/**
	 * Constructor.
	 *
	 * Sets up the default options for the exporter.
	 *
	 * @since   12.1
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
	 * @return  JDatabaseImporterPostgresql  Method supports chaining.
	 *
	 * @since   12.1
	 */
	public function asXml()
	{
		$this->asFormat = 'xml';

		return $this;
	}

	/**
	 * Checks if all data and options are in order prior to exporting.
	 *
	 * @return  JDatabaseImporterPostgresql  Method supports chaining.
	 *
	 * @since   12.1
	 * @throws  Exception if an error is encountered.
	 */
	public function check()
	{
		// Check if the db connector has been set.
		if (!($this->db instanceof JDatabaseDriverPostgresql))
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
	 * @return  JDatabaseImporterPostgresql  Method supports chaining.
	 *
	 * @since   12.1
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
	 * @since   12.1
	 */
	protected function getAddColumnSQL($table, SimpleXMLElement $field)
	{
		$sql = 'ALTER TABLE ' . $this->db->quoteName($table) . ' ADD COLUMN ' . $this->getColumnSQL($field);

		return $sql;
	}

	/**
	 * Get the SQL syntax to add an index.
	 *
	 * @param   SimpleXMLElement  $field  The XML index definition.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	protected function getAddIndexSQL(SimpleXMLElement $field)
	{
		return (string) $field['Query'];
	}

	/**
	 * Get alters for table if there is a difference.
	 *
	 * @param   SimpleXMLElement  $structure  The XML structure of the table.
	 *
	 * @return  array
	 *
	 * @since   12.1
	 */
	protected function getAlterTableSQL(SimpleXMLElement $structure)
	{
		$table = $this->getRealTableName($structure['name']);
		$oldFields = $this->db->getTableColumns($table);
		$oldKeys = $this->db->getTableKeys($table);
		$oldSequence = $this->db->getTableSequences($table);
		$alters = array();

		// Get the fields and keys from the XML that we are aiming for.
		$newFields = $structure->xpath('field');
		$newKeys = $structure->xpath('key');
		$newSequence = $structure->xpath('sequence');

		/* Sequence section */
		$oldSeq = $this->getSeqLookup($oldSequence);
		$newSequenceLook = $this->getSeqLookup($newSequence);

		foreach ($newSequenceLook as $kSeqName => $vSeq)
		{
			if (isset($oldSeq[$kSeqName]))
			{
				// The field exists, check it's the same.
				$column = $oldSeq[$kSeqName][0];

				/* For older database version that doesn't support these fields use default values */
				if (version_compare($this->db->getVersion(), '9.1.0') < 0)
				{
					$column->Min_Value = '1';
					$column->Max_Value = '9223372036854775807';
					$column->Increment = '1';
					$column->Cycle_option = 'NO';
					$column->Start_Value = '1';
				}

				// Test whether there is a change.
				$change = ((string) $vSeq[0]['Type'] != $column->Type) || ((string) $vSeq[0]['Start_Value'] != $column->Start_Value)
					|| ((string) $vSeq[0]['Min_Value'] != $column->Min_Value) || ((string) $vSeq[0]['Max_Value'] != $column->Max_Value)
					|| ((string) $vSeq[0]['Increment'] != $column->Increment) || ((string) $vSeq[0]['Cycle_option'] != $column->Cycle_option)
					|| ((string) $vSeq[0]['Table'] != $column->Table) || ((string) $vSeq[0]['Column'] != $column->Column)
					|| ((string) $vSeq[0]['Schema'] != $column->Schema) || ((string) $vSeq[0]['Name'] != $column->Name);

				if ($change)
				{
					$alters[] = $this->getChangeSequenceSQL($kSeqName, $vSeq);
				}

				// Unset this field so that what we have left are fields that need to be removed.
				unset($oldSeq[$kSeqName]);
			}
			else
			{
				// The sequence is new
				$alters[] = $this->getAddSequenceSQL($newSequenceLook[$kSeqName][0]);
			}
		}

		// Any sequences left are orphans
		foreach ($oldSeq as $name => $column)
		{
			// Delete the sequence.
			$alters[] = $this->getDropSequenceSQL($name);
		}

		/* Field section */
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
					|| ((string) $field['Default'] != $column->Default);

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

		/* Index section */
		// Get the lookups for the old and new keys
		$oldLookup = $this->getIdxLookup($oldKeys);
		$newLookup = $this->getIdxLookup($newKeys);

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
					for ($i = 0; $i < $newCount; $i++)
					{
						// Check only query field -> different query means different index
						$same = ((string) $newLookup[$name][$i]['Query'] == $oldLookup[$name][$i]->Query);

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
					$alters[] = $this->getDropIndexSQL($name);
					$alters[]  = (string) $newLookup[$name][0]['Query'];
				}

				// Unset this field so that what we have left are fields that need to be removed.
				unset($oldLookup[$name]);
			}
			else
			{
				// This is a new key.
				$alters[] = (string) $newLookup[$name][0]['Query'];
			}
		}

		// Any keys left are orphans.
		foreach ($oldLookup as $name => $keys)
		{
			if ($oldLookup[$name][0]->is_primary == 'TRUE')
			{
				$alters[] = $this->getDropPrimaryKeySQL($table, $oldLookup[$name][0]->Index);
			}
			else
			{
				$alters[] = $this->getDropIndexSQL($name);
			}
		}

		return $alters;
	}

	/**
	 * Get the SQL syntax to drop a sequence.
	 *
	 * @param   string  $name  The name of the sequence to drop.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	protected function getDropSequenceSQL($name)
	{
		$sql = 'DROP SEQUENCE ' . $this->db->quoteName($name);

		return $sql;
	}

	/**
	 * Get the syntax to add a sequence.
	 *
	 * @param   SimpleXMLElement  $field  The XML definition for the sequence.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	protected function getAddSequenceSQL($field)
	{
		/* For older database version that doesn't support these fields use default values */
		if (version_compare($this->db->getVersion(), '9.1.0') < 0)
		{
			$field['Min_Value'] = '1';
			$field['Max_Value'] = '9223372036854775807';
			$field['Increment'] = '1';
			$field['Cycle_option'] = 'NO';
			$field['Start_Value'] = '1';
		}

		$sql = 'CREATE SEQUENCE ' . (string) $field['Name'] .
				' INCREMENT BY ' . (string) $field['Increment'] . ' MINVALUE ' . $field['Min_Value'] .
				' MAXVALUE ' . (string) $field['Max_Value'] . ' START ' . (string) $field['Start_Value'] .
				(((string) $field['Cycle_option'] == 'NO' ) ? ' NO' : '' ) . ' CYCLE' .
				' OWNED BY ' . $this->db->quoteName(
									(string) $field['Schema'] . '.' . (string) $field['Table'] . '.' . (string) $field['Column']
								);

		return $sql;
	}

	/**
	 * Get the syntax to alter a sequence.
	 *
	 * @param   SimpleXMLElement  $field  The XML definition for the sequence.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	protected function getChangeSequenceSQL($field)
	{
		/* For older database version that doesn't support these fields use default values */
		if (version_compare($this->db->getVersion(), '9.1.0') < 0)
		{
			$field['Min_Value'] = '1';
			$field['Max_Value'] = '9223372036854775807';
			$field['Increment'] = '1';
			$field['Cycle_option'] = 'NO';
			$field['Start_Value'] = '1';
		}

		$sql = 'ALTER SEQUENCE ' . (string) $field['Name'] .
				' INCREMENT BY ' . (string) $field['Increment'] . ' MINVALUE ' . (string) $field['Min_Value'] .
				' MAXVALUE ' . (string) $field['Max_Value'] . ' START ' . (string) $field['Start_Value'] .
				' OWNED BY ' . $this->db->quoteName(
									(string) $field['Schema'] . '.' . (string) $field['Table'] . '.' . (string) $field['Column']
								);

		return $sql;
	}

	/**
	 * Get the syntax to alter a column.
	 *
	 * @param   string            $table  The name of the database table to alter.
	 * @param   SimpleXMLElement  $field  The XML definition for the field.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	protected function getChangeColumnSQL($table, SimpleXMLElement $field)
	{
		$sql = 'ALTER TABLE ' . $this->db->quoteName($table) . ' ALTER COLUMN ' . $this->db->quoteName((string) $field['Field']) . ' '
			. $this->getAlterColumnSQL($table, $field);

		return $sql;
	}

	/**
	 * Get the SQL syntax for a single column that would be included in a table create statement.
	 *
	 * @param   string            $table  The name of the database table to alter.
	 * @param   SimpleXMLElement  $field  The XML field definition.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	protected function getAlterColumnSQL($table, $field)
	{
		// TODO Incorporate into parent class and use $this.
		$blobs = array('text', 'smalltext', 'mediumtext', 'largetext');

		$fName = (string) $field['Field'];
		$fType = (string) $field['Type'];
		$fNull = (string) $field['Null'];
		$fDefault = (isset($field['Default']) && $field['Default'] != 'NULL' ) ?
						preg_match('/^[0-9]$/', $field['Default']) ? $field['Default'] : $this->db->quote((string) $field['Default'])
					: null;

		$sql = ' TYPE ' . $fType;

		if ($fNull == 'NO')
		{
			if (in_array($fType, $blobs) || $fDefault === null)
			{
				$sql .= ",\nALTER COLUMN " . $this->db->quoteName($fName) . ' SET NOT NULL' .
						",\nALTER COLUMN " . $this->db->quoteName($fName) . ' DROP DEFAULT';
			}
			else
			{
				$sql .= ",\nALTER COLUMN " . $this->db->quoteName($fName) . ' SET NOT NULL' .
						",\nALTER COLUMN " . $this->db->quoteName($fName) . ' SET DEFAULT ' . $fDefault;
			}
		}
		else
		{
			if ($fDefault !== null)
			{
				$sql .= ",\nALTER COLUMN " . $this->db->quoteName($fName) . ' DROP NOT NULL' .
						",\nALTER COLUMN " . $this->db->quoteName($fName) . ' SET DEFAULT ' . $fDefault;
			}
		}

		/* sequence was created in other function, here is associated a default value but not yet owner */
		if (strpos($fDefault, 'nextval') !== false)
		{
			$sql .= ";\nALTER SEQUENCE " . $this->db->quoteName($table . '_' . $fName . '_seq') . ' OWNED BY ' . $this->db->quoteName($table . '.' . $fName);
		}

		return $sql;
	}

	/**
	 * Get the SQL syntax for a single column that would be included in a table create statement.
	 *
	 * @param   SimpleXMLElement  $field  The XML field definition.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	protected function getColumnSQL(SimpleXMLElement $field)
	{
		// TODO Incorporate into parent class and use $this.
		$blobs = array('text', 'smalltext', 'mediumtext', 'largetext');

		$fName = (string) $field['Field'];
		$fType = (string) $field['Type'];
		$fNull = (string) $field['Null'];
		$fDefault = (isset($field['Default']) && $field['Default'] != 'NULL' ) ?
						preg_match('/^[0-9]$/', $field['Default']) ? $field['Default'] : $this->db->quote((string) $field['Default'])
					: null;

		/* nextval() as default value means that type field is serial */
		if (strpos($fDefault, 'nextval') !== false)
		{
			$sql = $this->db->quoteName($fName) . ' SERIAL';
		}
		else
		{
			$sql = $this->db->quoteName($fName) . ' ' . $fType;

			if ($fNull == 'NO')
			{
				if (in_array($fType, $blobs) || $fDefault === null)
				{
					$sql .= ' NOT NULL';
				}
				else
				{
					$sql .= ' NOT NULL DEFAULT ' . $fDefault;
				}
			}
			else
			{
				if ($fDefault !== null)
				{
					$sql .= ' DEFAULT ' . $fDefault;
				}
			}
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
	 * @since   12.1
	 */
	protected function getDropColumnSQL($table, $name)
	{
		$sql = 'ALTER TABLE ' . $this->db->quoteName($table) . ' DROP COLUMN ' . $this->db->quoteName($name);

		return $sql;
	}

	/**
	 * Get the SQL syntax to drop an index.
	 *
	 * @param   string  $name  The name of the key to drop.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	protected function getDropIndexSQL($name)
	{
		$sql = 'DROP INDEX ' . $this->db->quoteName($name);

		return $sql;
	}

	/**
	 * Get the SQL syntax to drop a key.
	 *
	 * @param   string  $table  The table name.
	 * @param   string  $name   The constraint name.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	protected function getDropPrimaryKeySQL($table, $name)
	{
		$sql = 'ALTER TABLE ONLY ' . $this->db->quoteName($table) . ' DROP CONSTRAINT ' . $this->db->quoteName($name);

		return $sql;
	}

	/**
	 * Get the details list of keys for a table.
	 *
	 * @param   array  $keys  An array of objects that comprise the keys for the table.
	 *
	 * @return  array  The lookup array. array({key name} => array(object, ...))
	 *
	 * @since   12.1
	 * @throws  Exception
	 */
	protected function getIdxLookup($keys)
	{
		// First pass, create a lookup of the keys.
		$lookup = array();

		foreach ($keys as $key)
		{
			if ($key instanceof SimpleXMLElement)
			{
				$kName = (string) $key['Index'];
			}
			else
			{
				$kName = $key->Index;
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
	 * Get the details list of sequences for a table.
	 *
	 * @param   array  $sequences  An array of objects that comprise the sequences for the table.
	 *
	 * @return  array  The lookup array. array({key name} => array(object, ...))
	 *
	 * @since   12.1
	 * @throws  Exception
	 */
	protected function getSeqLookup($sequences)
	{
		// First pass, create a lookup of the keys.
		$lookup = array();

		foreach ($sequences as $seq)
		{
			if ($seq instanceof SimpleXMLElement)
			{
				$sName = (string) $seq['Name'];
			}
			else
			{
				$sName = $seq->Name;
			}
			if (empty($lookup[$sName]))
			{
				$lookup[$sName] = array();
			}
			$lookup[$sName][] = $seq;
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
	 * @since   12.1
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
	 * @since   12.1
	 * @throws  Exception on error.
	 * @todo    If it's not XML convert to XML first.
	 */
	protected function mergeStructure()
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
				if ($queries = $this->getAlterTableSQL($xml->database->table_structure))
				{
					// Run the queries to upgrade the data structure.
					foreach ($queries as $query)
					{
						$this->db->setQuery((string) $query);

						try
						{
							$this->db->execute();
						}
						catch (RuntimeException $e)
						{
							$this->addLog('Fail: ' . $this->db->getQuery());
							throw $e;
						}
						$this->addLog('Pass: ' . $this->db->getQuery());
					}

				}
			}
			else
			{
				// This is a new table.
				$sql = $this->xmlToCreate($table);

				$this->db->setQuery((string) $sql);

				try
				{
					$this->db->execute();
				}
				catch (RuntimeException $e)
				{
					$this->addLog('Fail: ' . $this->db->getQuery());
					throw $e;
				}
				$this->addLog('Pass: ' . $this->db->getQuery());
			}
		}
	}

	/**
	 * Sets the database connector to use for exporting structure and/or data from PostgreSQL.
	 *
	 * @param   JDatabaseDriverPostgresql  $db  The database connector.
	 *
	 * @return  JDatabaseImporterPostgresql  Method supports chaining.
	 *
	 * @since   12.1
	 */
	public function setDbo(JDatabaseDriverPostgresql $db)
	{
		$this->db = $db;

		return $this;
	}

	/**
	 * Sets an internal option to merge the structure based on the input data.
	 *
	 * @param   boolean  $setting  True to export the structure, false to not.
	 *
	 * @return  JDatabaseImporterPostgresql  Method supports chaining.
	 *
	 * @since   12.1
	 */
	public function withStructure($setting = true)
	{
		$this->options->withStructure = (boolean) $setting;

		return $this;
	}
}
