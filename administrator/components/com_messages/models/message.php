<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/**
 * Private Message model.
 *
 * @since  1.6
 */
class MessagesModelMessage extends JModelAdmin
{
	/**
	 * Message
	 */
	protected $item;

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		parent::populateState();

		$input = JFactory::getApplication()->input;

		$user  = JFactory::getUser();
		$this->setState('user.id', $user->get('id'));

		$messageId = (int) $input->getInt('message_id');
		$this->setState('message.id', $messageId);

		$replyId = (int) $input->getInt('reply_id');
		$this->setState('reply.id', $replyId);
	}

	/**
	 * Check that recipient user is the one trying to delete and then call parent delete method
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since  3.1
	 */
	public function delete(&$pks)
	{
		$pks   = (array) $pks;
		$table = $this->getTable();
		$user  = JFactory::getUser();

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				if ($table->user_id_to != $user->id)
				{
					// Prune items that you can't change.
					unset($pks[$i]);

					try
					{
						JLog::add(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
					}
					catch (RuntimeException $exception)
					{
						JFactory::getApplication()->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 'warning');
					}

					return false;
				}
			}
			else
			{
				$this->setError($table->getError());

				return false;
			}
		}

		return parent::delete($pks);
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A database object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'Message', $prefix = 'MessagesTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		if (!isset($this->item))
		{
			if ($this->item = parent::getItem($pk))
			{
				// Invalid message_id returns 0
				if ($this->item->user_id_to === '0')
				{
					$this->setError(JText::_('JERROR_ALERTNOAUTHOR'));

					return false;
				}

				// Prime required properties.
				if (empty($this->item->message_id))
				{
					// Prepare data for a new record.
					if ($replyId = $this->getState('reply.id'))
					{
						// If replying to a message, preload some data.
						$db    = $this->getDbo();
						$query = $db->getQuery(true)
							->select($db->quoteName(array('subject', 'user_id_from', 'user_id_to')))
							->from($db->quoteName('#__messages'))
							->where($db->quoteName('message_id') . ' = ' . (int) $replyId);

						try
						{
							$message = $db->setQuery($query)->loadObject();
						}
						catch (RuntimeException $e)
						{
							$this->setError($e->getMessage());

							return false;
						}

						if (!$message || $message->user_id_to != JFactory::getUser()->id)
						{
							$this->setError(JText::_('JERROR_ALERTNOAUTHOR'));

							return false;
						}

						$this->item->set('user_id_to', $message->user_id_from);
						$re = JText::_('COM_MESSAGES_RE');

						if (stripos($message->subject, $re) !== 0)
						{
							$this->item->set('subject', $re . ' ' . $message->subject);
						}
					}
				}
				elseif ($this->item->user_id_to != JFactory::getUser()->id)
				{
					$this->setError(JText::_('JERROR_ALERTNOAUTHOR'));

					return false;
				}
				else
				{
					// Mark message read
					$db    = $this->getDbo();
					$query = $db->getQuery(true)
						->update($db->quoteName('#__messages'))
						->set($db->quoteName('state') . ' = 1')
						->where($db->quoteName('message_id') . ' = ' . $this->item->message_id);
					$db->setQuery($query)->execute();
				}
			}

			// Get the user name for an existing message.
			if ($this->item->user_id_from && $fromUser = new JUser($this->item->user_id_from))
			{
				$this->item->set('from_user_name', $fromUser->name);
			}
		}

		return $this->item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm   A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_messages.message', 'message', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_messages.edit.message.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_messages.message', $data);

		return $data;
	}

	/**
	 * Checks that the current user matches the message recipient and calls the parent publish method
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 */
	public function publish(&$pks, $value = 1)
	{
		$user  = JFactory::getUser();
		$table = $this->getTable();
		$pks   = (array) $pks;

		// Check that the recipient matches the current user
		foreach ($pks as $i => $pk)
		{
			$table->reset();

			if ($table->load($pk))
			{
				if ($table->user_id_to != $user->id)
				{
					// Prune items that you can't change.
					unset($pks[$i]);

					try
					{
						JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
					}
					catch (RuntimeException $exception)
					{
						JFactory::getApplication()->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'warning');
					}

					return false;
				}
			}
		}

		return parent::publish($pks, $value);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$table = $this->getTable();

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Assign empty values.
		if (empty($table->user_id_from))
		{
			$table->user_id_from = JFactory::getUser()->get('id');
		}

		if ((int) $table->date_time == 0)
		{
			$table->date_time = JFactory::getDate()->toSql();
		}

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Load the user details (already valid from table check).
		$toUser = \JUser::getInstance($table->user_id_to);

		// Check if recipient can access com_messages.
		if (!$toUser->authorise('core.login.admin') || !$toUser->authorise('core.manage', 'com_messages'))
		{
			$this->setError(\JText::_('COM_MESSAGES_ERROR_RECIPIENT_NOT_AUTHORISED'));

			return false;
		}

		// Load the recipient user configuration.
		$model  = JModelLegacy::getInstance('Config', 'MessagesModel', array('ignore_request' => true));
		$model->setState('user.id', $table->user_id_to);
		$config = $model->getItem();

		if (empty($config))
		{
			$this->setError($model->getError());

			return false;
		}

		if ($config->get('lock', false))
		{
			$this->setError(JText::_('COM_MESSAGES_ERR_SEND_FAILED'));

			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		if ($config->get('mail_on_new', true))
		{
			$fromUser         = JUser::getInstance($table->user_id_from);
			$debug            = JFactory::getConfig()->get('debug_lang');
			$default_language = JComponentHelper::getParams('com_languages')->get('administrator');
			$lang             = JLanguage::getInstance($toUser->getParam('admin_language', $default_language), $debug);
			$lang->load('com_messages', JPATH_ADMINISTRATOR);

			// Build the email subject and message
			$app      = JFactory::getApplication();
			$linkMode = $app->get('force_ssl', 0) >= 1 ? Route::TLS_FORCE : Route::TLS_IGNORE;
			$sitename = $app->get('sitename');
			$fromName = $fromUser->get('name');
			$siteURL  = JRoute::link('administrator', 'index.php?option=com_messages&view=message&message_id=' . $table->message_id, false, $linkMode, true);
			$subject  = html_entity_decode($table->subject, ENT_COMPAT, 'UTF-8');
			$message  = strip_tags(html_entity_decode($table->message, ENT_COMPAT, 'UTF-8'));

			$subj	  = sprintf($lang->_('COM_MESSAGES_NEW_MESSAGE'), $fromName, $sitename);
			$msg 	  = $subject . "\n\n" . $message . "\n\n" . sprintf($lang->_('COM_MESSAGES_PLEASE_LOGIN'), $siteURL);

			// Send the email
			$mailer = JFactory::getMailer();

			if (!$mailer->addReplyTo($fromUser->email, $fromUser->name))
			{
				try
				{
					JLog::add(JText::_('COM_MESSAGES_ERROR_COULD_NOT_SEND_INVALID_REPLYTO'), JLog::WARNING, 'jerror');
				}
				catch (RuntimeException $exception)
				{
					JFactory::getApplication()->enqueueMessage(JText::_('COM_MESSAGES_ERROR_COULD_NOT_SEND_INVALID_REPLYTO'), 'warning');
				}

				// The message is still saved in the database, we do not allow this failure to cause the entire save routine to fail
				return true;
			}

			if (!$mailer->addRecipient($toUser->email, $toUser->name))
			{
				try
				{
					JLog::add(JText::_('COM_MESSAGES_ERROR_COULD_NOT_SEND_INVALID_RECIPIENT'), JLog::WARNING, 'jerror');
				}
				catch (RuntimeException $exception)
				{
					JFactory::getApplication()->enqueueMessage(JText::_('COM_MESSAGES_ERROR_COULD_NOT_SEND_INVALID_RECIPIENT'), 'warning');
				}

				// The message is still saved in the database, we do not allow this failure to cause the entire save routine to fail
				return true;
			}

			$mailer->setSubject($subj);
			$mailer->setBody($msg);

			// The Send method will raise an error via JError on a failure, we do not need to check it ourselves here
			$mailer->Send();
		}

		return true;
	}

	/**
	 * Sends a message to the site's super users
	 *
	 * @param   string  $subject  The message subject
	 * @param   string  $message  The message
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function notifySuperUsers($subject, $message, $fromUser = null)
	{
		$db = $this->getDbo();

		try
		{
			/** @var JTableAsset $table */
			$table  = $this->getTable('Asset', 'JTable');
			$rootId = $table->getRootId();

			/** @var JAccessRule[] $rules */
			$rules     = JAccess::getAssetRules($rootId)->getData();
			$rawGroups = $rules['core.admin']->getData();

			if (empty($rawGroups))
			{
				$this->setError(JText::_('COM_MESSAGES_ERROR_MISSING_ROOT_ASSET_GROUPS'));

				return false;
			}

			$groups = array();

			foreach ($rawGroups as $g => $enabled)
			{
				if ($enabled)
				{
					$groups[] = $db->quote($g);
				}
			}

			if (empty($groups))
			{
				$this->setError(JText::_('COM_MESSAGES_ERROR_NO_GROUPS_SET_AS_SUPER_USER'));

				return false;
			}

			$query = $db->getQuery(true)
				->select($db->quoteName('map.user_id'))
				->from($db->quoteName('#__user_usergroup_map', 'map'))
				->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('map.user_id'))
				->where($db->quoteName('map.group_id') . ' IN(' . implode(',', $groups) . ')')
				->where($db->quoteName('u.block') . ' = 0')
				->where($db->quoteName('u.sendEmail') . ' = 1');

			$userIDs = $db->setQuery($query)->loadColumn(0);

			if (empty($userIDs))
			{
				$this->setError(JText::_('COM_MESSAGES_ERROR_NO_USERS_SET_AS_SUPER_USER'));

				return false;
			}

			foreach ($userIDs as $id)
			{
				/*
				 * All messages must have a valid from user, we have use cases where an unauthenticated user may trigger this
				 * so we will set the from user as the to user
				 */
				$data = array(
					'user_id_from' => $id,
					'user_id_to'   => $id,
					'subject'      => $subject,
					'message'      => $message,
				);

				if (!$this->save($data))
				{
					return false;
				}
			}

			return true;
		}
		catch (Exception $exception)
		{
			$this->setError($exception->getMessage());

			return false;
		}
	}
}
