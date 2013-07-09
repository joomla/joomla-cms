<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Model for component configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       1.5
 * @deprecated  3.2
 */
class ConfigModelComponent extends JModelForm
{
	/**
	 * The event to trigger before saving the data.
	 *
	 * @var    string
	 * @since  3.1.0
	 * @deprecated  3.2
	 */
	protected $event_before_save = 'onConfigurationBeforeSave';

	/**
	 * The event to trigger before deleting the data.
	 *
	 * @var    string
	 * @since  3.1.0
	 * @deprecated  3.2
	 */
	protected $event_after_save = 'onConfigurationAfterSave';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @deprecated  3.2
	 */
	protected function populateState()
	{

		JLog::add('models/ConfigModelComponent is deprecated. Use model/ConfigModelComponent instead.', JLog::WARNING, 'deprecated');
		JLoader::registerPrefix('Config', JPATH_ADMINISTRATOR . '/components/com_config/model');
		$model = new ConfigModelComponent;

		$model->populateState();
	}

	/**
	 * Method to get a form object.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   1.6
	 * @deprecated  3.2
	 */
	public function getForm($data = array(), $loadData = true)
	{

		JLog::add('models/ConfigModelComponent is deprecated. Use model/ConfigModelComponent instead.', JLog::WARNING, 'deprecated');
		JLoader::registerPrefix('Config', JPATH_ADMINISTRATOR . '/components/com_config/model');
		$model = new ConfigModelComponent;

		$model->getForm($data, $loadData);
	}

	/**
	 * Get the component information.
	 *
	 * @return  object
	 *
	 * @since   1.6
	 * @deprecated  3.2
	 */
	function getComponent()
	{

		JLog::add('models/ConfigModelComponent is deprecated. Use model/ConfigModelComponent instead.', JLog::WARNING, 'deprecated');
		JLoader::registerPrefix('Config', JPATH_ADMINISTRATOR . '/components/com_config/model');
		$model = new ConfigModelComponent;

		return $model->getComponent();
	}

	/**
	 * Method to save the configuration data.
	 *
	 * @param   array  $data  An array containing all global config data.
	 *
	 * @return  bool   True on success, false on failure.
	 *
	 * @since   1.6
	 * @deprecated  3.2
	 */
	public function save($data)
	{

		JLog::add('models/ConfigModelComponent is deprecated. Use model/ConfigModelComponent instead.', JLog::WARNING, 'deprecated');
		JLoader::registerPrefix('Config', JPATH_ADMINISTRATOR . '/components/com_config/model');
		$model = new ConfigModelComponent;

		return $model->save($data);
	}
}
