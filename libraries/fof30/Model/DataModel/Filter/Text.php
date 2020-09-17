<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model\DataModel\Filter;

defined('_JEXEC') || die;

use JDatabaseDriver;

class Text extends AbstractFilter
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db     The database object
	 * @param   object           $field  The field information as taken from the db
	 */
	public function __construct($db, $field)
	{
		parent::__construct($db, $field);

		$this->null_value = '';
	}

	/**
	 * Returns the default search method for this field.
	 *
	 * @return  string
	 */
	public function getDefaultSearchMethod()
	{
		return 'partial';
	}

	/**
	 * Perform a partial match (search in string)
	 *
	 * @param   mixed  $value  The value to compare to
	 *
	 * @return  string  The SQL where clause for this search
	 */
	public function partial($value)
	{
		if ($this->isEmpty($value))
		{
			return '';
		}

		return '(' . $this->getFieldName() . ' LIKE ' . $this->db->quote('%' . $value . '%') . ')';
	}

	/**
	 * Perform an exact match (match string)
	 *
	 * @param   mixed  $value  The value to compare to
	 *
	 * @return  string  The SQL where clause for this search
	 */
	public function exact($value)
	{
		if ($this->isEmpty($value))
		{
			return '';
		}

		if (is_array($value) || is_object($value))
		{
			$value = (array) $value;

			$db    = $this->db;
			$value = array_map([$db, 'quote'], $value);

			return '(' . $this->getFieldName() . ' IN (' . implode(',', $value) . '))';
		}

		return '(' . $this->getFieldName() . ' LIKE ' . $this->db->quote($value) . ')';
	}

	/**
	 * Dummy method; this search makes no sense for text fields
	 *
	 * @param   mixed    $from     Ignored
	 * @param   mixed    $to       Ignored
	 * @param   boolean  $include  Ignored
	 *
	 * @return  string  Empty string
	 */
	public function between($from, $to, $include = true)
	{
		return '';
	}

	/**
	 * Dummy method; this search makes no sense for text fields
	 *
	 * @param   mixed    $from     Ignored
	 * @param   mixed    $to       Ignored
	 * @param   boolean  $include  Ignored
	 *
	 * @return  string  Empty string
	 */
	public function outside($from, $to, $include = false)
	{
		return '';
	}

	/**
	 * Dummy method; this search makes no sense for text fields
	 *
	 * @param   mixed    $value     Ignored
	 * @param   mixed    $interval  Ignored
	 * @param   boolean  $include   Ignored
	 *
	 * @return  string  Empty string
	 */
	public function interval($value, $interval, $include = true)
	{
		return '';
	}

	/**
	 * Dummy method; this search makes no sense for text fields
	 *
	 * @param   mixed    $from     Ignored
	 * @param   mixed    $to       Ignored
	 * @param   boolean  $include  Ignored
	 *
	 * @return  string  Empty string
	 */
	public function range($from, $to, $include = false)
	{
		return '';
	}

	/**
	 * Dummy method; this search makes no sense for text fields
	 *
	 * @param   mixed    $from      Ignored
	 * @param   mixed    $interval  Ignored
	 * @param   boolean  $include   Ignored
	 *
	 * @return  string  Empty string
	 */
	public function modulo($from, $interval, $include = false)
	{
		return '';
	}
}
