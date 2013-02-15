<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once JPATH_PLATFORM . '/joomla/database/importer/postgresql.php';

/**
 * Class to expose protected properties and methods in JDatabasePostgresqlImporter for testing purposes
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @since       12.1
 */
class JDatabaseImporterPostgresqlInspector extends JDatabaseImporterPostgresql
{
	/**
	 * Gets any property from the class.
	 *
	 * @param   string  $property  The name of the class property.
	 *
	 * @return  mixed   The value of the class property.
	 *
	 * @since   12.1
	 */
	public function __get($property)
	{
		return $this->$property;
	}

	/**
	 * Exposes the protected check method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function check()
	{
		return parent::check();
	}

	/**
	 * Exposes the protected getAddColumnSQL method.
	 *
	 * @param   string            $table  The table name.
	 * @param   SimpleXMLElement  $field  The XML field definition.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	public function getAddColumnSQL($table, SimpleXMLElement $field)
	{
		return parent::getAddColumnSQL($table, $field);
	}

	/**
	 * Exposes the protected getAddKeySQL method.
	 *
	 * @param   SimpleXMLElement  $field  The XML index definition.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	public function getAddIndexSQL(SimpleXMLElement $field)
	{
		return parent::getAddIndexSQL($field);
	}

	/**
	 * Exposes the protected getAddSequenceSQL method.
	 *
	 * @param   SimpleXMLElement  $structure  The XML sequence definition.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	public function getAddSequenceSQL(SimpleXMLElement $structure)
	{
		return parent::getAddSequenceSQL($structure);
	}

	/**
	 * Exposes the protected getAlterTableSQL method.
	 *
	 * @param   SimpleXMLElement  $structure  The XML structure of the table.
	 *
	 * @return  array
	 *
	 * @since   12.1
	 */
	public function getAlterTableSQL(SimpleXMLElement $structure)
	{
		return parent::getAlterTableSQL($structure);
	}

	/**
	 * Exposes the protected getChangeColumnSQL method.
	 *
	 * @param   string            $table  The table name.
	 * @param   SimpleXMLElement  $field  The XML field definition.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	public function getChangeColumnSQL($table, SimpleXMLElement $field)
	{
		return parent::getChangeColumnSQL($table, $field);
	}

	/**
	 * Exposes the protected getColumnSQL method.
	 *
	 * @param   SimpleXMLElement  $field  The XML field definition.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	public function getColumnSQL(SimpleXMLElement $field)
	{
		return parent::getColumnSQL($field);
	}

	/**
	 * Exposes the protected getChangeSequenceSQL method.
	 *
	 * @param   SimpleXMLElement  $structure  The XML sequence definition.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	public function getChangeSequenceSQL(SimpleXMLElement $structure)
	{
		return parent::getChangeSequenceSQL($structure);
	}

	/**
	 * Exposes the protected getDropColumnSQL method.
	 *
	 * @param   string  $table  The table name.
	 * @param   string  $name   The name of the field to drop.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	public function getDropColumnSQL($table, $name)
	{
		return parent::getDropColumnSQL($table, $name);
	}

	/**
	 * Exposes the protected getDropKeySQL method.
	 *
	 * @param   string  $table  The table name.
	 * @param   string  $name   The name of the key to drop.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	public function getDropKeySQL($table, $name)
	{
		return parent::getDropKeySQL($table, $name);
	}

	/**
	 * Exposes the protected getDropPrimaryKeySQL method.
	 *
	 * @param   string  $table  The table name.
	 * @param   string  $name   The constraint name.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	public function getDropPrimaryKeySQL($table, $name)
	{
		return parent::getDropPrimaryKeySQL($table, $name);
	}

	/**
	 * Exposes the protected getDropIndexSQL method.
	 *
	 * @param   string  $name  The index name.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	public function getDropIndexSQL($name)
	{
		return parent::getDropIndexSQL($name);
	}

	/**
	 * Exposes the protected getDropSequenceSQL method.
	 *
	 * @param   string  $name  The index name.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	public function getDropSequenceSQL($name)
	{
		return parent::getDropSequenceSQL($name);
	}

	/**
	 * Exposes the protected getIdxLookup method.
	 *
	 * @param   array  $keys  An array of objects that comprise the indexes for the table.
	 *
	 * @return  array	The lookup array. array({key name} => array(object, ...))
	 *
	 * @since   12.1
	 * @throws	Exception
	 */
	public function getIdxLookup($keys)
	{
		return parent::getIdxLookup($keys);
	}

	/**
	 * Exposes the protected getSeqLookup method.
	 *
	 * @param   array  $sequences  An array of objects that comprise the sequences for the table.
	 *
	 * @return  array	The lookup array. array({key name} => array(object, ...))
	 *
	 * @since   12.1
	 * @throws	Exception
	 */
	public function getSeqLookup($sequences)
	{
		return parent::getSeqLookup($sequences);
	}

	/**
	 * Exposes the protected getRealTableName method.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  string	The real name of the table.
	 *
	 * @since   12.1
	 */
	public function getRealTableName($table)
	{
		return parent::getRealTableName($table);
	}

	/**
	 * Exposes the protected mergeStructure method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @throws  Exception on error.
	 */
	public function mergeStructure()
	{
		return parent::mergeStructure();
	}

	/**
	 * Exposes the protected withStructure method.
	 *
	 * @param   boolean  $setting  True to export the structure, false to not.
	 *
	 * @return  void
	 *
	 * @since	12.1
	 */
	public function withStructure($setting = true)
	{
		return parent::withStructure($setting);
	}

}
