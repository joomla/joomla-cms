<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('PrivacyHelper', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/privacy.php');

/**
 * Export model class.
 *
 * @since  3.9.0
 */
class PrivacyModelExport extends JModelLegacy
{
	/**
	 * Create the export document for an information request.
	 *
	 * @param   integer  $id  The request ID to process
	 *
	 * @return  PrivacyExportDomain[]|boolean  A SimpleXMLElement object for a successful export or boolean false on an error
	 *
	 * @since   3.9.0
	 */
	public function collectDataForExportRequest($id = null)
	{
		$id = !empty($id) ? $id : (int) $this->getState($this->getName() . '.request_id');

		if (!$id)
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_REQUEST_ID_REQUIRED_FOR_EXPORT'));

			return false;
		}

		/** @var PrivacyTableRequest $table */
		$table = $this->getTable();

		if (!$table->load($id))
		{
			$this->setError($table->getError());

			return false;
		}

		if ($table->request_type !== 'export')
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_REQUEST_TYPE_NOT_EXPORT'));

			return false;
		}

		if ($table->status != 1)
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_CANNOT_EXPORT_UNCONFIRMED_REQUEST'));

			return false;
		}

		// If there is a user account associated with the email address, load it here for use in the plugins
		$db = $this->getDbo();

		$userId = (int) $db->setQuery(
			$db->getQuery(true)
				->select('id')
				->from($db->quoteName('#__users'))
				->where($db->quoteName('email') . ' = ' . $db->quote($table->email)),
			0,
			1
		)->loadResult();

		$user = $userId ? JUser::getInstance($userId) : null;

		// Log the export
		$this->logExport($table);

		JPluginHelper::importPlugin('privacy');

		$pluginResults = JFactory::getApplication()->triggerEvent('onPrivacyExportRequest', array($table, $user));

		$domains = array();

		foreach ($pluginResults as $pluginDomains)
		{
			$domains = array_merge($domains, $pluginDomains);
		}

		return $domains;
	}

	/**
	 * Email the data export to the user.
	 *
	 * @param   integer  $id  The request ID to process
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function emailDataExport($id = null)
	{
		$id = !empty($id) ? $id : (int) $this->getState($this->getName() . '.request_id');

		if (!$id)
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_REQUEST_ID_REQUIRED_FOR_EXPORT'));

			return false;
		}

		$exportData = $this->collectDataForExportRequest($id);

		if ($exportData === false)
		{
			// Error is already set, we just need to bail
			return false;
		}

		/** @var PrivacyTableRequest $table */
		$table = $this->getTable();

		if (!$table->load($id))
		{
			$this->setError($table->getError());

			return false;
		}

		if ($table->request_type !== 'export')
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_REQUEST_TYPE_NOT_EXPORT'));

			return false;
		}

		if ($table->status != 1)
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_CANNOT_EXPORT_UNCONFIRMED_REQUEST'));

			return false;
		}

		// Log the email
		$this->logExportEmailed($table);

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

		// The mailer can be set to either throw Exceptions or return boolean false, account for both
		try
		{
			$app = JFactory::getApplication();

			$substitutions = array(
				'[SITENAME]' => $app->get('sitename'),
				'[URL]'      => JUri::root(),
				'\\n'        => "\n",
			);

			$emailSubject = $lang->_('COM_PRIVACY_EMAIL_DATA_EXPORT_COMPLETED_SUBJECT');
			$emailBody    = $lang->_('COM_PRIVACY_EMAIL_DATA_EXPORT_COMPLETED_BODY');

			foreach ($substitutions as $k => $v)
			{
				$emailSubject = str_replace($k, $v, $emailSubject);
				$emailBody    = str_replace($k, $v, $emailBody);
			}

			$mailer = JFactory::getMailer();
			$mailer->setSubject($emailSubject);
			$mailer->setBody($emailBody);
			$mailer->addRecipient($table->email);
			$mailer->addStringAttachment(
				PrivacyHelper::renderDataAsXml($exportData),
				'user-data_' . JUri::getInstance()->toString(array('host')) . '.xml'
			);

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

		return true;
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
	 * Log the data export to the action log system.
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public function logExport(PrivacyTableRequest $request)
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModel');

		$user = JFactory::getUser();

		$message = array(
			'action'      => 'export',
			'id'          => $request->id,
			'itemlink'    => 'index.php?option=com_privacy&view=request&id=' . $request->id,
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		/** @var ActionlogsModelActionlog $model */
		$model = JModelLegacy::getInstance('Actionlog', 'ActionlogsModel');
		$model->addLog(array($message), 'COM_PRIVACY_ACTION_LOG_EXPORT', 'com_privacy.request', $user->id);
	}

	/**
	 * Log the data export email to the action log system.
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public function logExportEmailed(PrivacyTableRequest $request)
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModel');

		$user = JFactory::getUser();

		$message = array(
			'action'      => 'export_emailed',
			'id'          => $request->id,
			'itemlink'    => 'index.php?option=com_privacy&view=request&id=' . $request->id,
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		/** @var ActionlogsModelActionlog $model */
		$model = JModelLegacy::getInstance('Actionlog', 'ActionlogsModel');
		$model->addLog(array($message), 'COM_PRIVACY_ACTION_LOG_EXPORT_EMAILED', 'com_privacy.request', $user->id);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	protected function populateState()
	{
		// Get the pk of the record from the request.
		$this->setState($this->getName() . '.request_id', JFactory::getApplication()->input->getUint('id'));

		// Load the parameters.
		$this->setState('params', JComponentHelper::getParams('com_privacy'));
	}
}
