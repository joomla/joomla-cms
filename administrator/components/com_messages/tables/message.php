<?php
/**
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.database.table');

/**
 * Message Table class
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.5
 */
class MessagesTableMessage extends JTable
{
	/**
	 * Constructor
	 *
	 * @param database A database connector object
	 */
	function __construct(& $db)
	{
		parent::__construct('#__messages', 'message_id', $db);
	}

	/**
	 * Validation and filtering.
	 *
	 * @return boolean
	 */
	function check()
	{
		// Check the to and from users.
		$user = new JUser($this->user_id_from);
		if (empty($user->id)) {
			$this->setError(JText::_('COM_MESSAGES_ERROR_INVALID_FROM_USER'));
			return false;
		}

		$user = new JUser($this->user_id_to);
		if (empty($user->id)) {
			$this->setError(JText::_('COM_MESSAGES_ERROR_INVALID_TO_USER'));
			return false;
		}

		if (empty($this->subject)) {
			$this->setError(JText::_('COM_MESSAGES_ERROR_INVALID_SUBJECT'));
			return false;
		}

		if (empty($this->message)) {
			$this->setError(JText::_('COM_MESSAGES_ERROR_INVALID_MESSAGE'));
			return false;
		}

		return true;
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param	mixed	An optional array of primary key values to update.  If not
	 *					set the instance property value is used.
	 * @param	integer The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param	integer The user id of the user performing the operation.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k) {
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else {
				$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				return false;
			}
		}

		// Build the WHERE clause for the primary keys.
		$where = $k.' IN ('.implode(',', $pks).')';

		// Update the publishing state for rows with the given primary keys.
		$this->_db->setQuery(
			'UPDATE '.$this->_db->quoteName($this->_tbl).
			' SET '.$this->_db->quoteName('state').' = '.(int) $state .
			' WHERE ('.$where.')'
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks)) {
			$this->state = $state;
		}

		$this->setError('');
		return true;
	}
}
