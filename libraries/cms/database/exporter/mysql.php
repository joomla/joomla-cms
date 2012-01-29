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
 * MySQL export driver.
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @since       11.1
 */
class JDatabaseExporterMySQL extends JDatabaseExporter
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

		$buffer = array_merge($buffer, $this->buildXmlStructure());

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
		$query = $this->db->getQuery(true);

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
				$buffer[] = '   <field'
					. ' Field="' . $field->Field . '"'
					. ' Type="' . $field->Type . '"'
					. ' Null="' . $field->Null . '"'
					. ' Key="' . $field->Key . '"'
					. ' Default="' . $field->Default . '"'
					. ' Extra="' . $field->Extra . '"'
					. ' Comment="' . htmlspecialchars($field->Comment) . '"'
					. ' />';
			}

			foreach ($keys as $key)
			{
				$buffer[] = '   <key'
					. ' Table="' . $table . '"'
					. ' Non_unique="' . $key->Non_unique . '"'
					. ' Key_name="' . $key->Key_name . '"'
					. ' Seq_in_index="' . $key->Seq_in_index . '"'
					. ' Column_name="' . $key->Column_name . '"'
					. ' Collation="' . $key->Collation . '"'
					. ' Null="' . $key->Null . '"'
					. ' Index_type="' . $key->Index_type . '"'
					. ' Comment="' . htmlspecialchars($key->Comment) . '"'
//@todo fix unit tests to enable this feature..
//					. ' Index_comment="' . htmlspecialchars($key->Index_comment) . '"'
					. ' />';
			}

			$buffer[] = '  </table_structure>';

			/*
			 * Table data
			 */
			if (!$this->options->get('with-data'))
			{
				continue;
			}

			$query->clear()
				->from($this->db->quoteName($table))
				->select('*');

			$rows = $this->db->setQuery($query)->loadObjectList();

			$buffer[] = '  <table_data name="' . $table . '">';

			foreach ($rows as $row)
			{
				$buffer[] = '    <row>';

				foreach ($row as $fieldName => $fieldValue)
				{
					$buffer[] = '      <field'
						. ' name="' . $fieldName . '">'
						. htmlspecialchars($fieldValue)
						. '</field>';
				}

				$buffer[] = '    </row>';
			}

			$buffer[] = '  </table_data>';
		}

		return $buffer;
	}

	/**
	 * Checks if all data and options are in order prior to exporting.
	 *
	 * @return  JDatabaseExporterMySQL  Method supports chaining.
	 *
	 * @since   11.1
	 *
	 * @throws  Exception if an error is encountered.
	 */
	public function check()
	{
		// Check if the db connector has been set.
		if (!($this->db instanceof JDatabaseMysql))
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
