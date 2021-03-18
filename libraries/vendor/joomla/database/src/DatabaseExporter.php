<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
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
	 * @var    DatabaseInterface
	 * @since  1.0
	 */
	protected $db;

	/**
	 * An array input sources (table names).
	 *
	 * @var    string[]
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

		// Export not only structure
		$this->withStructure();
		$this->withData();

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
	 * @param   string[]|string  $from  The name of a single table, or an array of the table names to export.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function from($from)
	{
		if (\is_string($from))
		{
			$this->from = [$from];
		}
		elseif (\is_array($from))
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
		return preg_replace("|^$prefix|", '#__', $table);
	}

	/**
	 * Sets the database connector to use for importing structure and/or data.
	 *
	 * @param   DatabaseInterface  $db  The database connector.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setDbo(DatabaseInterface $db)
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

	/**
	 * Sets an internal option to export the data of the input table(s).
	 *
	 * @param   boolean  $setting  True to export the data, false to not.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function withData($setting = false)
	{
		$this->options->withData = (boolean) $setting;

		return $this;
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
				// Cacth blob for conversion xml
				if ($field->Type == 'mediumblob')
				{
					$colblob[] = $field->Field;
				}
			}

			$this->db->setQuery(
				$this->db->getQuery(true)
					->select($this->db->quoteName(array_keys($fields)))
					->from($this->db->quoteName($table))
			);

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
}
