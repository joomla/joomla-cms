<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Pdo;

use Joomla\Database\DatabaseIterator;

/**
 * PDO Database Iterator.
 *
 * @since  1.0
 */
class PdoIterator extends DatabaseIterator
{
	/**
	 * Get the number of rows in the result set for the executed SQL given by the cursor.
	 *
	 * @return  integer  The number of rows in the result set.
	 *
	 * @since   1.0
	 * @see     Countable::count()
	 */
	public function count()
	{
		if (!empty($this->cursor) && $this->cursor instanceof \PDOStatement)
		{
			return $this->cursor->rowCount();
		}

		return 0;
	}

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @return  mixed   Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   1.0
	 */
	protected function fetchObject()
	{
		if (!empty($this->cursor) && $this->cursor instanceof \PDOStatement)
		{
			return $this->cursor->fetchObject($this->class);
		}

		return false;
	}

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function freeResult()
	{
		if (!empty($this->cursor) && $this->cursor instanceof \PDOStatement)
		{
			$this->cursor->closeCursor();
		}
	}
}
