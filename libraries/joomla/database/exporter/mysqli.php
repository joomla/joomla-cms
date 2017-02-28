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
 * MySQLi export driver.
 *
 * @since  11.1
 */
class JDatabaseExporterMysqli extends JDatabaseExporter
{
	/**
	 * Builds the XML data for the tables to export.
	 *
	 * @return  string  An XML string
	 *
	 * @since   11.1
	 * @throws  Exception if an error occurs.
	 */
	protected function buildXml()
	{
		$buffer = array();

		$buffer[] = '<?xml version="1.0"?>';
		$buffer[] = '<mysqldump xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
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
		$buffer[] = '</mysqldump>';

		return implode("\n", $buffer);
	}

	/**
	 * Builds the XML structure to export.
	 *
	 * @return  array  An array of XML lines (strings).
	 *
	 * @since   11.1
	 * @throws  Exception if an error occurs.
	 */
	protected function buildXmlStructure()
	{
		$buffer = array();

		foreach ($this->from as $table)
		{
			// Replace the magic prefix if found.
			$table = $this->getGenericTableName($table);

			// Get the details columns information.
			$fields = $this->db->getTableColumns($table, false);
			$keys = $this->db->getTableKeys($table);

			$buffer[] = '  <table_structure name="' . $table . '">';

			foreach ($fields as $field)
			{
				$buffer[] = '   <field Field="' . $field->Field . '"' . ' Type="' . $field->Type . '"' . ' Null="' . $field->Null . '"' . ' Key="' .
					$field->Key . '"' . (isset($field->Default) ? ' Default="' . $field->Default . '"' : '') . ' Extra="' . $field->Extra . '"' .
					' />';
			}

			foreach ($keys as $key)
			{
				$buffer[] = '   <key Table="' . $table . '"' . ' Non_unique="' . $key->Non_unique . '"' . ' Key_name="' . $key->Key_name . '"' .
					' Seq_in_index="' . $key->Seq_in_index . '"' . ' Column_name="' . $key->Column_name . '"' . ' Collation="' . $key->Collation . '"' .
					' Null="' . $key->Null . '"' . ' Index_type="' . $key->Index_type . '"' .
					' Sub_part ="' . $key->Sub_part . '"' .
					' Comment="' . htmlspecialchars($key->Comment, ENT_COMPAT, 'UTF-8') . '"' . ' />';
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
	 * @since   3.6
	 * @throws  Exception if an error occurs.
	 */
	protected function buildXmlData()
	{
		$buffer = array();

		foreach ($this->from as $table)
		{
			// Replace the magic prefix if found.
			$table = $this->getGenericTableName($table);

			// Get the details columns information.
			$fields  = $this->db->getTableColumns($table, false);
			$colblob = array();

			foreach ($fields as $field)
			{
				// Cacth blob for conversion xml
				if ($field->Type == 'mediumblob')
				{
					$colblob[] = $field->Field;
				}
			}

			$query = $this->db->getQuery(true);
			$query->select($query->qn(array_keys($fields)))
				->from($query->qn($table));
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
						$buffer[] = '    <field name="' . $key . '">' . base64_encode($value) . '</field>';
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
	 * @return  JDatabaseExporterMysqli  Method supports chaining.
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
}
