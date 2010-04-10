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
	protected $_context = 'com_messages';

	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->_item = 'message';
		$this->_option = 'com_messages';
	}
	
	/**
	 * Method to auto-populate the model state.
	 */
	protected function _populateState()
	{
		$user = JFactory::getUser();
		$this->setState('user.id', $user->get('id'));

		$messageId = (int) JRequest::getInt('message_id');
		$this->setState('message.id', $messageId);

		$replyId = (int) JRequest::getInt('reply_id');
		$this->setState('reply.id', $replyId);

		// Load the parameters.
		$params	= JComponentHelper::getParams('com_messages');
		$this->setState('params', $params);
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
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

		// Convert to the JObject before adding other data.
		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');

		// Prime required properties.
		if (empty($table->id)) {
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

				$value->set('user_id_to', $message->user_id_from);
				$re = JText::_('COM_MESSAGES_RE');
				if (stripos($message->subject, $re) !== 0) {
					$value->set('subject', $re.$message->subject);
				}
			}
		} else {
			// Get the user name for an existing messasge.
			if ($table->user_id_from && $fromUser = new JUser($table->user_id_from)) {
				$value->set('from_user_name', $fromUser->name);
			}
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
		try {
			$form = parent::getForm('com_messages.message', 'message', array('control' => 'jform'));
		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_messages.edit.message.data', array());

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
	
	function _orderConditions($table = null)
	{
		$condition = array();
		return $condition;	
	}
}