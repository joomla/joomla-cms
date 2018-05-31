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
 * Export model class.
 *
 * @since  __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
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

		// Log the export
		$this->logExport($table);

		JPluginHelper::importPlugin('privacy');

		$pluginResults = JFactory::getApplication()->triggerEvent('onPrivacyExportRequest', array($table));

		$domains = array();

		foreach ($pluginResults as $pluginDomains)
		{
			$domains += $pluginDomains;
		}

		return $domains;
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

	/**
	 * Log the data export to the action log system.
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function logExport(PrivacyTableRequest $request)
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
		$model->addLogsToDb(array($message), 'COM_PRIVACY_ACTION_LOG_EXPORT', 'com_privacy.request', $user->id);
	}
}
