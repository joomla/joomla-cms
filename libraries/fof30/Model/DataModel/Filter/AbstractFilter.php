<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model\DataModel\Filter;

defined('_JEXEC') || die;

use FOF30\Model\DataModel\Filter\Exception\InvalidFieldObject;
use FOF30\Model\DataModel\Filter\Exception\NoDatabaseObject;
use InvalidArgumentException;
use JDatabaseDriver;
use ReflectionClass;
use ReflectionMethod;

abstract class AbstractFilter
{
	/**
	 * The null value for this type
	 *
	 * @var  mixed
	 */
	public $null_value = null;
	protected $db = null;
	/**
	 * The column name of the table field
	 *
	 * @var string
	 */
	protected $name = '';
	/**
	 * The column type of the table field
	 *
	 * @var string
	 */
	protected $type = '';
	/**
	 * Should I allow filtering against the number 0?
	 *
	 * @var bool
	 */
	protected $filterZero = true;
	/**
	 * Prefix each table name with this table alias. For example, field bar normally creates a WHERE clause:
	 * `bar` = '1'
	 * If tableAlias is set to "foo" then the WHERE clause it generates becomes
	 * `foo`.`bar` = '1'
	 *
	 * @var  null
	 */
	protected $tableAlias = null;

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db     The database object
	 * @param   object            $field  The field information as taken from the db
	 */
	public function __construct($db, $field)
	{
		$this->db = $db;

		if (!is_object($field) || !isset($field->name) || !isset($field->type))
		{
			throw new InvalidFieldObject;
		}

		$this->name = $field->name;
		$this->type = $field->type;

		if (isset ($field->filterZero))
		{
			$this->filterZero = $field->filterZero;
		}

		if (isset ($field->tableAlias))
		{
			$this->tableAlias = $field->tableAlias;
		}
	}

	/**
	 * Creates a field Object based on the field column type
	 *
	 * @param   object  $field   The field information
	 * @param   array   $config  The field configuration (like the db object to use)
	 *
	 * @return  AbstractFilter  The Filter object
	 *
	 * @throws  InvalidArgumentException
	 */
	public static function getField($field, $config = [])
	{
		if (!is_object($field) || !isset($field->name) || !isset($field->type))
		{
			throw new InvalidFieldObject;
		}

		$type = $field->type;

		$classType = self::getFieldType($type);

		$className = '\\FOF30\\Model\\DataModel\\Filter\\' . ucfirst($classType);

		if (($classType !== false) && class_exists($className, true))
		{
			if (!isset($config['dbo']))
			{
				throw new NoDatabaseObject($className);
			}

			$db = $config['dbo'];

			$field = new $className($db, $field);

			return $field;
		}

		return null;
	}

	/**
	 * Get the class name based on the field Type
	 *
	 * @param   string  $type  The type of the field
	 *
	 * @return  string  the class name suffix
	 */
	public static function getFieldType($type)
	{
		// Remove parentheses, indicating field options / size (they don't matter in type detection)
		if (!empty($type))
		{
			[$type, ] = explode('(', $type);
		}

		$detectedType = null;

		switch (trim($type))
		{
			case 'varchar':
			case 'text':
			case 'smalltext':
			case 'longtext':
			case 'char':
			case 'mediumtext':
			case 'character varying':
			case 'nvarchar':
			case 'nchar':
				$detectedType = 'Text';
				break;

			case 'date':
			case 'datetime':
			case 'time':
			case 'year':
			case 'timestamp':
			case 'timestamp without time zone':
			case 'timestamp with time zone':
				$detectedType = 'Date';
				break;

			case 'tinyint':
			case 'smallint':
				$detectedType = 'Boolean';
				break;
		}

		// Sometimes we have character types followed by a space and some cruft. Let's handle them.
		if (is_null($detectedType) && !empty($type))
		{
			[$type, ] = explode(' ', $type);

			switch (trim($type))
			{
				case 'varchar':
				case 'text':
				case 'smalltext':
				case 'longtext':
				case 'char':
				case 'mediumtext':
				case 'nvarchar':
				case 'nchar':
					$detectedType = 'Text';
					break;

				case 'date':
				case 'datetime':
				case 'time':
				case 'year':
				case 'timestamp':
					$detectedType = 'Date';
					break;

				case 'tinyint':
				case 'smallint':
					$detectedType = 'Boolean';
					break;

				default:
					$detectedType = 'Number';
					break;
			}
		}

		// If all else fails assume it's a Number and hope for the best
		if (empty($detectedType))
		{
			$detectedType = 'Number';
		}

		return $detectedType;
	}

	/**
	 * Is it a null or otherwise empty value?
	 *
	 * @param   mixed  $value  The value to test for emptiness
	 *
	 * @return  boolean
	 */
	public function isEmpty($value)
	{
		return (($value === $this->null_value) || empty($value))
			&& !($this->filterZero && ($value === "0"));
	}

	/**
	 * Returns the default search method for a field. This always returns 'exact'
	 * and you are supposed to override it in specialised classes. The possible
	 * values are exact, partial, between and outside, unless something
	 * different is returned by getSearchMethods().
	 *
	 * @return  string
	 * @see  self::getSearchMethods()
	 *
	 */
	public function getDefaultSearchMethod()
	{
		return 'exact';
	}

	/**
	 * Return the search methods available for this field class,
	 *
	 * @return  array
	 */
	public function getSearchMethods()
	{
		$ignore = [
			'isEmpty', 'getField', 'getFieldType', '__construct', 'getDefaultSearchMethod', 'getSearchMethods',
			'getFieldName',
		];

		$class   = new ReflectionClass(__CLASS__);
		$methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

		$tmp = [];

		foreach ($methods as $method)
		{
			$tmp[] = $method->name;
		}

		$methods = $tmp;

		if ($methods = array_diff($methods, $ignore))
		{
			return $methods;
		}

		return [];
	}

	/**
	 * Perform an exact match (equality matching)
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

		if (is_array($value))
		{
			$db    = $this->db;
			$value = array_map([$db, 'quote'], $value);

			return '(' . $this->getFieldName() . ' IN (' . implode(',', $value) . '))';
		}
		else
		{
			return $this->search($value, '=');
		}
	}

	/**
	 * Perform a partial match (usually: search in string)
	 *
	 * @param   mixed  $value  The value to compare to
	 *
	 * @return  string  The SQL where clause for this search
	 */
	abstract public function partial($value);

	/**
	 * Perform a between limits match (usually: search for a value between
	 * two numbers or a date between two preset dates). When $include is true
	 * the condition tested is:
	 * $from <= VALUE <= $to
	 * When $include is false the condition tested is:
	 * $from < VALUE < $to
	 *
	 * @param   mixed    $from     The lowest value to compare to
	 * @param   mixed    $to       The highest value to compare to
	 * @param   boolean  $include  Should we include the boundaries in the search?
	 *
	 * @return  string  The SQL where clause for this search
	 */
	abstract public function between($from, $to, $include = true);

	/**
	 * Perform an outside limits match (usually: search for a value outside an
	 * area or a date outside a preset period). When $include is true
	 * the condition tested is:
	 * (VALUE <= $from) || (VALUE >= $to)
	 * When $include is false the condition tested is:
	 * (VALUE < $from) || (VALUE > $to)
	 *
	 * @param   mixed    $from     The lowest value of the excluded range
	 * @param   mixed    $to       The highest value of the excluded range
	 * @param   boolean  $include  Should we include the boundaries in the search?
	 *
	 * @return  string  The SQL where clause for this search
	 */
	abstract public function outside($from, $to, $include = false);

	/**
	 * Perform an interval search (usually: a date interval check)
	 *
	 * @param   string               $from      The value to search
	 * @param   string|array|object  $interval  The interval
	 *
	 * @return  string  The SQL where clause for this search
	 */
	abstract public function interval($from, $interval);

	/**
	 * Perform a between limits match (usually: search for a value between
	 * two numbers or a date between two preset dates). When $include is true
	 * the condition tested is:
	 * $from <= VALUE <= $to
	 * When $include is false the condition tested is:
	 * $from < VALUE < $to
	 *
	 * @param   mixed    $from     The lowest value to compare to
	 * @param   mixed    $to       The highest value to compare to
	 * @param   boolean  $include  Should we include the boundaries in the search?
	 *
	 * @return  string  The SQL where clause for this search
	 */
	abstract public function range($from, $to, $include = true);

	/**
	 * Perform an modulo search
	 *
	 * @param   integer|float  $from      The starting value of the search space
	 * @param   integer|float  $interval  The interval period of the search space
	 * @param   boolean        $include   Should I include the boundaries in the search?
	 *
	 * @return  string  The SQL where clause
	 */
	abstract public function modulo($from, $interval, $include = true);

	/**
	 * Return the SQL where clause for a search
	 *
	 * @param   mixed   $value     The value to search for
	 * @param   string  $operator  The operator to use
	 *
	 * @return  string  The SQL where clause for this search
	 */
	public function search($value, $operator = '=')
	{
		if ($this->isEmpty($value))
		{
			return '';
		}

		$prefix = '';

		if (substr($operator, 0, 1) == '!')
		{
			$prefix   = 'NOT ';
			$operator = substr($operator, 1);
		}

		return $prefix . '(' . $this->getFieldName() . ' ' . $operator . ' ' . $this->db->quote($value) . ')';
	}

	/**
	 * Get the field name
	 *
	 * @return  string    The field name
	 */
	public function getFieldName()
	{
		$name = $this->db->qn($this->name);

		if (!empty($this->tableAlias))
		{
			$name = $this->db->qn($this->tableAlias) . '.' . $name;
		}

		return $name;
	}
}
