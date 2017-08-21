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
 * Joomla Platform Database Exporter Class
 *
 * @since  12.1
 */
abstract class JDatabaseExporter
{
	/**
	 * The type of output format (xml).
	 *
	 * @var    string
	 * @since  13.1
	 */
	protected $asFormat = 'xml';

	/**
	 * An array of cached data.
	 *
	 * @var    array
	 * @since  13.1
	 */
	protected $cache = array();

	/**
	 * The database connector to use for exporting structure and/or data.
	 *
	 * @var    JDatabaseDriver
	 * @since  13.1
	 */
	protected $db = null;

	/**
	 * An array input sources (table names).
	 *
	 * @var    array
	 * @since  13.1
	 */
	protected $from = array();

	/**
	 * An array of options for the exporter.
	 *
	 * @var    object
	 * @since  13.1
	 */
	protected $options = null;

	/**
	 * Constructor.
	 *
	 * Sets up the default options for the exporter.
	 *
	 * @since   13.1
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
	 * @since   13.1
	 * @throws  Exception if an error is encountered.
	 */
	public function __toString()
	{
		// Check everything is ok to run first.
		$this->check();

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
	 * @return  JDatabaseExporter  Method supports chaining.
	 *
	 * @since   13.1
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
	 * @since   13.1
	 * @throws  Exception if an error occurs.
	 */
	abstract protected function buildXml();

	/**
	 * Builds the XML structure to export.
	 *
	 * @return  array  An array of XML lines (strings).
	 *
	 * @since   13.1
	 * @throws  Exception if an error occurs.
	 */
	abstract protected function buildXmlStructure();

	/**
	 * Checks if all data and options are in order prior to exporting.
	 *
	 * @return  JDatabaseDriver  Method supports chaining.
	 *
	 * @since   13.1
	 * @throws  Exception if an error is encountered.
	 */
	abstract public function check();

	/**
	 * Specifies a list of table names to export.
	 *
	 * @param   mixed  $from  The name of a single table, or an array of the table names to export.
	 *
	 * @return  JDatabaseExporter  Method supports chaining.
	 *
	 * @since   13.1
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
	 * Get the generic name of the table, converting the database prefix to the wildcard string.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  string  The name of the table with the database prefix replaced with #__.
	 *
	 * @since   13.1
	 */
	protected function getGenericTableName($table)
	{
		$prefix = $this->db->getPrefix();

		// Replace the magic prefix if found.
		$table = preg_replace("|^$prefix|", '#__', $table);

		return $table;
	}

	/**
	 * Sets the database connector to use for exporting structure and/or data from MySQL.
	 *
	 * @param   JDatabaseDriver  $db  The database connector.
	 *
	 * @return  JDatabaseExporter  Method supports chaining.
	 *
	 * @since   13.1
	 */
	public function setDbo(JDatabaseDriver $db)
	{
		$this->db = $db;

		return $this;
	}

	/**
	 * Sets an internal option to export the structure of the input table(s).
	 *
	 * @param   boolean  $setting  True to export the structure, false to not.
	 *
	 * @return  JDatabaseExporter  Method supports chaining.
	 *
	 * @since   13.1
	 */
	public function withStructure($setting = true)
	{
		$this->options->withStructure = (boolean) $setting;

		return $this;
	}
}
