<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * User notes table class
 *
 * @since  2.5
 */
class UsersTableNote extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database object
	 *
	 * @since  2.5
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__user_notes', 'id', $db);

		$this->setColumnAlias('published', 'state');

		JTableObserverContenthistory::createObserver($this, array('typeAlias' => 'com_users.note'));
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
		$date = JFactory::getDate()->toSql();
		$userId = JFactory::getUser()->get('id');

		$this->modified_time = $date;
		$this->modified_user_id = $userId;

		if (!((int) $this->review_time))
		{
			// Null date.
			$this->review_time = $this->_db->getNullDate();
		}

		if (empty($this->id))
		{
			// New record.
			$this->created_time = $date;
			$this->created_user_id = $userId;
		}

		// Attempt to store the data.
		return parent::store($updateNulls);
	}
}
