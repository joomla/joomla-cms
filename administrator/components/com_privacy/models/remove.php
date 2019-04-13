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
JLoader::register('PrivacyRemovalStatus', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/removal/status.php');

/**
 * Remove model class.
 *
 * @since  3.9.0
 */
class PrivacyModelRemove extends JModelLegacy
{
	/**
	 * Remove the user data.
	 *
	 * @param   integer  $id  The request ID to process
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function removeDataForRequest($id = null)
	{
		$id = !empty($id) ? $id : (int) $this->getState($this->getName() . '.request_id');

		if (!$id)
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_REQUEST_ID_REQUIRED_FOR_REMOVE'));

			return false;
		}

		/** @var PrivacyTableRequest $table */
		$table = $this->getTable();

		if (!$table->load($id))
		{
			$this->setError($table->getError());

			return false;
		}

		if ($table->request_type !== 'remove')
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_REQUEST_TYPE_NOT_REMOVE'));

			return false;
		}

		if ($table->status != 1)
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_CANNOT_REMOVE_UNCONFIRMED_REQUEST'));

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

		$canRemove = true;

		JPluginHelper::importPlugin('privacy');

		/** @var PrivacyRemovalStatus[] $pluginResults */
		$pluginResults = JFactory::getApplication()->triggerEvent('onPrivacyCanRemoveData', array($table, $user));

		foreach ($pluginResults as $status)
		{
			if (!$status->canRemove)
			{
				$this->setError($status->reason ?: JText::_('COM_PRIVACY_ERROR_CANNOT_REMOVE_DATA'));

				$canRemove = false;
			}
		}

		if (!$canRemove)
		{
			$this->logRemoveBlocked($table, $this->getErrors());

			return false;
		}

		// Log the removal
		$this->logRemove($table);

		JFactory::getApplication()->triggerEvent('onPrivacyRemoveData', array($table, $user));

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
	 * Log the data removal to the action log system.
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public function logRemove(PrivacyTableRequest $request)
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModel');

		$user = JFactory::getUser();

		$message = array(
			'action'      => 'remove',
			'id'          => $request->id,
			'itemlink'    => 'index.php?option=com_privacy&view=request&id=' . $request->id,
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		);

		/** @var ActionlogsModelActionlog $model */
		$model = JModelLegacy::getInstance('Actionlog', 'ActionlogsModel');
		$model->addLog(array($message), 'COM_PRIVACY_ACTION_LOG_REMOVE', 'com_privacy.request', $user->id);
	}

	/**
	 * Log the data removal being blocked to the action log system.
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   string[]             $reasons  The reasons given why the record could not be removed.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public function logRemoveBlocked(PrivacyTableRequest $request, array $reasons)
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModel');

		$user = JFactory::getUser();

		$message = array(
			'action'      => 'remove-blocked',
			'id'          => $request->id,
			'itemlink'    => 'index.php?option=com_privacy&view=request&id=' . $request->id,
			'userid'      => $user->id,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
			'reasons'     => implode('; ', $reasons),
		);

		/** @var ActionlogsModelActionlog $model */
		$model = JModelLegacy::getInstance('Actionlog', 'ActionlogsModel');
		$model->addLog(array($message), 'COM_PRIVACY_ACTION_LOG_REMOVE_BLOCKED', 'com_privacy.request', $user->id);
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
