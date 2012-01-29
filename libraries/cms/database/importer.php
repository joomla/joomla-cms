<?php
/**
 * @package     Joomla.CMS
 * @subpackage  Database.maintainer
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Base class
 */
abstract class JDatabaseImporter
{
	/**
	 * @var    array  An array of cached data.
	 * @since  11.1
	 */
	protected $cache = array();

	/**
	 * The database connector to use for exporting structure and/or data.
	 *
	 * @var    JDatabaseMySQL
	 * @since  11.1
	 */
	protected $db = null;

	/**
	 * The input source.
	 *
	 * @var    mixed
	 * @since  11.1
	 */
	protected $from = array();

	/**
	 * The type of input format (XML).
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
	 * @param JDatabase  $db
	 * @param JObject    $options
	 *
	 * @return JDatabaseExporter
	 *
	 * @throws Exception
	 */
	public static function getInstance(JDatabase $db, JObject $options)
	{
		$className = 'JDatabaseImporter' . ucfirst($db->name);

		return new $className($db, $options);
	}

	protected function __construct(JDatabase $db, JObject $options)
	{
		$this->db = $db;
		$this->options = $options;
		$this->verbose = $options->get('verbose') ? true : false;
		$this->from = $options->get('from');

		$this->cache = array('columns' => array(), 'keys' => array());
		$this->options->set('with-structure', true);

		$this->nlChar = ('cli' == PHP_SAPI) ? "\n" : '<br />';
	}

	abstract protected function formatCreate(SimpleXMLElement $xml);

	abstract protected function formatInsert(SimpleXMLElement $xml);

	abstract protected function formatTruncate(SimpleXMLElement $xml);

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
		$this->format = (string) $format;

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
	 * Specifies the data source to import.
	 *
	 * @param   mixed  $from  The data source to import.
	 *
	 * @return  JDatabaseImporterMySQL  Method supports chaining.
	 *
	 * @since   11.1
	 */
	public function from($from)
	{
		$this->from = $from;

		return $this;
	}

	/**
	 * Set the output option for the exporter to XML format.
	 *
	 * @return  JDatabaseImporterMySQL  Method supports chaining.
	 *
	 * @since   11.1
	 */
	public function asXml()
	{
		$this->format = 'xml';

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
//		$this->check();

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

	private function buildXml()
	{
		$xml = JFactory::getXML($this->from);

		$sql = '';

		foreach ($xml->database->table_structure as $create)
		{
			$sql .= $this->formatCreate($create);
		}//foreach

		foreach ($xml->database->table_data as $insert)
		{
			$sql .= $this->formatInsert($insert);
		}//foreach

		return $sql;
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
		$table = preg_replace("|^$prefix|", '#__', $table);

		return $table;
	}

	protected function quote($string)
	{
		return $this->db->quote((string)$string);
	}

	protected function nameQuote($string)
	{
		return $this->db->quoteName((string)$string);
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
