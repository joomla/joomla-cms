<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * PDO PostgreSQL Database Exporter.
 *
 * @since  3.9.0
 */
class JDatabaseExporterPgsql extends JDatabaseExporterPostgresql
{
	/**
	 * Checks if all data and options are in order prior to exporting.
	 *
	 * @return  JDatabaseExporterPgsql  Method supports chaining.
	 *
	 * @since   3.9.0
	 * @throws  \Exception if an error is encountered.
	 */
	public function check()
	{
		// Check if the db connector has been set.
		if (!($this->db instanceof JDatabaseDriverPgsql))
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
	 * Builds the XML data to export.
	 *
	 * @return  array  An array of XML lines (strings).
	 *
	 * @since   __DEPLOY_VERSION__
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
			$collob = array();

			foreach ($fields as $field)
			{
				// Cacth blob for conversion xml
				if ($field->Type == 'mediumblob')
				{
					$colblob[] = $field->Field;
				}

				// Catch lob PDO stream for conversion xml
				if ($field->Type == 'bytea')
				{
					$collob[] = $field->Field;
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
					if (!in_array($key, $colblob) && !in_array($key, $collob))
					{
						$buffer[] = '    <field name="' . $key . '">' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '</field>';
					}
					elseif (in_array($key, $collob))
					{
						$buffer[] = '    <field name="' . $key . '">' . htmlspecialchars(stream_get_contents($value), ENT_COMPAT, 'UTF-8') . '</field>';
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
}
