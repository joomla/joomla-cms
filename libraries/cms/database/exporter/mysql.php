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

		foreach ($this->from as $table)
		{
			// Replace the magic prefix if found.
			$table = $this->getGenericTableName($table);

			// Get the details columns information.
			$fields = $this->db->getTableColumns($table);
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
					' Null="' . $key->Null . '"' . ' Index_type="' . $key->Index_type . '"' . ' Comment="' . htmlspecialchars($key->Comment) . '"' .
					' />';
			}

			$buffer[] = '  </table_structure>';
		}

		return $buffer;
	}

}
