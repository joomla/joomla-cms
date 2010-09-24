<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Private Message model.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class MessagesModelMessage extends JModelAdmin
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		parent::populateState();

		$user = JFactory::getUser();
		$this->setState('user.id', $user->get('id'));

		$messageId = (int) JRequest::getInt('message_id');
		$this->setState('message.id', $messageId);

		$replyId = (int) JRequest::getInt('reply_id');
		$this->setState('reply.id', $replyId);
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	*/
	public function getTable($type = 'Message', $prefix = 'MessagesTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) {
			// Prime required properties.
			if (empty($item->id)) {
				// Prepare data for a new record.
				if ($replyId = $this->getState('reply.id')) {
					// If replying to a message, preload some data.
					$db		= $this->getDbo();
					$query	= $db->getQuery(true);

					$query->select('subject, user_id_from');
					$query->from('#__messages');
					$query->where('message_id = '.(int) $replyId);
					$message = $db->setQuery($query)->loadObject();

					if ($error = $db->getErrorMsg()) {
						$this->setError($error);
						return false;
					}

					$item->set('user_id_to', $message->user_id_from);
					$re = JText::_('COM_MESSAGES_RE');
					if (stripos($message->subject, $re) !== 0) {
						$item->set('subject', $re.$message->subject);
					}
				}
			}
		}
		
		// Get the user name for an existing messasge.
		if ($item->user_id_from && $fromUser = new JUser($item->user_id_from)) {
			$item->set('from_user_name', $fromUser->name);
		}		

		return $item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_messages.message', 'message', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_messages.edit.message.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
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

		// Bind the data.
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

		// Check the data.
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
			$this->setError(JText::_('COM_MESSAGES_ERR_SEND_FAILED'));
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		if ($config->get('mail_on_new')) {
			// Load the user details (already valid from table check).
			$fromUser	= new JUser($table->user_id_from);
			$toUser		= new JUser($table->user_id_to);

			$siteURL	= JURI::base();
			$sitename	= JFactory::getApplication()->getCfg('sitename');

			$subject	= sprintf (JText::_('COM_MESSAGES_NEW_MESSAGE_ARRIVED'), $sitename);
			$msg		= sprintf (JText::_('COM_MESSAGES_PLEASE_LOGIN'), $siteURL);

			JUtility::sendMail($fromUser->email, $fromUser->name, $toUser->email, $subject, $msg);
		}

		return true;
	}
}