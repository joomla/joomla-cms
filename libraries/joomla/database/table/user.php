<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Users table
 *
 * @package		Joomla.Framework
 * @subpackage	Table
 * @since		1.0
 */
class JTableUser extends JTable
{
	/**
	 * Associative array of user names => group ids
	 *
	 * @access	public
	 * @since	1.6
	 * @var		array
	 */
	var $groups;

	/**
	* @param database A database connector object
	*/
	function __construct(&$db)
	{
		parent::__construct('#__users', 'id', $db);

		// Initialise.
		$this->id = 0;
		$this->sendEmail = 0;
	}

	/**
	 * Method to load a user, user groups, and any other necessary data
	 * from the database so that it can be bound to the user object.
	 *
	 * @access	public
	 * @param	integer		$userId		An optional user id.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.0
	 */
	function load($userId = null, $reset = true)
	{
		// Get the id to load.
		if ($userId !== null) {
			$this->id = $userId;
		} else {
			$userId = $this->id;
		}

		// Check for a valid id to load.
		if ($userId === null) {
			return false;
		}

		// Reset the table.
		$this->reset();

		// Load the user data.
		$this->_db->setQuery(
			'SELECT *' .
			' FROM #__users' .
			' WHERE id = '.(int) $userId
		);
		$data = (array) $this->_db->loadAssoc();

		// Check for an error message.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if(!count($data))
		{
			return false;
		}
		// Bind the data to the table.
		$return = $this->bind($data);

		if ($return !== false)
		{
			// Load the user groups.
			$this->_db->setQuery(
				'SELECT g.id, g.title' .
				' FROM #__usergroups AS g' .
				' JOIN #__user_usergroup_map AS m ON m.group_id = g.id' .
				' WHERE m.user_id = '.(int) $userId
			);
			// Add the groups to the user data.
			$this->groups = $this->_db->loadAssocList('title','id');

			// Check for an error message.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return $return;
	}

	/**
	 * Method to bind the user, user groups, and any other necessary data.
	 *
	 * @access	public
	 * @param	array		$array		The data to bind.
	 * @param	mixed		$ignore		An array or space separated list of fields to ignore.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.0
	 */
	function bind($array, $ignore = '')
	{
		if (key_exists('params', $array) && is_array($array['params'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}

		// Attempt to bind the data.
		$return = parent::bind($array, $ignore);

		// Load the real group data based on the bound ids.
		if ($return && !empty($this->groups))
		{
			// Set the group ids.
			JArrayHelper::toInteger($this->groups);

			// Get the titles for the user groups.
			$this->_db->setQuery(
				'SELECT `id`, `title`' .
				' FROM `#__usergroups`' .
				' WHERE `id` = '.implode(' OR `id` = ', $this->groups)
			);
			// Set the titles for the user groups.
			$this->groups = $this->_db->loadAssocList('title','id');

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return $return;
	}

	/**
	 * Validation and filtering
	 *
	 * @return boolean True is satisfactory
	 */
	function check()
	{
		jimport('joomla.mail.helper');

		// Validate user information
		if (trim($this->name) == '') {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_YOUR_NAME'));
			return false;
		}

		if (trim($this->username) == '') {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_A_USER_NAME'));
			return false;
		}

		if (preg_match( "#[<>\"'%;()&]#i", $this->username) || strlen(utf8_decode($this->username )) < 2) {
			$this->setError( JText::sprintf( 'JLIB_DATABASE_ERROR_VALID_AZ09', 2 ));
			return false;
		}

		if ((trim($this->email) == "") || ! JMailHelper::isEmailAddress($this->email)) {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_VALID_MAIL'));
			return false;
		}

		// Set the registration timestamp
		if ($this->registerDate == null || $this->registerDate == '0000-00-00 00:00:00' ) {
			$this->registerDate = JFactory::getDate()->toMySQL();
		}


		// check for existing username
		$query = 'SELECT id'
		. ' FROM #__users '
		. ' WHERE username = ' . $this->_db->Quote($this->username)
		. ' AND id != '. (int) $this->id;
		;
		$this->_db->setQuery($query);
		$xid = intval($this->_db->loadResult());
		if ($xid && $xid != intval($this->id)) {
			$this->setError( JText::_('JLIB_DATABASE_ERROR_USERNAME_INUSE'));
			return false;
		}


		// check for existing email
		$query = 'SELECT id'
			. ' FROM #__users '
			. ' WHERE email = '. $this->_db->Quote($this->email)
			. ' AND id != '. (int) $this->id
			;
		$this->_db->setQuery($query);
		$xid = intval($this->_db->loadResult());
		if ($xid && $xid != intval($this->id)) {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_EMAIL_INUSE'));
			return false;
		}

		// check for root_user != username
		$config		= JFactory::getConfig();
		$rootUser	= $config->get('root_user');
		if (!is_numeric($rootUser))
		{
			$query = $this->_db->getQuery(true);
			$query->select('id');
			$query->from('#__users');
			$query->where('username = '.$this->_db->quote($rootUser));
			$this->_db->setQuery($query);
			$xid = intval($this->_db->loadResult());
			if ($rootUser==$this->username && (!$xid || $xid && $xid != intval($this->id))  || $xid && $xid == intval($this->id) && $rootUser!=$this->username) {
				$this->setError( JText::_('JLIB_DATABASE_ERROR_USERNAME_CANNOT_CHANGE'));
				return false;
			}
		}

		return true;
	}

	function store($updateNulls = false)
	{
		// Get the table key and key value.
		$k = $this->_tbl_key;
		$key =  $this->$k;

		// TODO: This is a dumb way to handle the groups.
		// Store groups locally so as to not update directly.
		$groups = $this->groups;
		unset($this->groups);

		// Insert or update the object based on presence of a key value.
		if ($key) {
			// Already have a table key, update the row.
			$return = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
		}
		else {
			// Don't have a table key, insert the row.
			$return = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
		}

		// Handle error if it exists.
		if (!$return)
		{
			$this->setError(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', strtolower(get_class($this)), $this->_db->getErrorMsg()));
			return false;
		}

		// Reset groups to the local object.
		$this->groups = $groups;
		unset($groups);

		// Store the group data if the user data was saved.
		if ($return && is_array($this->groups) && count($this->groups))
		{
			// Delete the old user group maps.
			$this->_db->setQuery(
				'DELETE FROM `#__user_usergroup_map`' .
				' WHERE `user_id` = '.(int) $this->id
			);
			$this->_db->query();

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			// Set the new user group maps.
			$this->_db->setQuery(
				'INSERT INTO `#__user_usergroup_map` (`user_id`, `group_id`)' .
				' VALUES ('.$this->id.', '.implode('), ('.$this->id.', ', $this->groups).')'
			);
			$this->_db->query();

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to delete a user, user groups, and any other necessary
	 * data from the database.
	 *
	 * @access	public
	 * @param	integer		$userId		An optional user id.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.0
	 */
	function delete($userId = null)
	{
		// Set the primary key to delete.
		$k = $this->_tbl_key;
		if ($userId) {
			$this->$k = intval($userId);
		}

		// Delete the user.
		$this->_db->setQuery(
			'DELETE FROM `'.$this->_tbl.'`' .
			' WHERE `'.$this->_tbl_key.'` = '.(int) $this->$k
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Delete the user group maps.
		$this->_db->setQuery(
			'DELETE FROM `#__user_usergroup_map`' .
			' WHERE `user_id` = '.(int) $this->$k
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		/*
		 * Clean Up Related Data.
		 */

		$this->_db->setQuery(
			'DELETE FROM `#__messages_cfg`' .
			' WHERE `user_id` = '.(int) $this->$k
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$this->_db->setQuery(
			'DELETE FROM `#__messages`' .
			' WHERE `user_id_to` = '.(int) $this->$k
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Updates last visit time of user
	 *
	 * @param int The timestamp, defaults to 'now'
	 * @return boolean False if an error occurs
	 */
	function setLastVisit($timeStamp = null, $userId = null)
	{
		// Check for User ID
		if (is_null($userId))
		{
			if (isset($this)) {
				$userId = $this->id;
			} else {
				// do not translate
				jexit(JText::_('JLIB_DATABASE_ERROR_SETLASTVISIT'));
			}
		}

		// If no timestamp value is passed to functon, than current time is used.
		$date = JFactory::getDate($timeStamp);

		// Update the database row for the user.
		$this->_db->setQuery(
			'UPDATE `'.$this->_tbl.'`' .
			' SET `lastvisitDate` = '.$this->_db->Quote($date->toMySQL()) .
			' WHERE `id` = '.(int) $userId
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}
}
