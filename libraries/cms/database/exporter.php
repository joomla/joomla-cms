<?php
/**
 * @package     Joomla.CMS
 * @subpackage  Database.Exporter
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Base class
 */
abstract class JDatabaseExporter
{
	/**
	 * @var JDatabase
	 */
	protected $db;

	/**
	 * New line character for the console or html output.
	 *
	 * @var string
	 */
	protected $nlChar;

	/**
	 * @var bool
	 */
	private $verbose = true;

	/**
	 * An array input sources (table names).
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $from = array();

	/**
	 * The type of output format (xml).
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $format = 'xml';

	/**
	 * An array of options for the exporter.
	 *
	 * @var    JObject
	 * @since  11.1
	 */
	protected $options = null;

	/**
	 * @static
	 *
	 * @param JDatabase $db
	 * @param JObject $options
	 *
	 * @return JDatabaseExporter
	 */
	public static function getInstance(JDatabase $db, JObject $options)
	{
		$className = 'JDatabaseExporter' . ucfirst($db->name);

		return new $className($db, $options);
	}

	/**
	 * Constructor.
	 *
	 * @param JDatabase $db
	 * @param JObject $options
	 */
	protected function __construct(JDatabase $db, JObject $options)
	{
		$this->db = $db;
		$this->options = $options;
		$this->verbose = $options->get('verbose') ? true : false;
		$this->from = $options->get('from');

		$this->nlChar = ('cli' == PHP_SAPI) ? "\n" : '<br />';
	}

	/**
	 * Set the output option for the exporter to XML format.
	 *
	 * @param string $format
	 *
	 * @return  JDatabaseExporter  Method supports chaining.
	 *
	 * @since   11.1
	 */
	public function setFormat($format)
	{
		$this->format = (string) $format;;

		return $this;
	}

	/**
	 * Sets an internal option to export the structure of the input table(s).
	 *
	 * @param   boolean  $setting  True to export the structure, false to not.
	 *
	 * @return  JDatabaseExporterMySQL  Method supports chaining.
	 *
	 * @since   11.1
	 */
	public function withStructure($setting = true)
	{
		$this->options->set('with-structure', (boolean) $setting);

		return $this;
	}

	/**
	 * Sets an internal option to export the data of the input table(s).
	 *
	 * @param   boolean  $setting  True to export the data, false to not.
	 *
	 * @return  JDatabaseExporterMySQL  Method supports chaining.
	 *
	 * @since   12.1
	 */
	public function withData($setting = true)
	{
		$this->options->set('with-data', (boolean) $setting);

		return $this;
	}

	/**
	 * Specifies a list of table names to export.
	 *
	 * @param   mixed  $from  The name of a single table, or an array of the table names to export.
	 *
	 * @return  JDatabaseExporterMySQL  Method supports chaining.
	 *
	 * @since   11.1
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
	 * Magic function to exports the data to a string.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 * @throws  Exception if an error is encountered.
	 */
	public function __toString()
	{
		// Check everything is ok to run first.
		$this->check();

		// Get the format.
		switch ($this->format)
		{
			case 'xml':
			default:
				$buffer = $this->buildXml();
				break;
		}

		return $buffer;
	}

	/**
	 * Get the generic name of the table, converting the database prefix to the wildcard string.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  string  The name of the table with the database prefix replaced with #__.
	 *
	 * @since   11.1
	 */
	protected function getGenericTableName($table)
	{
		$prefix = $this->db->getPrefix();

		// Replace the magic prefix if found.
		return preg_replace("|^$prefix|", '#__', $table);
	}

	protected function out($string, $nl = true)
	{
		if (!$this->verbose)
		{
			return;
		}

		echo $string . (true == $nl) ? $this->nlChar : '';
	}
}
