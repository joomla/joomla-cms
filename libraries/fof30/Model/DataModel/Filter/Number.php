<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model\DataModel\Filter;

defined('_JEXEC') || die;

class Number extends AbstractFilter
{
	/**
	 * The partial match is mapped to an exact match
	 *
	 * @param   mixed  $value  The value to compare to
	 *
	 * @return  string  The SQL where clause for this search
	 */
	public function partial($value)
	{
		return $this->exact($value);
	}

	/**
	 * Perform a between limits match. When $include is true
	 * the condition tested is:
	 * $from <= VALUE <= $to
	 * When $include is false the condition tested is:
	 * $from < VALUE < $to
	 *
	 * @param   mixed    $from     The lowest value to compare to
	 * @param   mixed    $to       The higherst value to compare to
	 * @param   boolean  $include  Should we include the boundaries in the search?
	 *
	 * @return  string  The SQL where clause for this search
	 */
	public function between($from, $to, $include = true)
	{
		$from = (float) $from;
		$to   = (float) $to;

		if ($this->isEmpty($from) || $this->isEmpty($to))
		{
			return '';
		}

		$extra = '';

		if ($include)
		{
			$extra = '=';
		}

		$from = $this->sanitiseValue($from);
		$to   = $this->sanitiseValue($to);

		$sql = '((' . $this->getFieldName() . ' >' . $extra . ' ' . $from . ') AND ';
		$sql .= '(' . $this->getFieldName() . ' <' . $extra . ' ' . $to . '))';

		return $sql;
	}

	/**
	 * Perform an outside limits match. When $include is true
	 * the condition tested is:
	 * (VALUE <= $from) || (VALUE >= $to)
	 * When $include is false the condition tested is:
	 * (VALUE < $from) || (VALUE > $to)
	 *
	 * @param   mixed    $from     The lowest value of the excluded range
	 * @param   mixed    $to       The higherst value of the excluded range
	 * @param   boolean  $include  Should we include the boundaries in the search?
	 *
	 * @return  string  The SQL where clause for this search
	 */
	public function outside($from, $to, $include = false)
	{
		$from = (float) $from;
		$to   = (float) $to;

		if ($this->isEmpty($from) || $this->isEmpty($to))
		{
			return '';
		}

		$extra = '';

		if ($include)
		{
			$extra = '=';
		}

		$from = $this->sanitiseValue($from);
		$to   = $this->sanitiseValue($to);

		$sql = '((' . $this->getFieldName() . ' <' . $extra . ' ' . $from . ') OR ';
		$sql .= '(' . $this->getFieldName() . ' >' . $extra . ' ' . $to . '))';

		return $sql;
	}

	/**
	 * Perform an interval match. It's similar to a 'between' match, but the
	 * from and to values are calculated based on $value and $interval:
	 * $value - $interval < VALUE < $value + $interval
	 *
	 * @param   integer|float  $value     The center value of the search space
	 * @param   integer|float  $interval  The width of the search space
	 * @param   boolean        $include   Should I include the boundaries in the search?
	 *
	 * @return  string  The SQL where clause
	 */
	public function interval($value, $interval, $include = true)
	{
		if ($this->isEmpty($value))
		{
			return '';
		}

		// Convert them to float, just to be sure
		$value    = (float) $value;
		$interval = (float) $interval;

		$from = $value - $interval;
		$to   = $value + $interval;

		$extra = '';

		if ($include)
		{
			$extra = '=';
		}

		$from = $this->sanitiseValue($from);
		$to   = $this->sanitiseValue($to);

		$sql = '((' . $this->getFieldName() . ' >' . $extra . ' ' . $from . ') AND ';
		$sql .= '(' . $this->getFieldName() . ' <' . $extra . ' ' . $to . '))';

		return $sql;
	}

	/**
	 * Perform a range limits match. When $include is true
	 * the condition tested is:
	 * $from <= VALUE <= $to
	 * When $include is false the condition tested is:
	 * $from < VALUE < $to
	 *
	 * @param   mixed    $from     The lowest value to compare to
	 * @param   mixed    $to       The higherst value to compare to
	 * @param   boolean  $include  Should we include the boundaries in the search?
	 *
	 * @return  string  The SQL where clause for this search
	 */
	public function range($from, $to, $include = true)
	{
		if ($this->isEmpty($from) && $this->isEmpty($to))
		{
			return '';
		}

		$extra = '';

		if ($include)
		{
			$extra = '=';
		}

		$sql = [];

		if ($from)
		{
			$sql[] = '(' . $this->getFieldName() . ' >' . $extra . ' ' . $from . ')';
		}
		if ($to)
		{
			$sql[] = '(' . $this->getFieldName() . ' <' . $extra . ' ' . $to . ')';
		}

		$sql = '(' . implode(' AND ', $sql) . ')';

		return $sql;
	}

	/**
	 * Perform an interval match. It's similar to a 'between' match, but the
	 * from and to values are calculated based on $value and $interval:
	 * $value - $interval < VALUE < $value + $interval
	 *
	 * @param   integer|float  $value     The starting value of the search space
	 * @param   integer|float  $interval  The interval period of the search space
	 * @param   boolean        $include   Should I include the boundaries in the search?
	 *
	 * @return  string  The SQL where clause
	 */
	public function modulo($value, $interval, $include = true)
	{
		if ($this->isEmpty($value) || $this->isEmpty($interval))
		{
			return '';
		}

		$extra = '';

		if ($include)
		{
			$extra = '=';
		}

		$sql = '(' . $this->getFieldName() . ' >' . $extra . ' ' . $value . ' AND ';
		$sql .= '(' . $this->getFieldName() . ' - ' . $value . ') % ' . $interval . ' = 0)';

		return $sql;
	}

	/**
	 * Overrides the parent to handle floats in locales where the decimal separator is a comma instead of a dot
	 *
	 * @param   mixed   $value
	 * @param   string  $operator
	 *
	 * @return  string
	 */
	public function search($value, $operator = '=')
	{
		$value = $this->sanitiseValue($value);

		return parent::search($value, $operator);
	}

	/**
	 * Sanitises float values. Really ugly and desperate workaround. Read below.
	 *
	 * Some locales, such as el-GR, use a comma as the decimal separator. This means that $x = 1.23; echo (string) $x;
	 * will yield 1,23 (with a comma!) instead of 1.23 (with a dot!). This affects the way the SQL WHERE clauses are
	 * generated. All database servers expect a dot as the decimal separator. If they see a decimal with a comma as the
	 * separator they throw a SQL error.
	 *
	 * This method will try to replace commas with dots. I tried working around this with locale switching and the %F
	 * (capital F) format option in sprintf to no avail. I'm pretty sure I was doing something wrong, but I ran out of
	 * time trying to find an academically correct solution. The current implementation of sanitiseValue is a silly
	 * hack around the problem. If you have a proper –and better performing– solution please send in a PR and I'll put
	 * it to the test.
	 *
	 * @param   mixed  $value  A string representing a number, integer, float or array of them.
	 *
	 * @return  mixed  The sanitised value, or null if the input wasn't numeric.
	 */
	public function sanitiseValue($value)
	{
		if (!is_numeric($value) && !is_string($value) && !is_array($value))
		{
			$value = null;
		}

		if (!is_array($value))
		{
			$value = str_replace(',', '.', (string) $value);
		}
		else
		{
			$value = array_map([$this, 'sanitiseValue'], $value);
		}

		return $value;
	}
}
