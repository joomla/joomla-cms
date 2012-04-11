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
 * PostgreSQL export driver.
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @since       12.1
 */
class JDatabaseExporterPostgresql extends JDatabaseExporter
{
	/**
	 * An array of cached data.
	 *
	 * @var    array
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
	 * An array input sources (table names).
	 *
	 * @var    array
	 * @since  12.1
	 */
	protected $from = array();

	/**
	 * The type of output format (xml).
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

		// Export with only structure
		$this->withStructure();

		// Export as xml.
		$this->asXml();

		// Default destination is a string using $output = (string) $exporter;
	}

	/**
	 * Magic function to exports the data to a string.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 * @throws  Exception if an error is encountered.
	 */
	public function __toString()
	{
		// Check everything is ok to run first.
		$this->check();

		$buffer = '';

		// Get the format.
		switch ($this->asFormat)
		{
			case 'xml':
			default:
				$buffer = $this->buildXml();
				break;
		}

		return $buffer;
	}

	/**
	 * Set the output option for the exporter to XML format.
	 *
	 * @return  JDatabaseExporterPostgresql  Method supports chaining.
	 *
	 * @since   12.1
	 */
	public function asXml()
	{
		$this->asFormat = 'xml';

		return $this;
	}

	/**
	 * Builds the XML data for the tables to export.
	 *
	 * @return  string  An XML string
	 *
	 * @since   12.1
	 * @throws  Exception if an error occurs.
	 */
	protected function buildXml()
	{
		$buffer = array();

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
	 * @since   12.1
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
			$sequences = $this->db->getTableSequences($table);

			$buffer[] = '  <table_structure name="' . $table . '">';

			foreach ($sequences as $sequence)
			{
				if (version_compare($this->db->getVersion(), '9.1.0') < 0)
				{
					$sequence->start_value = null;
				}

				$buffer[] = '   <sequence Name="' . $sequence->sequence . '"' . ' Schema="' . $sequence->schema . '"' .
					' Table="' . $sequence->table . '"' . ' Column="' . $sequence->column . '"' . ' Type="' . $sequence->data_type . '"' .
					' Start_Value="' . $sequence->start_value . '"' . ' Min_Value="' . $sequence->minimum_value . '"' .
					' Max_Value="' . $sequence->maximum_value . '"' . ' Increment="' . $sequence->increment . '"' .
					' Cycle_option="' . $sequence->cycle_option . '"' .
					' />';
			}

			foreach ($fields as $field)
			{
				$buffer[] = '   <field Field="' . $field->column_name . '"' . ' Type="' . $field->type . '"' . ' Null="' . $field->null . '"' .
							(isset($field->default) ? ' Default="' . $field->default . '"' : '') . ' Comments="' . $field->comments . '"' .
					' />';
			}

			foreach ($keys as $key)
			{
				$buffer[] = '   <key Index="' . $key->idxName . '"' . ' is_primary="' . $key->isPrimary . '"' . ' is_unique="' . $key->isUnique . '"' .
					' Query="' . $key->Query . '" />';
			}

			$buffer[] = '  </table_structure>';
		}

		return $buffer;
	}

	/**
	 * Checks if all data and options are in order prior to exporting.
	 *
	 * @return  JDatabaseExporterPostgresql  Method supports chaining.
	 *
	 * @since   12.1
	 *
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
	 * Get the generic name of the table, converting the database prefix to the wildcard string.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  string  The name of the table with the database prefix replaced with #__.
	 *
	 * @since   12.1
	 */
	protected function getGenericTableName($table)
	{
		// TODO Incorporate into parent class and use $this.
		$prefix = $this->db->getPrefix();

		// Replace the magic prefix if found.
		$table = preg_replace("|^$prefix|", '#__', $table);

		return $table;
	}

	/**
	 * Specifies a list of table names to export.
	 *
	 * @param   mixed  $from  The name of a single table, or an array of the table names to export.
	 *
	 * @return  JDatabaseExporterPostgresql  Method supports chaining.
	 *
	 * @since   12.1
	 * @throws  Exception if input is not a string or array.
	 */
	public function from($from)
	{
		if (is_string($from))
		{
			$this->from = array($from);
		}
		elseif (is_array($from))
		{
			$this->from = $from;
		}
		else
		{
			throw new Exception('JPLATFORM_ERROR_INPUT_REQUIRES_STRING_OR_ARRAY');
		}

		return $this;
	}

	/**
	 * Sets the database connector to use for exporting structure and/or data from PostgreSQL.
	 *
	 * @param   JDatabaseDriverPostgresql  $db  The database connector.
	 *
	 * @return  JDatabaseExporterPostgresql  Method supports chaining.
	 *
	 * @since   12.1
	 */
	public function setDbo(JDatabaseDriverPostgresql $db)
	{
		$this->db = $db;

		return $this;
	}

	/**
	 * Sets an internal option to export the structure of the input table(s).
	 *
	 * @param   boolean  $setting  True to export the structure, false to not.
	 *
	 * @return  JDatabaseExporterPostgresql  Method supports chaining.
	 *
	 * @since   12.1
	 */
	public function withStructure($setting = true)
	{
		$this->options->withStructure = (boolean) $setting;

		return $this;
	}
}
