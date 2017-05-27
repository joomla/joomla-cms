<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Oracle database iterator.
 *
 * @since  12.1
 */
class JDatabaseIteratorOracle extends JDatabaseIterator
{
	/**
	* Is used to decide whether a result set
	* should generate lowercase field names
	*
	* @var boolean
	*/
	protected $toLower = true;

	/**
	* Is used to decide whether a result set
	* should return the LOB values or the LOB objects
	*
	* @var boolean
	*/
	protected $returnLobs = true;

	/**
	 * Database iterator constructor.
	 *
	 * @param   mixed   $cursor      The database cursor.
	 * @param   string  $column      An option column to use as the iterator key.
	 * @param   string  $class       The class of object that is returned.
	 * @param   bool    $toLower     The class of object that is returned.
	 * @param   bool    $returnLobs  The class of object that is returned.
	 *
	 * @throws  InvalidArgumentException
	 */
	public function __construct($cursor, $column = null, $class = 'stdClass', $toLower = true, $returnLobs = true)
	{
		if (!class_exists($class))
		{
			throw new InvalidArgumentException(sprintf('new %s(*%s*, cursor)', get_class($this), gettype($class)));
		}

		$this->cursor = $cursor;
		$this->class = $class;
		$this->toLower = (bool) $toLower;
		$this->returnLobs = (bool) $returnLobs;
		$this->_column = $column;
		$this->_fetched = 0;
		$this->next();
	}

	/**
	 * Get the number of rows in the result set for the executed SQL given by the cursor.
	 *
	 * @return  integer  The number of rows in the result set.
	 *
	 * @since   12.1
	 * @see     Countable::count()
	 */
	public function count()
	{
		return oci_num_rows($this->cursor);
	}

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @return  mixed   Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   12.1
	 */
	protected function fetchObject()
	{
		$mode = $this->getMode();

		$row = oci_fetch_array($this->cursor, $mode);

		if ($row && $this->toLower)
		{
			$row = array_change_key_case($row);
		}

		if ($row)
		{
			if ($this->class !== 'stdClass')
			{
				$row = new $this->class($row);
			}
			else
			{
				$row = (object) $row;
			}
		}

		return $row;
	}

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function freeResult()
	{
		@oci_free_statement($this->cursor);
	}

	/**
	* Depending on the value for $returnLobs,
	* this method returns the proper constant
	* combinations to be passed to the oci* functions
	*
	* @param   bool  $numeric  Assoc or Numeric Mode
	*
	* @return int
	*/
	public function getMode($numeric = false)
	{
		if ($numeric === false)
		{
			if ($this->returnLobs)
			{
				$mode = OCI_ASSOC+OCI_RETURN_NULLS+OCI_RETURN_LOBS;
			}
			else
			{
				$mode = OCI_ASSOC+OCI_RETURN_NULLS;
			}
		}
		else
		{
			if ($this->returnLobs)
			{
				$mode = OCI_NUM+OCI_RETURN_NULLS+OCI_RETURN_LOBS;
			}
			else
			{
				$mode = OCI_NUM+OCI_RETURN_NULLS;
			}
		}

		return $mode;
	}
}
