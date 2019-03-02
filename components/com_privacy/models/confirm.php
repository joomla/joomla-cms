<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Request confirmation model class.
 *
 * @since  3.9.0
 */
class PrivacyModelConfirm extends JModelAdmin
{
	/**
	 * Confirms the information request.
	 *
	 * @param   array  $data  The data expected for the form.
	 *
	 * @return  mixed  Exception | JException | boolean
	 *
	 * @since   3.9.0
	 */
	public function confirmRequest($data)
	{
		// Get the form.
		$form = $this->getForm();
		$data['email'] = JStringPunycode::emailToPunycode($data['email']);

		// Check for an error.
		if ($form instanceof Exception)
		{
			return $form;
		}

		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data);

		// Check for an error.
		if ($return instanceof Exception)
		{
			return $return;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $formError)
			{
				$this->setError($formError->getMessage());
			}

			return false;
		}

		// Search for the information request
		/** @var PrivacyTableRequest $table */
		$table = $this->getTable();

		if (!$table->load(array('email' => $data['email'], 'status' => 0)))
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_NO_PENDING_REQUESTS'));

			return false;
		}

		// A request can only be confirmed if it is in a pending status and has a confirmation token
		if ($table->status != '0' || !$table->confirm_token)
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_NO_PENDING_REQUESTS'));

			return false;
		}

		// A request can only be confirmed if the token is less than 24 hours old
		$confirmTokenCreatedAt = new JDate($table->confirm_token_created_at);
		$confirmTokenCreatedAt->add(new DateInterval('P1D'));

		$now = new JDate('now');

		if ($now > $confirmTokenCreatedAt)
		{
			// Invalidate the request
			$table->status = -1;
			$table->confirm_token = '';

			try
			{
				$table->store();
			}
			catch (JDatabaseException $exception)
			{
				// The error will be logged in the database API, we just need to catch it here to not let things fatal out
			}

			$this->setError(JText::_('COM_PRIVACY_ERROR_CONFIRM_TOKEN_EXPIRED'));

			return false;
		}

		// Verify the token
		if (!JUserHelper::verifyPassword($data['confirm_token'], $table->confirm_token))
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_NO_PENDING_REQUESTS'));

			return false;
		}

		// Everything is good to go, transition the request to confirmed
		$saved = $this->save(
			array(
				'id'     => $table->id,
				'status' => 1,
				'confirm_token' => '',
			)
		);

		if (!$saved)
		{
			// Error was set by the save method
			return false;
		}

		// Push a notification to the site's super users, deliberately ignoring if this process fails so the below message goes out
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_messages/models', 'MessagesModel');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_messages/tables');

		/** @var MessagesModelMessage $messageModel */
		$messageModel = JModelLegacy::getInstance('Message', 'MessagesModel');

		$messageModel->notifySuperUsers(
			JText::_('COM_PRIVACY_ADMIN_NOTIFICATION_USER_CONFIRMED_REQUEST_SUBJECT'),
			JText::sprintf('COM_PRIVACY_ADMIN_NOTIFICATION_USER_CONFIRMED_REQUEST_MESSAGE', $table->email)
		);

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModel');

		$message = array(
			'action'       => 'request-confirmed',
			'subjectemail' => $table->email,
			'id'           => $table->id,
			'itemlink'     => 'index.php?option=com_privacy&view=request&id=' . $table->id,
		);

		/** @var ActionlogsModelActionlog $model */
		$model = JModelLegacy::getInstance('Actionlog', 'ActionlogsModel');
		$model->addLog(array($message), 'COM_PRIVACY_ACTION_LOG_CONFIRMED_REQUEST', 'com_privacy.request');

		return true;
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
		$form = $this->loadForm('com_privacy.confirm', 'confirm', array('control' => 'jform'));

		if (empty($form))
		{
			return false;
		}

		$input = JFactory::getApplication()->input;

		if ($input->getMethod() === 'GET')
		{
			$form->setValue('confirm_token', '', $input->get->getAlnum('confirm_token'));
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
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	protected function populateState()
	{
		// Get the application object.
		$params = JFactory::getApplication()->getParams('com_privacy');

		// Load the parameters.
		$this->setState('params', $params);
	}
}
