<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die;

jimport('joomla.application.component.model');

/**
 * Messages Component Message Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class MessagesModelMessage extends JModel
{

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function _populateState()
	{
		$app		= &JFactory::getApplication('administrator');

		$messageId = (int) JRequest::getInt('message_id');
		$this->setState('message.id', $messageId);
	}

	public function save($data)
	{
		$table = $this->getTable();

		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		if (!$table->send()) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	public function getSubject()
	{
		return JRequest::getString('subject');
	}

	public function getRecipientsList()
	{
		$access	= &JFactory::getACL();
		$groups	= array();

		$userid = JRequest::getInt('userid', 0);

		// Include user in groups that have access to log in to the administrator.
		$return = $access->getAuthorisedUsergroups('core.manageistrator.login', true);
		if (count($return)) {
			$groups = array_merge($groups, $return);
		}

		// Remove duplicate entries and serialize.
		JArrayHelper::toInteger($groups);
		$groups = implode(',', array_unique($groups));

		// Build the query to get the users.
		$query = new JQuery();
		$query->select('u.id AS value');
		$query->select('u.name AS text');
		$query->from('#__users AS u');
		$query->join('INNER', '#__user_usergroup_map AS m ON m.user_id = u.id');
		$query->where('u.block = 0');
		$query->where('m.group_id IN ('.$groups.')');

		// Get the users.
		$this->_db->setQuery((string) $query);
		$users = $this->_db->loadObjectList();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			JError::raiseNotice(500, $this->_db->getErrorMsg());
			return false;
		}

		// Build the options.
		$options = array(JHtml::_('select.option',  '0', '- '. JText::_('Select User') .' -'));

		if (count($users)) {
			$options = array_merge($options, $users);
		}

		return JHtml::_('select.genericlist', $options, 'user_id_to', 'class="inputbox" size="1"', 'value', 'text', $userid);
	}

	/**
	 * Returns a member item for the current message_id
	 *
	 * @return mixed	An object representing a database table row
	 */
	public function getItem()
	{
		$id = (int) $this->getState('message.id');

		$query = new JQuery();
		$query->select('a.*');
		$query->select('u.name AS user_from');
		$query->from('#__messages AS a');
		$query->join('INNER', '#__users AS u ON u.id = a.user_id_from');
		$query->where('a.message_id = '.$id);

		$this->_db->setQuery($query);
		$row = $this->_db->loadObject();

		return $row;
	}

	/**
	 * When messages are displayed through com_messages, it is necessary
	 * to mark them as 'read' so they do not appear as new messages.
	 *
	 * @return boolean		True on success / false on failure
	 */
	public function markAsRead()
	{
		$query = 'UPDATE #__messages'
		. ' SET state = 1'
		. ' WHERE message_id = ' . (int) $this->getState('message.id')
		;

		$this->_db->setQuery($query);
		$this->_db->query();

		return true;
	}

	/**
	 * Method to delete messages from the database
	 *
	 * @param integer $cid 		An array of numeric ids for the rows
	 * @return boolean 			True on success / false on failure
	 */
	public function delete($cid)
	{
		// Get a message row instance
		$table = $this->getTable();

		for ($i = 0, $c = count($cid); $i < $c; $i++) {
			// Load the row.
			$return = $table->load($cid[$i]);

			// Check for an error.
			if ($return === false) {
				$this->setError($table->getError());
				return false;
			}

			// Delete the row.
			$return = $table->delete();

			// Check for an error.
			if ($return === false) {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}
}