<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
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
class JDatabaseExporterMySQLi
{
	/**
	 * @var    array  An array of cached data.
	 * @since  11.1
	 */
	protected $cache = array();

	/**
	 * @var    JDatabaseDriverMySQLi  The database connector to use for exporting structure and/or data.
	 * @since  11.1
	 */
	protected $db = null;

	/**
	 * @var    array  An array input sources (table names).
	 * @since  11.1
	 */
	protected $from = array();

	/**
	 * @var    string  The type of output format (xml).
	 * @since  11.1
	 */
	protected $asFormat = 'xml';

	/**
	 * @var    JObject  An array of options for the exporter.
	 * @since  11.1
	 */
	protected $options = null;

	/**
	 * Constructor.
	 *
	 * Sets up the default options for the exporter.
	 *
	 * @return  JDatabaseExporterMySQLi
	 * @since   11.1
	 */
	public function __construct()
	{
		$this->options = new JObject;

		$this->cache = array(
			'columns'	=> array(),
			'keys'		=> array(),
		);

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
	 * @since   11.1
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
	 * @return  JDatabaseExporterMySQLi  Method supports chaining.
	 * @since   11.1
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
	 * @throws  Exception if an error occurs.
	 * @since   11.1
	 */
	protected function buildXml()
	{
		$buffer = array();

		$buffer[] = '<?xml version="1.0"?>';
		$buffer[] = '<mysqldump xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
		$buffer[] = ' <database name="">';

		$buffer	= array_merge($buffer, $this->buildXmlStructure());

		$buffer[] = ' </database>';
		$buffer[] = '</mysqldump>';

		return implode("\n", $buffer);
	}

	/**
	 * Builds the XML structure to export.
	 *
	 * @return  array  An array of XML lines (strings).
	 * @throws  Exception if an error occurs.
	 * @since   11.1
	 */
	protected function buildXmlStructure()
	{
		$buffer = array();

		foreach ($this->from as $table)
		{
			// Replace the magic prefix if found.
			$table = $this->getGenericTableName($table);

			// Get the details columns information.
			$fields	= $this->getColumns($table);
			$keys	= $this->getKeys($table);

			$buffer[] = '  <table_structure name="'.$table .'">';

			foreach ($fields as $field) {
				$buffer[] = '   <field Field="'.$field->Field.'"'.
					' Type="'.$field->Type.'"'.
					' Null="'.$field->Null.'"'.
					' Key="'.$field->Key.'"'.
					(isset($field->Default) ? ' Default="'.$field->Default.'"' : '').
					' Extra="'.$field->Extra.'"'.
					' />';
			}

			foreach ($keys as $key) {
				$buffer[] = '   <key Table="'.$table.'"'.
					' Non_unique="'.$key->Non_unique.'"'.
					' Key_name="'.$key->Key_name.'"'.
					' Seq_in_index="'.$key->Seq_in_index.'"'.
					' Column_name="'.$key->Column_name.'"'.
					' Collation="'.$key->Collation.'"'.
					' Null="'.$key->Null.'"'.
					' Index_type="'.$key->Index_type.'"'.
					' Comment="'.htmlspecialchars($key->Comment).'"'.
					' />';

			}

			$buffer[] = '  </table_structure>';
		}

		return $buffer;
	}

	/**
	 * Checks if all data and options are in order prior to exporting.
	 *
	 * @return  JDatabaseExporterMySQLi  Method supports chaining.
	 * @since   11.1
	 * @throws  Exception if an error is encountered.
	 */
	public function check()
	{
		// Check if the db connector has been set.
		if (!($this->db instanceof JDatabaseDriverMySql)) {
			throw new Exception('JPLATFORM_ERROR_DATABASE_CONNECTOR_WRONG_TYPE');
		}

		// Check if the tables have been specified.
		if (empty($this->from)) {
			throw new Exception('JPLATFORM_ERROR_NO_TABLES_SPECIFIED');
		}

		return $this;
	}

	/**
	 * Get the details list of columns for a table.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  array   An arry of the column specification for the table.
	 * @since   11.1
	 * @throws  Exception
	 * @todo    Move into database connector class.
	 */
	protected function getColumns($table)
	{
		if (empty($this->cache['columns'][$table])) {
			// Get the details columns information.
			$this->db->setQuery(
				'SHOW FULL COLUMNS FROM '.$this->db->nameQuote($table)
			);
			$this->cache['columns'][$table] = $this->db->loadObjectList('Field');

			// Check for a db error.
			if ($this->db->getErrorNum()) {
				throw new Exception($this->db->getErrorMsg());
			}
		}

		return $this->cache['columns'][$table];
	}

	/**
	 * Get the generic name of the table, converting the database prefix to the wildcard string.
	 *
	 * @param   string  $table	The name of the table.
	 *
	 * @return  string  The name of the table with the database prefix replaced with #__.
	 * @since   11.1
	 */
	protected function getGenericTableName($table)
	{
		// TODO Incorporate into parent class and use $this.
		$prefix	= $this->db->getPrefix();

		// Replace the magic prefix if found.
		$table = preg_replace("|^$prefix|", '#__', $table);

		return $table;
	}

	/**
	 * Get the details list of keys for a table.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  array  An arry of the column specification for the table.
	 * @since   11.1
	 * @throws  Exception
	 * @todo    Move into database connector class.
	 */
	protected function getKeys($table)
	{
		if (empty($this->cache['keys'][$table])) {
			// Get the details columns information.
			$this->db->setQuery(
				'SHOW KEYS FROM '.$this->db->nameQuote($table)
			);
			$this->cache['keys'][$table] = $this->db->loadObjectList();

			// Check for a db error.
			if ($this->db->getErrorNum()) {
				throw new Exception($db->getErrorMsg());
			}
		}

		return $this->cache['keys'][$table];
	}

	/**
	 * Specifies a list of table names to export.
	 *
	 * @param   mixed  $from  The name of a single table, or an array of the table names to export.
	 *
	 * @return  JDatabaseExporterMySQLi  Method supports chaining.
	 * @since   11.1
	 * @throws  Exception if input is not a string or array.
	 */
	public function from($from)
	{
		if (is_string($from)) {
			$this->from = array($from);
		}
		else if (is_array($from)) {
			$this->from = $from;
		}
		else {
			throw new Exception('JPLATFORM_ERROR_INPUT_REQUIRES_STRING_OR_ARRAY');
		}

		return $this;
	}

	/**
	 * Sets the database connector to use for exporting structure and/or data from MySQL.
	 *
	 * @param   JDatabaseDriverMySQLi  $db  The database connector.
	 *
	 * @return  JDatabaseExporterMySQLi  Method supports chaining.
	 * @since   11.1
	 */
	public function setDbo(JDatabaseDriverMySQLi $db)
	{
		$this->db = $db;

		return $this;
	}

	/**
	 * Sets an internal option to export the structure of the input table(s).
	 *
	 * @param   boolean  $setting  True to export the structure, false to not.
	 *
	 * @return  JDatabaseExporterMySQLi  Method supports chaining.
	 * @since   11.1
	 */
	public function withStructure($setting = true)
	{
		$this->options->set('with-structure', (boolean) $setting);

		return $this;
	}
}