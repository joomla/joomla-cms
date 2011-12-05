<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since       2.5.0
 */
class UsersTableNote extends JTable
{
	/*
	 * Constructor
	 *
	 * @param	object	$db	Database object
	 *
	 * @since	1.1
	 */
	function __construct(&$db)
	{
		parent::__construct('#__user_notes', 'id', $db);
	}

	/**
	 * Overload the store method for the Weblinks table.
	 *
	 * @param	boolean	$updateNulls	Toggle whether null values should be updated.
	 *
	 * @return	boolean	True on success, false on failure.
	 * @since	1.0
	 */
	public function store($updateNulls = false)
	{
		// Initialiase variables.
		$date	= JFactory::getDate()->toMySQL();
		$userId	= JFactory::getUser()->get('id');

		if (empty($this->id)) {
			// New record.
			$this->created_time		= $date;
			$this->created_user_id	= $userId;
		}
		else {
			// Existing record.
			$this->modified_time	= $date;
			$this->modified_user_id	= $userId;
		}

		// Attempt to store the data.
		return parent::store($updateNulls);
	}
}