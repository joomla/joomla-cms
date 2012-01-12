<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
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
	 * message
	 */
	protected $item;

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
		if (!isset($this->item))
		{
			if ($this->item = parent::getItem($pk)) {
				// Prime required properties.
				if (empty($this->item->message_id))
				{
					// Prepare data for a new record.
					if ($replyId = $this->getState('reply.id'))
					{
						// If replying to a message, preload some data.
						$db		= $this->getDbo();
						$query	= $db->getQuery(true);

						$query->select('subject, user_id_from');
						$query->from('#__messages');
						$query->where('message_id = '.(int) $replyId);
						$message = $db->setQuery($query)->loadObject();

						if ($error = $db->getErrorMsg())
						{
							$this->setError($error);
							return false;
						}


						$this->item->set('user_id_to', $message->user_id_from);
						$re = JText::_('COM_MESSAGES_RE');
						if (stripos($message->subject, $re) !== 0) {
							$this->item->set('subject', $re.$message->subject);
						}
					}
				}
				elseif ($this->item->user_id_to != JFactory::getUser()->id)
				{
					$this->setError(JText::_('JERROR_ALERTNOAUTHOR'));
					return false;
				}
				else {
					// Mark message read
					$db		= $this->getDbo();
					$query	= $db->getQuery(true);
					$query->update('#__messages');
					$query->set('state = 1');
					$query->where('message_id = '.$this->item->message_id);
					$db->setQuery($query)->query();
				}
			}

			// Get the user name for an existing messasge.
			if ($this->item->user_id_from && $fromUser = new JUser($this->item->user_id_from)) {
				$this->item->set('from_user_name', $fromUser->name);
			}
		}
		return $this->item;
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
			$table->date_time = JFactory::getDate()->toSql();
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

		if ($config->get('locked', false)) {
			$this->setError(JText::_('COM_MESSAGES_ERR_SEND_FAILED'));
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		if ($config->get('mail_on_new', true)) {
			// Load the user details (already valid from table check).
			$fromUser = JUser::getInstance($table->user_id_from);
			$toUser = JUser::getInstance($table->user_id_to);
			$debug = JFactory::getConfig()->get('debug_lang');
			$default_language = JComponentHelper::getParams('com_languages')->get('administrator');
			$lang = JLanguage::getInstance($toUser->getParam('admin_language', $default_language), $debug);
			$lang->load('com_messages', JPATH_ADMINISTRATOR);

			$siteURL	= JURI::root() . 'administrator/index.php?option=com_messages&view=message&message_id='.$table->message_id;
			$sitename	= JFactory::getApplication()->getCfg('sitename');

			$subject	= sprintf ($lang->_('COM_MESSAGES_NEW_MESSAGE_ARRIVED'), $sitename);
			$msg		= sprintf ($lang->_('COM_MESSAGES_PLEASE_LOGIN'), $siteURL);
			JFactory::getMailer()->sendMail($fromUser->email, $fromUser->name, $toUser->email, $subject, $msg);
		}

		return true;
	}
}
