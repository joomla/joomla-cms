<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * User notes table class
 *
 * @package     Joomla.Administrator
 * @subpackage  com_users
 * @since       2.5
 */
class UsersTableNote extends JTable
{
	/**
	 * Constructor
	 *
	 * @param  JDatabase  &$db  Database object
	 *
	 * @since  2.5
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__user_notes', 'id', $db);
	}

	/**
	 * Overloaded store method for the notes table.
	 *
	 * @param   boolean  $updateNulls  Toggle whether null values should be updated.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   2.5
	 */
	public function store($updateNulls = false)
	{
		// Initialise variables.
		$date = JFactory::getDate()->toMySQL();
		$userId = JFactory::getUser()->get('id');

		if (empty($this->id))
		{
			// New record.
			$this->created_time = $date;
			$this->created_user_id = $userId;
		}
		else
		{
			// Existing record.
			$this->modified_time = $date;
			$this->modified_user_id = $userId;
		}

		// Attempt to store the data.
		return parent::store($updateNulls);
	}
}
