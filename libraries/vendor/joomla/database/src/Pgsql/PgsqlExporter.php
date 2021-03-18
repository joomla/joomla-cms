<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Pgsql;

use Joomla\Database\DatabaseExporter;

/**
 * PDO PostgreSQL Database Exporter.
 *
 * @since  1.5.0
 */
class PgsqlExporter extends DatabaseExporter
{
	/**
	 * Builds the XML data for the tables to export.
	 *
	 * @return  string  An XML string
	 *
	 * @since   1.0
	 * @throws  \Exception if an error occurs.
	 */
	protected function buildXml()
	{
		$buffer = [];

		$buffer[] = '<?xml version="1.0"?>';
		$buffer[] = '<postgresqldump xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
		$buffer[] = ' <database name="">';

		if ($this->options->withStructure)
		{
			$buffer = array_merge($buffer, $this->buildXmlStructure());
		}

		if ($this->options->withData)
		{
			$buffer = array_merge($buffer, $this->buildXmlData());
		}

		$buffer[] = ' </database>';
		$buffer[] = '</postgresqldump>';

		return implode("\n", $buffer);
	}

	/**
	 * Builds the XML structure to export.
	 *
	 * @return  array  An array of XML lines (strings).
	 *
	 * @since   1.0
	 * @throws  \Exception if an error occurs.
	 */
	protected function buildXmlStructure()
	{
		$buffer = [];

		foreach ($this->from as $table)
		{
			// Replace the magic prefix if found.
			$table = $this->getGenericTableName($table);

			// Get the details columns information.
			$fields    = $this->db->getTableColumns($table, false);
			$keys      = $this->db->getTableKeys($table);
			$sequences = $this->db->getTableSequences($table);

			$buffer[] = '  <table_structure name="' . $table . '">';

			foreach ($sequences as $sequence)
			{
				$buffer[] = '   <sequence Name="' . $this->getGenericTableName($sequence->sequence) . '" Schema="' . $sequence->schema . '"' .
					' Table="' . $table . '" Column="' . $sequence->column . '" Type="' . $sequence->data_type . '"' .
					' Start_Value="' . $sequence->start_value . '" Min_Value="' . $sequence->minimum_value . '"' .
					' Max_Value="' . $sequence->maximum_value . '" Last_Value="' . $this->db->getSequenceLastValue($sequence->sequence) . '"' .
					' Increment="' . $sequence->increment . '" Cycle_option="' . $sequence->cycle_option . '"' .
					' Is_called="' . $this->db->getSequenceIsCalled($sequence->sequence) . '"' .
					' />';
			}

			foreach ($fields as $field)
			{
				$buffer[] = '   <field Field="' . $field->column_name . '" Type="' . $field->type . '" Null="' . $field->null . '"' .
					' Default="' . $field->Default . '" Comments="' . $field->comments . '" />';
			}

			foreach ($keys as $key)
			{
				$buffer[] = '   <key Index="' . $this->getGenericTableName($key->idxName) . '" is_primary="' . $key->isPrimary . '"' .
					' is_unique="' . $key->isUnique . '" Key_name="' . $this->db->getNamesKey($table, $key->indKey) . '"' .
					' Query=\'' . $key->Query . '\' />';
			}

			$buffer[] = '  </table_structure>';
		}

		return $buffer;
	}

	/**
	 * Builds the XML data to export.
	 *
	 * @return  array  An array of XML lines (strings).
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception if an error occurs.
	 */
	protected function buildXmlData()
	{
		$buffer = [];

		foreach ($this->from as $table)
		{
			// Replace the magic prefix if found.
			$table = $this->getGenericTableName($table);

			// Get the details columns information.
			$fields  = $this->db->getTableColumns($table, false);
			$colblob = [];

			foreach ($fields as $field)
			{
				// Catch blob for xml conversion
				// PostgreSQL binary large object type
				if ($field->Type == 'bytea')
				{
					$colblob[] = $field->Field;
				}
			}

			$query = $this->db->getQuery(true);
			$query->select($query->quoteName(array_keys($fields)))
				->from($query->quoteName($table));
			$this->db->setQuery($query);

			$rows = $this->db->loadObjectList();

			if (!count($rows))
			{
				continue;
			}

			$buffer[] = '  <table_data name="' . $table . '">';

			foreach ($rows as $row)
			{
				$buffer[] = '   <row>';

				foreach ($row as $key => $value)
				{
					if (!in_array($key, $colblob))
					{
						$buffer[] = '    <field name="' . $key . '">' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '</field>';
					}
					else
					{
						$buffer[] = '    <field name="' . $key . '">' . stream_get_contents($value) . '</field>';
					}
				}

				$buffer[] = '   </row>';
			}

			$buffer[] = '  </table_data>';
		}

		return $buffer;
	}

	/**
	 * Checks if all data and options are in order prior to exporting.
	 *
	 * @return  $this
	 *
	 * @since   1.5.0
	 * @throws  \RuntimeException
	 */
	public function check()
	{
		// Check if the db connector has been set.
		if (!($this->db instanceof PgsqlDriver))
		{
			throw new \RuntimeException('Database connection wrong type.');
		}

		// Check if the tables have been specified.
		if (empty($this->from))
		{
			throw new \RuntimeException('ERROR: No Tables Specified');
		}

		return $this;
	}
}
