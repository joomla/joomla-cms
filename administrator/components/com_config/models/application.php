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
 * Model for the global configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @deprecated  3.2
 */
class ConfigModelApplication extends JModelForm
{
	/**
	 * Method to get a form object.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   1.6
	 * @deprecated  3.2  Use ConfigModelApplication instead.
	 */
	public function getForm($data = array(), $loadData = true)
	{

		JLog::add('models/ConfigModelApplication is deprecated. Use model/ConfigModelApplication instead.', JLog::WARNING, 'deprecated');
		JLoader::registerPrefix('Config', JPATH_ADMINISTRATOR . '/components/com_config/model');
		$model = new ConfigModelApplication;

		return $model->getForm($data, $loadData);
	}

	/**
	 * Method to get the configuration data.
	 *
	 * This method will load the global configuration data straight from
	 * JConfig. If configuration data has been saved in the session, that
	 * data will be merged into the original data, overwriting it.
	 *
	 * @return  array  An array containg all global config data.
	 *
	 * @since   1.6
	 * @deprecated  3.2  Use ConfigModelApplication instead.
	 */
	public function getData()
	{

		JLog::add('models/ConfigModelApplication is deprecated. Use model/ConfigModelApplication instead.', JLog::WARNING, 'deprecated');
		JLoader::registerPrefix('Config', JPATH_ADMINISTRATOR . '/components/com_config/model');
		$model = new ConfigModelApplication;

		return $model->getData();
	}

	/**
	 * Method to save the configuration data.
	 *
	 * @param   array  $data  An array containing all global config data.
	 *
	 * @return  bool   True on success, false on failure.
	 *
	 * @since   1.6
	 * @deprecated  3.2  Use ConfigModelApplication instead.
	 */
	public function save($data)
	{

		JLog::add('models/ConfigModelApplication is deprecated. Use model/ConfigModelApplication instead.', JLog::WARNING, 'deprecated');
		JLoader::registerPrefix('Config', JPATH_ADMINISTRATOR . '/components/com_config/model');
		$model = new ConfigModelApplication;

		return $model->save($data);
	}

	/**
	 * Method to unset the root_user value from configuration data.
	 *
	 * This method will load the global configuration data straight from
	 * JConfig and remove the root_user value for security, then save the configuration.
	 *
	 * @since   1.6
	 * @deprecated  3.2  Use ConfigModelApplication instead.
	 */
	public function removeroot()
	{

		JLog::add('models/ConfigModelApplication is deprecated. Use model/ConfigModelApplication instead.', JLog::WARNING, 'deprecated');
		JLoader::registerPrefix('Config', JPATH_ADMINISTRATOR . '/components/com_config/model');
		$model = new ConfigModelApplication;

		return $model->removeroot();
	}

	/**
	 * Method to write the configuration to a file.
	 *
	 * @param   JRegistry  $config  A JRegistry object containing all global config data.
	 *
	 * @return  bool       True on success, false on failure.
	 *
	 * @since   2.5.4
	 * @deprecated  3.2  Use ConfigModelApplication instead.
	 */
	private function writeConfigFile(JRegistry $config)
	{
		JLog::add('models/ConfigModelApplication is deprecated. Use model/ConfigModelApplication instead.', JLog::WARNING, 'deprecated');
	}
}
