<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Request item model class.
 *
 * @since  3.9.0
 */
class PrivacyModelRequest extends JModelAdmin
{
	/**
	 * Clean the cache
	 *
	 * @param   string   $group      The cache group
	 * @param   integer  $client_id  The ID of the client
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	protected function cleanCache($group = 'com_privacy', $client_id = 1)
	{
		parent::cleanCache('com_privacy', 1);
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm|boolean  A JForm object on success, false on failure
	 *
	 * @since   3.9.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_privacy.request', 'request', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   3.9.0
	 * @throws  \Exception
	 */
	public function getTable($name = 'Request', $prefix = 'PrivacyTable', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array  The default data is an empty array.
	 *
	 * @since   3.9.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_privacy.edit.request.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Log the completion of a request to the action log system.
	 *
	 * @param   integer  $id  The ID of the request to process.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function logRequestCompleted($id)
	{
		/** @var PrivacyTableRequest $table */
		$table = $this->getTable();

		if (!$table->load($id))
		{
			$this->setError($table->getError());

			return false;
		}

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModel');

		$user = JFactory::getUser();

		$message = array(
			'action'       => 'request-completed',
			'requesttype'  => $table->request_type,
			'subjectemail' => $table->email,
			'id'           => $table->id,
			'itemlink'     => 'index.php?option=com_privacy&view=request&id=' . $table->id,
			'userid'       => $user->id,
			'username'     => $user->username,
			'accountlink'  => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		/** @var ActionlogsModelActionlog $model */
		$model = JModelLegacy::getInstance('Actionlog', 'ActionlogsModel');
		$model->addLog(array($message), 'COM_PRIVACY_ACTION_LOG_ADMIN_COMPLETED_REQUEST', 'com_privacy.request', $user->id);

		return true;
	}

	/**
	 * Log the creation of a request to the action log system.
	 *
	 * @param   integer  $id  The ID of the request to process.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function logRequestCreated($id)
	{
		/** @var PrivacyTableRequest $table */
		$table = $this->getTable();

		if (!$table->load($id))
		{
			$this->setError($table->getError());

			return false;
		}

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModel');

		$user = JFactory::getUser();

		$message = array(
			'action'       => 'request-created',
			'requesttype'  => $table->request_type,
			'subjectemail' => $table->email,
			'id'           => $table->id,
			'itemlink'     => 'index.php?option=com_privacy&view=request&id=' . $table->id,
			'userid'       => $user->id,
			'username'     => $user->username,
			'accountlink'  => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		/** @var ActionlogsModelActionlog $model */
		$model = JModelLegacy::getInstance('Actionlog', 'ActionlogsModel');
		$model->addLog(array($message), 'COM_PRIVACY_ACTION_LOG_ADMIN_CREATED_REQUEST', 'com_privacy.request', $user->id);

		return true;
	}

	/**
	 * Log the invalidation of a request to the action log system.
	 *
	 * @param   integer  $id  The ID of the request to process.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function logRequestInvalidated($id)
	{
		/** @var PrivacyTableRequest $table */
		$table = $this->getTable();

		if (!$table->load($id))
		{
			$this->setError($table->getError());

			return false;
		}

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModel');

		$user = JFactory::getUser();

		$message = array(
			'action'       => 'request-invalidated',
			'requesttype'  => $table->request_type,
			'subjectemail' => $table->email,
			'id'           => $table->id,
			'itemlink'     => 'index.php?option=com_privacy&view=request&id=' . $table->id,
			'userid'       => $user->id,
			'username'     => $user->username,
			'accountlink'  => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		/** @var ActionlogsModelActionlog $model */
		$model = JModelLegacy::getInstance('Actionlog', 'ActionlogsModel');
		$model->addLog(array($message), 'COM_PRIVACY_ACTION_LOG_ADMIN_INVALIDATED_REQUEST', 'com_privacy.request', $user->id);

		return true;
	}

	/**
	 * Notifies the user that an information request has been created by a site administrator.
	 *
	 * Because confirmation tokens are stored in the database as a hashed value, this method will generate a new confirmation token
	 * for the request.
	 *
	 * @param   integer  $id  The ID of the request to process.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function notifyUserAdminCreatedRequest($id)
	{
		/** @var PrivacyTableRequest $table */
		$table = $this->getTable();

		if (!$table->load($id))
		{
			$this->setError($table->getError());

			return false;
		}

		/*
		 * If there is an associated user account, we will attempt to send this email in the user's preferred language.
		 * Because of this, it is expected that Language::_() is directly called and that the Text class is NOT used
		 * for translating all messages.
		 *
		 * Error messages will still be displayed to the administrator, so those messages should continue to use the Text class.
		 */

		$lang = JFactory::getLanguage();

		$db = $this->getDbo();

		$userId = (int) $db->setQuery(
			$db->getQuery(true)
				->select('id')
				->from($db->quoteName('#__users'))
				->where($db->quoteName('email') . ' = ' . $db->quote($table->email)),
			0,
			1
		)->loadResult();

		if ($userId)
		{
			$receiver = JUser::getInstance($userId);

			/*
			 * We don't know if the user has admin access, so we will check if they have an admin language in their parameters,
			 * falling back to the site language, falling back to the currently active language
			 */

			$langCode = $receiver->getParam('admin_language', '');

			if (!$langCode)
			{
				$langCode = $receiver->getParam('language', $lang->getTag());
			}

			$lang = JLanguage::getInstance($langCode, $lang->getDebug());
		}

		// Ensure the right language files have been loaded
		$lang->load('com_privacy', JPATH_ADMINISTRATOR, null, false, true)
			|| $lang->load('com_privacy', JPATH_ADMINISTRATOR . '/components/com_privacy', null, false, true);

		// Regenerate the confirmation token
		$token       = JApplicationHelper::getHash(JUserHelper::genRandomPassword());
		$hashedToken = JUserHelper::hashPassword($token);

		$table->confirm_token            = $hashedToken;
		$table->confirm_token_created_at = JFactory::getDate()->toSql();

		try
		{
			$table->store();
		}
		catch (JDatabaseException $exception)
		{
			$this->setError($exception->getMessage());

			return false;
		}

		// The mailer can be set to either throw Exceptions or return boolean false, account for both
		try
		{
			$app = JFactory::getApplication();

			$linkMode = $app->get('force_ssl', 0) == 2 ? 1 : -1;

			$substitutions = array(
				'[SITENAME]' => $app->get('sitename'),
				'[URL]'      => JUri::root(),
				'[TOKENURL]' => JRoute::link('site', 'index.php?option=com_privacy&view=confirm&confirm_token=' . $token, false, $linkMode),
				'[FORMURL]'  => JRoute::link('site', 'index.php?option=com_privacy&view=confirm', false, $linkMode),
				'[TOKEN]'    => $token,
				'\\n'        => "\n",
			);

			switch ($table->request_type)
			{
				case 'export':
					$emailSubject = $lang->_('COM_PRIVACY_EMAIL_ADMIN_REQUEST_SUBJECT_EXPORT_REQUEST');
					$emailBody    = $lang->_('COM_PRIVACY_EMAIL_ADMIN_REQUEST_BODY_EXPORT_REQUEST');

					break;

				case 'remove':
					$emailSubject = $lang->_('COM_PRIVACY_EMAIL_ADMIN_REQUEST_SUBJECT_REMOVE_REQUEST');
					$emailBody    = $lang->_('COM_PRIVACY_EMAIL_ADMIN_REQUEST_BODY_REMOVE_REQUEST');

					break;

				default:
					$this->setError(JText::_('COM_PRIVACY_ERROR_UNKNOWN_REQUEST_TYPE'));

					return false;
			}

			foreach ($substitutions as $k => $v)
			{
				$emailSubject = str_replace($k, $v, $emailSubject);
				$emailBody    = str_replace($k, $v, $emailBody);
			}

			$mailer = JFactory::getMailer();
			$mailer->setSubject($emailSubject);
			$mailer->setBody($emailBody);
			$mailer->addRecipient($table->email);

			$mailResult = $mailer->Send();

			if ($mailResult instanceof JException)
			{
				// JError was already called so we just need to return now
				return false;
			}
			elseif ($mailResult === false)
			{
				$this->setError($mailer->ErrorInfo);

				return false;
			}

			return true;
		}
		catch (phpmailerException $exception)
		{
			$this->setError($exception->getMessage());

			return false;
		}
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   3.9.0
	 */
	public function save($data)
	{
		$table = $this->getTable();
		$key   = $table->getKeyName();
		$pk    = !empty($data[$key]) ? $data[$key] : (int) $this->getState($this->getName() . '.id');

		if (!$pk && !JFactory::getConfig()->get('mailonline', 1))
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_CANNOT_CREATE_REQUEST_WHEN_SENDMAIL_DISABLED'));

			return false;
		}

		return parent::save($data);
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   3.9.0
	 */
	public function validate($form, $data, $group = null)
	{
		$validatedData = parent::validate($form, $data, $group);

		// If parent validation failed there's no point in doing our extended validation
		if ($validatedData === false)
		{
			return false;
		}

		// The user cannot create a request for their own account
		if (strtolower(JFactory::getUser()->email) === strtolower($validatedData['email']))
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_CANNOT_CREATE_REQUEST_FOR_SELF'));

			return false;
		}

		// Check for an active request for this email address
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('COUNT(id)')
			->from('#__privacy_requests')
			->where('email = ' . $db->quote($validatedData['email']))
			->where('request_type = ' . $db->quote($validatedData['request_type']))
			->where('status IN (0, 1)');

		$activeRequestCount = (int) $db->setQuery($query)->loadResult();

		if ($activeRequestCount > 0)
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_ACTIVE_REQUEST_FOR_EMAIL'));

			return false;
		}

		return $validatedData;
	}
}
