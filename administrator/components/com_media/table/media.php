<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Media Table class
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.4
 */
class MediaTableMedia extends JTableCorecontent
{

	/**
	 * Overriding JTable checkout method for #__ucm_core_content
	 *
	 * @param   integer  $userId  The Id of the user checking out the row.
	 * @param   mixed    $pk      An optional primary key value to check out.  If not set
	 *                            the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.4
	 * @throws  UnexpectedValueException
	 */
	public function checkOut($userId, $pk = null)
	{
		// If there is no checked_out or checked_out_time field, just return true.
		if (!property_exists($this, 'core_checked_out_user_id') || !property_exists($this, 'core_checked_out_time'))
		{
			return true;
		}

		if (is_null($pk))
		{
			$pk = array();

			foreach ($this->_tbl_keys AS $key)
			{
				$pk[$key] = $this->$key;
			}
		}
		elseif (!is_array($pk))
		{
			$pk = array($this->_tbl_key => $pk);
		}

		foreach ($this->_tbl_keys AS $key)
		{
			$pk[$key] = is_null($pk[$key]) ? $this->$key : $pk[$key];

			if ($pk[$key] === null)
			{
				throw new UnexpectedValueException('Null primary key not allowed.');
			}
		}

		// Get the current time in the database format.
		$time = JFactory::getDate()->toSql();

		// Check the row out by primary key.
		$query = $this->_db->getQuery(true)
		->update($this->_tbl)
		->set($this->_db->quoteName('core_checked_out_user_id') . ' = ' . (int) $userId)
		->set($this->_db->quoteName('core_checked_out_time') . ' = ' . $this->_db->quote($time));
		$this->appendPrimaryKeys($query, $pk);
		$this->_db->setQuery($query);
		$this->_db->execute();

		// Set table values in the object.
		$this->core_checked_out_user_id      = (int) $userId;
		$this->core_checked_out_time = $time;

		return true;
	}

	/**
	 * Overriding JTable checkin method for #__ucm_core_content
	 *
	 * @param   mixed  $pk  An optional primary key value to check out.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.4
	 * @throws  UnexpectedValueException
	 */
	public function checkIn($pk = null)
	{
		// If there is no checked_out or checked_out_time field, just return true.
		if (!property_exists($this, 'core_checked_out_user_id') || !property_exists($this, 'core_checked_out_time'))
		{
			return true;
		}

		if (is_null($pk))
		{
			$pk = array();

			foreach ($this->_tbl_keys AS $key)
			{
				$pk[$this->$key] = $this->$key;
			}
		}
		elseif (!is_array($pk))
		{
			$pk = array($this->_tbl_key => $pk);
		}

		foreach ($this->_tbl_keys AS $key)
		{
			$pk[$key] = empty($pk[$key]) ? $this->$key : $pk[$key];

			if ($pk[$key] === null)
			{
				throw new UnexpectedValueException('Null primary key not allowed.');
			}
		}

		// Check the row in by primary key.
		$query = $this->_db->getQuery(true)
		->update($this->_tbl)
		->set($this->_db->quoteName('core_checked_out_user_id') . ' = 0')
		->set($this->_db->quoteName('core_checked_out_time') . ' = ' . $this->_db->quote($this->_db->getNullDate()));
		$this->appendPrimaryKeys($query, $pk);
		$this->_db->setQuery($query);

		// Check for a database error.
		$this->_db->execute();

		// Set table values in the object.
		$this->core_checked_out_user_id = 0;
		$this->core_checked_out_time = '';

		return true;
	}

}
