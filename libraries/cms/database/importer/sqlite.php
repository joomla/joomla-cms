<?php
/**
 * Created by JetBrains PhpStorm.
 * User: elkuku
 * Date: 27.01.12
 * Time: 20:54
 */
class JDatabaseImporterSqlite extends JDatabaseImporter
{
	protected $quoteString = '';

	/**
	 * (non-PHPdoc)
	 * @see Xml2SqlFormatter::formatCreate()
	 */
	public function formatCreate(SimpleXMLElement $create)
	{
		$tableName = (string) $create->attributes()->name;

		$tableName = str_replace($this->options->get('prefix'), '#__', $tableName);

		$fields = array();

		$primaryKeySet = false;

		$affinityTypes = array(
			'INTEGER' => array('int'),
			'TEXT' => array('char', 'text', 'clob'),
			'NONE' => array('blob'),
			'REAL' => array('real', 'floa', 'doub'),
		);

		/* @var JXMLElement $field */
		foreach ($create->field as $field)
		{
			$attribs = $field->attributes();

			$as = array();

			$as[] = (string) $attribs->Field;

			$type = (string) $attribs->Type;

			$type = str_replace(' unsigned', '', $type);

			$affinity = '';

			if (!$affinity)
			{
				foreach ($affinityTypes as $aType => $cTypes)
				{
					if ($affinity)
						continue;

					foreach ($cTypes as $cType)
					{
						if (false !== strpos($type, $cType))
						{
							$affinity = $aType;

							continue 2;
						}
					}
				}
			}

			if (!$affinity)
			{
				$affinity = 'NUMERIC';
			}

			$as[] = $affinity;

			if ('PRI' == (string) $attribs->Key
				&& !$primaryKeySet
			)
			{
				$as[] = 'PRIMARY KEY';
				$primaryKeySet = true;
			}

			if (0) //@todo - we ditch NOT NULL for now,as SQLite is very strict about it :(
			{
				if ('NO' == (string) $attribs->Null
					&& 'auto_increment' != (string) $attribs->Extra
				)
					$as[] = 'NOT NULL';
			}

			$default = (string) $attribs->Default;

			if ('' != $default)
				$as[] = "DEFAULT '$default'";

			if ('auto_increment' == (string) $attribs->Extra)
				$as[] = 'AUTOINCREMENT';

			$fields[] = implode(' ', $as);
		}

		$s = array();

		$s[] = '';
		$s[] = '-- Table structure for table ' . $tableName;
		$s[] = '';
		$s[] = 'CREATE TABLE IF NOT EXISTS ' . $tableName . ' (';
		$s[] = implode(",\n", $fields);
		$s[] = ');';

		return implode("\n", $s);
	}

	/**
	 * (non-PHPdoc)
	 * @see Xml2SqlFormatter::formatInsert()
	 *
	 * @param SimpleXMLElement $insert
	 *
	 * @return string
	 */
	public function formatInsert(SimpleXMLElement $insert)
	{
		if (!isset($insert->row->field))
			return '';

		$tableName = (string) $insert->attributes()->name;

		$tableName = str_replace($this->options->get('prefix'), '#__', $tableName);

		$keys = array();
		$values = array();

		foreach ($insert->row->field as $field)
		{
			$keys[] = (string) $field->attributes()->name;
		}

		$s = array();

		$s[] = '';
		$s[] = '-- Table data for table ' . $tableName;
		$s[] = '';
		$s[] = 'INSERT INTO ' . $tableName;

		$started = false;

		foreach ($insert->row as $row)
		{
			$vs = array();

			$i = 0;

			foreach ($row->field as $field)
			{
				// ''escape'' single quotes by prefixing them with another single quote
				$f = str_replace("'", "''", (string) $field);

				$vs[] = ($started) ? "'" . $f . "'" : "'" . $f . "' AS " . $keys[$i++];
			}

			if (!$started)
			{
				$s[] = ' SELECT ' . implode(', ', $vs);
			}
			else
			{
				$s[] = 'UNION SELECT ' . implode(', ', $vs);
			}

			$started = true;
		}

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

		return 'DELETE FROM ' . $tableName . ";\n";
	}
}
