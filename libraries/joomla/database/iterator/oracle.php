<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
	protected $tolower = true;

	/**
	* Is used to decide whether a result set
	* should return the LOB values or the LOB objects
	*/
	protected $returnlobs = true;

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

		if ($row && $this->tolower)
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
		oci_free_statement($this->cursor);
	}

	/**
	* Sets the $tolower variable to true
	* so that field names will be created
	* using lowercase values.
	*
	* @return void
	*/
	public function toLower()
	{
		$this->tolower = true;
	}

	/**
	* Sets the $tolower variable to false
	* so that field names will be created
	* using uppercase values.
	*
	* @return void
	*/
	public function toUpper()
	{
		$this->tolower = false;
	}

	/**
	* Sets the $returnlobs variable to true
	* so that LOB object values will be
	* returned rather than an OCI-Lob Object.
	*
	* @return void
	*/
	public function returnLobValues()
	{
		$this->returnlobs = true;
	}

	/**
	* Sets the $returnlobs variable to false
	* so that OCI-Lob Objects will be returned.
	*
	* @return void
	*/
	public function returnLobObjects()
	{
		$this->returnlobs = false;
	}

	/**
	* Depending on the value for $returnlobs,
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
			if ($this->returnlobs)
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
			if ($this->returnlobs)
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
