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
 * Remove model class.
 *
 * @since  __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function userDataRemove($id = null)
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

		// Log the remove
		$this->logRemove($table);

		if ($table->user_id)
		{
			JPluginHelper::importPlugin('privacy');
			JFactory::getApplication()->triggerEvent('onPrivacyBeforeRemoveRequest', array($table));

			$userToRemove = JUser::getInstance($table->user_id);
			
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__users'))
				->set($db->quoteName('name') . ' = ' . $db->quote(''))
				->set($db->quoteName('username') . ' = ' . $db->quote(microtime()))
				->set($db->quoteName('email') . ' = ' . $db->quote(microtime()))
				->set($db->quoteName('block') . ' = 1')
				->where($db->quoteName('id') . ' = ' . (int) $userToRemove->id);

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (JDatabaseExceptionExecuting $e)
			{
				$this->setError(JText::_('COM_PRIVACY_ERROR_CANNOT_REMOVE_REQUEST'));
				return false;
			}

			JFactory::getApplication()->triggerEvent('onPrivacyAfterRemoveRequest', array($table));
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
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function getTable($name = 'Request', $prefix = 'PrivacyTable', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Log the data remove to the action log system.
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
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
		$model->addLogsToDb(array($message), 'COM_PRIVACY_ACTION_LOG_EXPORT', 'com_privacy.request', $user->id);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function populateState()
	{
		// Get the pk of the record from the request.
		$this->setState($this->getName() . '.request_id', JFactory::getApplication()->input->getUint('id'));

		// Load the parameters.
		$this->setState('params', JComponentHelper::getParams('com_privacy'));
	}
}
