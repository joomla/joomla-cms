<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Private Message model.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class MessagesModelMessage extends JModelForm
{
	/**
	 * Method to auto-populate the model state.
	 */
	protected function _populateState()
	{
		$user = JFactory::getUser();
		$this->setState('user.id', $user->get('id'));

		$messageId = (int) JRequest::getInt('message_id');
		$this->setState('message.id', $messageId);

		// Load the parameters.
		$params	= JComponentHelper::getParams('com_messages');
		$this->setState('params', $params);
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param	type 	$type 	 The table type to instantiate
	 * @param	string 	$prefix	 A prefix for the table class name. Optional.
	 * @param	array	$options Configuration array for model. Optional.
	 * @return	JTable	A database object
	*/
	public function getTable($type = 'Message', $prefix = 'MessagesTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function &getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('message.id');
		$false	= false;

		// Get a row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load($pk);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return $false;
		}

		// Prime required properties.
		if (empty($table->id)) {
			// Prepare data for a new record.
		} else {
			// Get the user names
			$query = new JQuery;
			$query->select('name, username');
			$query->from('#__users');
			$query->where('id = '.(int) $this->user_id_from);
		}

		// Convert to the JObject before adding other data.
		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');

		if ($fromUser = new JUser($table->user_id_from)) {
			$value->set('from_user_name', $fromUser->name);
		}

		return $value;
	}

	/**
	 * Method to get the record form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 */
	public function getForm()
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = parent::getForm('message', 'com_messages.message', array('array' => 'jform', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		// Determine correct permissions to check.
		if ($this->getState('newsfeed.id'))
		{
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit');
		}
		else
		{
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_newsfeeds.edit.newsfeed.data', array());

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 *
	 * @return	boolean	True on success.
	 */
	public function save($data)
	{
		$table = $this->getTable();

		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Assign empty values.
		if (empty($table->user_id_from)) {
			$table->user_id_from = JFactory::getUser()->get('id');
		}
		if (intval($table->date_time) == 0) {
			$table->date_time = JFactory::getDate()->toMySQL();
		}

		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Load the recipient user configuration.
		$model = JModel::getInstance('Config', 'MessagesModel', array('ignore_request' => true));
		$model->setState('user.id', $table->user_id_to);
		$config = $model->getItem();
		if (empty($config)) {
			$this->setError($model->getError());
			return false;
		}

		if ($config->get('locked')) {
			$this->setError(JText::_('MESSAGE_FAILED'));
			return false;
		}

		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		if ($config->get('mail_on_new')) {
			// Load the user details (already valid from table check).
			$fromUser	= new JUser($table->user_id_from);
			$toUser		= new JUser($table->user_id_to);

			$siteURL	= JURI::base();
			$sitename 	= JFactory::getApplication()->getCfg('sitename');

			$subject	= sprintf (JText::_('A new private message has arrived'), $sitename);
			$msg		= sprintf (JText::_('Please login to read your message'), $siteURL);

			JUtility::sendMail($fromUser->email, $fromUser->name, $toUser->email, $subject, $msg);
		}

		return true;
	}

	public function getRecipientsList()
	{
		$access	= &JFactory::getACL();
		$groups	= array();

		$userid = JRequest::getInt('userid', 0);

		// Include user in groups that have access to log in to the administrator.
		/*
		TODO: Fix this
		$return = $access->getAuthorisedUsergroups('core.manageistrator.login', true);
		if (count($return)) {
			$groups = array_merge($groups, $return);
		}
		 */

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
		if ($groups) {
			$query->where('m.group_id IN ('.$groups.')');
		}

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