<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database;

/**
 * Joomla Framework Database Exporter Class
 *
 * @since  1.0
 */
abstract class DatabaseExporter
{
	/**
	 * The type of output format.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $asFormat = 'xml';

	/**
	 * An array of cached data.
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $cache = ['columns' => [], 'keys' => []];

	/**
	 * The database connector to use for exporting structure and/or data.
	 *
	 * @var    DatabaseDriver
	 * @since  1.0
	 */
	protected $db;

	/**
	 * An array input sources (table names).
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $from = [];

	/**
	 * An array of options for the exporter.
	 *
	 * @var    \stdClass
	 * @since  1.0
	 */
	protected $options;

	/**
	 * Constructor.
	 *
	 * Sets up the default options for the exporter.
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		$this->options = new \stdClass;

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
	 * @since   1.0
	 */
	public function __toString()
	{
		$buffer = '';

		try
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
		}
		catch (\Exception $e)
		{
			// Do nothing
		}

		return $buffer;
	}

	/**
	 * Set the output option for the exporter to XML format.
	 *
	 * @return  $this
	 *
	 * @since   1.0
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
	 * @since   1.0
	 * @throws  \Exception if an error occurs.
	 */
	abstract protected function buildXml();

	/**
	 * Builds the XML structure to export.
	 *
	 * @return  array  An array of XML lines (strings).
	 *
	 * @since   1.0
	 * @throws  \Exception if an error occurs.
	 */
	abstract protected function buildXmlStructure();

	/**
	 * Checks if all data and options are in order prior to exporting.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  \Exception if an error is encountered.
	 */
	abstract public function check();

	/**
	 * Specifies a list of table names to export.
	 *
	 * @param   mixed  $from  The name of a single table, or an array of the table names to export.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function from($from)
	{
		if (is_string($from))
		{
			$this->from = [$from];
		}
		elseif (is_array($from))
		{
			$this->from = $from;
		}
		else
		{
			throw new \InvalidArgumentException('The exporter requires either a single table name or array of table names');
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
	 * @since   1.0
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
	 * @param   DatabaseDriver  $db  The database connector.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setDbo(DatabaseDriver $db)
	{
		$this->db = $db;

		return $this;
	}

	/**
	 * Sets an internal option to export the structure of the input table(s).
	 *
	 * @param   boolean  $setting  True to export the structure, false to not.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function withStructure($setting = true)
	{
		$this->options->withStructure = (boolean) $setting;

		return $this;
	}
}
