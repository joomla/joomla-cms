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
		// Load the parameters.
		$this->setState('params', JComponentHelper::getParams('com_privacy'));
	}
}
