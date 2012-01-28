<?php
/**
 * @package     Joomla.CMS
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * PostgreSQL import driver.
 *
 * @package     Joomla.CMS
 * @subpackage  Database
 * @since       11.1
 */
class JDatabaseImporterPostgresql extends JDatabaseImporter
{
	protected $quoteString = '"';

	/**
	 * (non-PHPdoc)
	 * @see Xml2SqlFormatter::formatCreate()
	 */
	public function formatCreate(SimpleXMLElement $create)
	{
		$tableName = (string) $create->attributes()->name;

		$tableName = str_replace($this->options->get('prefix'), '#__', $tableName);

		$s = array();

		$s[] = '';
		$s[] = '';
		$s[] = '-- Table structure for table ' . $tableName;
		$s[] = '';

		$s[] = 'CREATE TABLE ' . $this->quote($tableName) . ' (';

		$fields = array();
		$comments = array();

		foreach ($create->field as $field)
		{
			$attribs = $field->attributes();

			$as = array();

			$as[] = $this->quote($attribs->Field);

			if ('auto_increment' == (string) $attribs->Extra)
			{
				$as[] = 'serial';

				if ('NO' == (string) $attribs->Null)
					$as[] = 'NOT NULL';
			}
			else
			{
				$type = (string) $attribs->Type;

				preg_match('/([a-zA-Z]*)\(([0-9]*)\)/', $type, $matches);

				if ($matches)
				{
					if (0 === strpos(strtolower($matches[1]), 'tinyint'))
					{
						$type = 'smallint';
					}
					if (0 === strpos(strtolower($matches[1]), 'int'))
					{
						$type = ($matches[2] > 10) ? 'bigint' : 'integer';
					}
					elseif (0 === strpos(strtolower($matches[1]), 'varchar'))
					{
						$type = 'character varying(' . $matches[2] . ')';
					}
					elseif (0 === strpos(strtolower($matches[1]), 'char'))
					{
						$type = 'character(' . $matches[2] . ')';
					}
				}

				$as[] = $type;

				if ('PRI' == (string) $attribs->Key)
					$as[] = 'PRIMARY KEY';

				if ('NO' == (string) $attribs->Null)
					$as[] = 'NOT NULL';


				$default = (string) $attribs->Default;

				if ('' != $default)
				{
					$default = str_replace('0000-00-00 00:00:00', '1970-01-01 00:00:00', $default);
					$as[] = "DEFAULT '$default'";
				}
			}

			$f = '';

			if ((string) $attribs->Comment)
				$f .= '-- ' . $attribs->Comment . "\n";

			$f .= implode(' ', $as);

			$fields[] = $f;
		}

		$primaries = array();
		$uniques = array();
		// $indices = array();
		$keys = array();

		foreach ($create->key as $key)
		{
			$n = (string) $key->attributes()->Key_name;
			$c = (string) $key->attributes()->Column_name;

			if ('PRIMARY' == $n)
				$primaries[] = $c;
			elseif ('0' == (string) $key->attributes()->Non_unique)
				$uniques[$n][] = $c;
			// elseif('1' == (string)$key->attributes()->Seq_in_index)
			// $indices[$n][] = $c;
			else
				$keys[$n][] = $c;
		}

		$s[] = implode(",\n", $fields);

		if ($primaries)
			$s[] = 'PRIMARY KEY (' . $this->quote(implode($this->quoteString . ',' . $this->quoteString, $primaries)) . '),';


		// foreach ($indices as $kName => $columns)
		// {
		// $s[] = 'INDEX '.$this->quote($kName).' (`'.implode('`,`', $columns).'`),';
		// }//foreach

		foreach ($uniques as $kName => $columns)
		{
			$s[] = 'UNIQUE KEY ' . $this->quote($kName) . ' (' . $this->quote(implode($this->quoteString . ',' . $this->quoteString, $columns)) . '),';
		}

		foreach ($keys as $kName => $columns)
		{
			$s[] = 'KEY ' . $this->quote($kName) . ' (' . $this->quote(implode($this->quoteString . ',' . $this->quoteString, $columns)) . '),';
		}


		/*
  $collation = (string)$create->options->attributes()->Collation;

  $collation =($collation) ? ' DEFAULT CHARSET='.$collation : '';

  $s[] = ')'.$collation.';';
  */

		$s[] = ');';

		$s[] = '';

		return implode("\n", $s);
	}

	/**
	 * (non-PHPdoc)
	 * @see Xml2SqlFormatter::formatInsert()
	 */
	public function formatInsert(SimpleXMLElement $insert)
	{
		if (!isset($insert->row->field))
			return '';

		$tableName = (string) $insert->attributes()->name;

		$tableName = str_replace($this->options->get('prefix'), '#__', $tableName);

		$s = array();

		$s[] = '';
		$s[] = '-- Table data for table ' . $tableName;
		$s[] = '';

		$keys = array();

		foreach ($insert->row->field as $field)
		{
			$keys[] = $this->quote($field->attributes()->name);
		}

		$s[] = 'INSERT INTO ' . $this->quote($tableName) . ' (' . implode(', ', $keys) . ')';

		$fields = array();

		$values = array();

		foreach ($insert->row as $row)
		{
			$vs = array();

			foreach ($row->field as $field)
			{
				$f = (string) $field;

				if ($f != (string) (int) $field)
					$f = $this->quote($f);

				$vs[] = $f;
			}

			$values[] = '(' . implode(', ', $vs) . ')';
		}

		$s[] = 'VALUES';

		$s[] = implode(",\n", $values);

		$s[] = ';';

		return implode("\n", $s);
	}

	/**
	 * (non-PHPdoc)
	 * @see Xml2SqlFormatter::formatTruncate()
	 */
	public function formatTruncate(SimpleXMLElement $tableStructure)
	{
		$tableName = str_replace($this->options->get('prefix'), '#__', (string) $tableStructure->attributes()->name);

		return 'TRUNCATE TABLE ' . $tableName . ";\n";
	}

}
