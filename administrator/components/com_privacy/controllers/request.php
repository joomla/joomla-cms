<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Request management controller class.
 *
 * @since  3.9.0
 */
class PrivacyControllerRequest extends JControllerForm
{
	/**
	 * Method to complete a request.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function complete($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		/** @var PrivacyModelRequest $model */
		$model = $this->getModel();

		/** @var PrivacyTableRequest $table */
		$table = $model->getTable();

		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		$recordId = $this->input->getInt($urlVar);

		$item = $model->getItem($recordId);

		// Ensure this record can transition to the requested state
		if (!$this->canTransition($item, '2'))
		{
			$this->setError(\JText::_('COM_PRIVACY_ERROR_COMPLETE_TRANSITION_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				\JRoute::_(
					'index.php?option=com_privacy&view=request&id=' . $recordId, false
				)
			);

			return false;
		}

		// Build the data array for the update
		$data = array(
			$key     => $recordId,
			'status' => '2',
		);

		// Access check.
		if (!$this->allowSave($data, $key))
		{
			$this->setError(\JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				\JRoute::_(
					'index.php?option=com_privacy&view=request&id=' . $recordId, false
				)
			);

			return false;
		}

		// Attempt to save the data.
		if (!$model->save($data))
		{
			// Redirect back to the edit screen.
			$this->setError(\JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				\JRoute::_(
					'index.php?option=com_privacy&view=request&id=' . $recordId, false
				)
			);

			return false;
		}

		// Log the request completed
		$model->logRequestCompleted($recordId);

		$this->setMessage(\JText::_('COM_PRIVACY_REQUEST_COMPLETED'));

		$url = 'index.php?option=com_privacy&view=requests';

		// Check if there is a return value
		$return = $this->input->get('return', null, 'base64');

		if (!is_null($return) && \JUri::isInternal(base64_decode($return)))
		{
			$url = base64_decode($return);
		}

		// Redirect to the list screen.
		$this->setRedirect(\JRoute::_($url, false));

		return true;
	}

	/**
	 * Method to email the data export for a request.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function emailexport()
	{
		/** @var PrivacyModelExport $model */
		$model = $this->getModel('Export');

		$recordId = $this->input->getUint('id');

		if (!$model->emailDataExport($recordId))
		{
			// Redirect back to the edit screen.
			$this->setError(\JText::sprintf('COM_PRIVACY_ERROR_EXPORT_EMAIL_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
		}
		else
		{
			$this->setMessage(\JText::_('COM_PRIVACY_EXPORT_EMAILED'));
		}

		$url = 'index.php?option=com_privacy&view=requests';

		// Check if there is a return value
		$return = $this->input->get('return', null, 'base64');

		if (!is_null($return) && \JUri::isInternal(base64_decode($return)))
		{
			$url = base64_decode($return);
		}

		// Redirect to the list screen.
		$this->setRedirect(\JRoute::_($url, false));

		return true;
	}

	/**
	 * Method to invalidate a request.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function invalidate($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		/** @var PrivacyModelRequest $model */
		$model = $this->getModel();

		/** @var PrivacyTableRequest $table */
		$table = $model->getTable();

		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		$recordId = $this->input->getInt($urlVar);

		$item = $model->getItem($recordId);

		// Ensure this record can transition to the requested state
		if (!$this->canTransition($item, '-1'))
		{
			$this->setError(\JText::_('COM_PRIVACY_ERROR_INVALID_TRANSITION_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				\JRoute::_(
					'index.php?option=com_privacy&view=request&id=' . $recordId, false
				)
			);

			return false;
		}

		// Build the data array for the update
		$data = array(
			$key     => $recordId,
			'status' => '-1',
		);

		// Access check.
		if (!$this->allowSave($data, $key))
		{
			$this->setError(\JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				\JRoute::_(
					'index.php?option=com_privacy&view=request&id=' . $recordId, false
				)
			);

			return false;
		}

		// Attempt to save the data.
		if (!$model->save($data))
		{
			// Redirect back to the edit screen.
			$this->setError(\JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				\JRoute::_(
					'index.php?option=com_privacy&view=request&id=' . $recordId, false
				)
			);

			return false;
		}

		// Log the request invalidated
		$model->logRequestInvalidated($recordId);

		$this->setMessage(\JText::_('COM_PRIVACY_REQUEST_INVALIDATED'));

		$url = 'index.php?option=com_privacy&view=requests';

		// Check if there is a return value
		$return = $this->input->get('return', null, 'base64');

		if (!is_null($return) && \JUri::isInternal(base64_decode($return)))
		{
			$url = base64_decode($return);
		}

		// Redirect to the list screen.
		$this->setRedirect(\JRoute::_($url, false));

		return true;
	}

	/**
	 * Method to remove the user data for a privacy remove request.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function remove()
	{
		/** @var PrivacyModelRemove $model */
		$model = $this->getModel('Remove');

		$recordId = $this->input->getUint('id');

		if (!$model->removeDataForRequest($recordId))
		{
			// Redirect back to the edit screen.
			$this->setError(\JText::sprintf('COM_PRIVACY_ERROR_REMOVE_DATA_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				\JRoute::_(
					'index.php?option=com_privacy&view=request&id=' . $recordId, false
				)
			);

			return false;
		}

		$this->setMessage(\JText::_('COM_PRIVACY_DATA_REMOVED'));

		$url = 'index.php?option=com_privacy&view=requests';

		// Check if there is a return value
		$return = $this->input->get('return', null, 'base64');

		if (!is_null($return) && \JUri::isInternal(base64_decode($return)))
		{
			$url = base64_decode($return);
		}

		// Redirect to the list screen.
		$this->setRedirect(\JRoute::_($url, false));

		return true;
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   \JModelLegacy  $model      The data model object.
	 * @param   array          $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	protected function postSaveHook(\JModelLegacy $model, $validData = array())
	{
		// This hook only processes new items
		if (!$model->getState($model->getName() . '.new', false))
		{
			return;
		}

		if (!$model->logRequestCreated($model->getState($model->getName() . '.id')))
		{
			if ($error = $model->getError())
			{
				JFactory::getApplication()->enqueueMessage($error, 'warning');
			}
		}

		if (!$model->notifyUserAdminCreatedRequest($model->getState($model->getName() . '.id')))
		{
			if ($error = $model->getError())
			{
				JFactory::getApplication()->enqueueMessage($error, 'warning');
			}
		}
		else
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_PRIVACY_MSG_CONFIRM_EMAIL_SENT_TO_USER'));
		}
	}

	/**
	 * Method to determine if an item can transition to the specified status.
	 *
	 * @param   object  $item       The item being updated.
	 * @param   string  $newStatus  The new status of the item.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	private function canTransition($item, $newStatus)
	{
		switch ($item->status)
		{
			case '0':
				// A pending item can only move to invalid through this controller due to the requirement for a user to confirm the request
				return $newStatus === '-1';

			case '1':
				// A confirmed item can be marked completed or invalid
				return in_array($newStatus, array('-1', '2'), true);

			// An item which is already in an invalid or complete state cannot transition, likewise if we don't know the state don't change anything
			case '-1':
			case '2':
			default:
				return false;
		}
	}
}
