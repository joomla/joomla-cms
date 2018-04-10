<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Postgresql;

use Joomla\Database\DatabaseExporter;

/**
 * PostgreSQL Database Exporter.
 *
 * @since  1.0
 */
class PostgresqlExporter extends DatabaseExporter
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

		$buffer = array_merge($buffer, $this->buildXmlStructure());

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
				$buffer[] = '   <sequence Name="' . $sequence->sequence . '" Schema="' . $sequence->schema . '"' .
					' Table="' . $sequence->table . '" Column="' . $sequence->column . '" Type="' . $sequence->data_type . '"' .
					' Start_Value="' . $sequence->start_value . '" Min_Value="' . $sequence->minimum_value . '"' .
					' Max_Value="' . $sequence->maximum_value . '" Increment="' . $sequence->increment . '"' .
					' Cycle_option="' . $sequence->cycle_option . '"' .
					' />';
			}

			foreach ($fields as $field)
			{
				$buffer[] = '   <field Field="' . $field->column_name . '" Type="' . $field->type . '" Null="' . $field->null . '"' .
					(isset($field->default) ? ' Default="' . $field->default . '"' : '') . ' Comments="' . $field->comments . '" />';
			}

			foreach ($keys as $key)
			{
				$buffer[] = '   <key Index="' . $key->idxName . '" is_primary="' . $key->isPrimary . '" is_unique="' . $key->isUnique . '"'
					. ' Query="' . $key->Query . '" />';
			}

			$buffer[] = '  </table_structure>';
		}

		return $buffer;
	}

	/**
	 * Checks if all data and options are in order prior to exporting.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function check()
	{
		// Check if the db connector has been set.
		if (!($this->db instanceof PostgresqlDriver))
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
