<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Model for the global configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       3.2
 */
class ConfigModelApplication extends ConfigModelForm
{
	/**
	 * Method to get a form object.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_config.application', 'application', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the configuration data.
	 *
	 * This method will load the global configuration data straight from
	 * JConfig. If configuration data has been saved in the session, that
	 * data will be merged into the original data, overwriting it.
	 *
	 * @return	array  An array containg all global config data.
	 *
	 * @since	1.6
	 */
	public function getData()
	{
		// Get the config data.
		$config	= new JConfig;
		$data	= JArrayHelper::fromObject($config);

		// Prime the asset_id for the rules.
		$data['asset_id'] = 1;

		// Get the text filter data
		$params = JComponentHelper::getParams('com_config');
		$data['filters'] = JArrayHelper::fromObject($params->get('filters'));

		// If no filter data found, get from com_content (update of 1.6/1.7 site)
		if (empty($data['filters']))
		{
			$contentParams = JComponentHelper::getParams('com_content');
			$data['filters'] = JArrayHelper::fromObject($contentParams->get('filters'));
		}

		// Check for data in the session.
		$temp = JFactory::getApplication()->getUserState('com_config.config.global.data');

		// Merge in the session data.
		if (!empty($temp))
		{
			$data = array_merge($data, $temp);
		}

		return $data;
	}

	/**
	 * Method to save the configuration data.
	 *
	 * @param   array  $data  An array containing all global config data.
	 *
	 * @return	boolean  True on success, false on failure.
	 *
	 * @since	1.6
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();

		// Save the rules
		if (isset($data['rules']))
		{
			$rules	= new JAccessRules($data['rules']);

			// Check that we aren't removing our Super User permission
			// Need to get groups from database, since they might have changed
			$myGroups = JAccess::getGroupsByUser(JFactory::getUser()->get('id'));
			$myRules = $rules->getData();
			$hasSuperAdmin = $myRules['core.admin']->allow($myGroups);

			if (!$hasSuperAdmin)
			{
				$app->enqueueMessage(JText::_('COM_CONFIG_ERROR_REMOVING_SUPER_ADMIN'), 'error');

				return false;
			}

			$asset = JTable::getInstance('asset');

			if ($asset->loadByName('root.1'))
			{
				$asset->rules = (string) $rules;

				if (!$asset->check() || !$asset->store())
				{
					$app->enqueueMessage(JText::_('SOME_ERROR_CODE'), 'error');

					return;
				}
			}
			else
			{
				$app->enqueueMessage(JText::_('COM_CONFIG_ERROR_ROOT_ASSET_NOT_FOUND'), 'error');

				return false;
			}

			unset($data['rules']);
		}

		// Save the text filters
		if (isset($data['filters']))
		{
			$registry = new JRegistry;
			$registry->loadArray(array('filters' => $data['filters']));

			$extension = JTable::getInstance('extension');

			// Get extension_id
			$extension_id = $extension->find(array('name' => 'com_config'));

			if ($extension->load((int) $extension_id))
			{
				$extension->params = (string) $registry;

				if (!$extension->check() || !$extension->store())
				{
					$app->enqueueMessage(JText::_('SOME_ERROR_CODE'), 'error');

					return;
				}
			}
			else
			{
				$app->enqueueMessage(JText::_('COM_CONFIG_ERROR_CONFIG_EXTENSION_NOT_FOUND'), 'error');

				return false;
			}

			unset($data['filters']);
		}

		// Get the previous configuration.
		$prev = new JConfig;
		$prev = JArrayHelper::fromObject($prev);

		// Merge the new data in. We do this to preserve values that were not in the form.
		$data = array_merge($prev, $data);

		/*
		 * Perform miscellaneous options based on configuration settings/changes.
		 */

		// Escape the offline message if present.
		if (isset($data['offline_message']))
		{
			$data['offline_message'] = JFilterOutput::ampReplace($data['offline_message']);
		}

		// Purge the database session table if we are changing to the database handler.
		if ($prev['session_handler'] != 'database' && $data['session_handler'] == 'database')
		{
			$table = JTable::getInstance('session');
			$table->purge(-1);
		}

		if (empty($data['cache_handler']))
		{
			$data['caching'] = 0;
		}

		// Give a warning if the cache-folder can not be opened
		$path = JPATH_SITE.'/cache';
		if ($data['caching'] > 0 && $data['cache_handler'] == 'file' && @opendir($path) == false)
		{
			JLog::add(JText::sprintf('COM_CONFIG_ERROR_CACHE_PATH_NOTWRITABLE', $path), JLog::WARNING, 'jerror');
			$data['caching'] = 0;
		}

		// Clean the cache if disabled but previously enabled.
		if (!$data['caching'] && $prev['caching'])
		{
			$cache = JFactory::getCache();
			$cache->clean();
		}

		// Create the new configuration object.
		$config = new JRegistry('config');
		$config->loadArray($data);

		// Overwrite the old FTP credentials with the new ones.
		$temp = JFactory::getConfig();
		$temp->set('ftp_enable', $data['ftp_enable']);
		$temp->set('ftp_host', $data['ftp_host']);
		$temp->set('ftp_port', $data['ftp_port']);
		$temp->set('ftp_user', $data['ftp_user']);
		$temp->set('ftp_pass', $data['ftp_pass']);
		$temp->set('ftp_root', $data['ftp_root']);

		// Clear cache of com_config component.
		$this->cleanCache('_system', 0);
		$this->cleanCache('_system', 1);

		// Write the configuration file.
		return $this->writeConfigFile($config);
	}

	/**
	 * Method to unset the root_user value from configuration data.
	 *
	 * This method will load the global configuration data straight from
	 * JConfig and remove the root_user value for security, then save the configuration.
	 *
	 * @return	boolean  True on success, false on failure.
	 *
	 * @since	1.6
	 */
	public function removeroot()
	{
		// Get the previous configuration.
		$prev = new JConfig;
		$prev = JArrayHelper::fromObject($prev);

		// Create the new configuration object, and unset the root_user property
		$config = new JRegistry('config');
		unset($prev['root_user']);
		$config->loadArray($prev);

		// Write the configuration file.
		return $this->writeConfigFile($config);
	}

	/**
	 * Method to write the configuration to a file.
	 *
	 * @param   JRegistry  $config  A JRegistry object containing all global config data.
	 *
	 * @return	boolean  True on success, false on failure.
	 *
	 * @since	2.5.4
	 * @throws  RuntimeException
	 */
	private function writeConfigFile(JRegistry $config)
	{
		jimport('joomla.filesystem.path');
		jimport('joomla.filesystem.file');

		// Set the configuration file path.
		$file = JPATH_CONFIGURATION . '/configuration.php';

		// Get the new FTP credentials.
		$ftp = JClientHelper::getCredentials('ftp', true);

		$app = JFactory::getApplication();

		// Attempt to make the file writeable if using FTP.
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0644'))
		{
			$app->enqueueMessage(JText::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTWRITABLE'), 'notice');
		}

		// Attempt to write the configuration file as a PHP class named JConfig.
		$configuration = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));

		if (!JFile::write($file, $configuration))
		{
			throw new RuntimeException(JText::_('COM_CONFIG_ERROR_WRITE_FAILED'));
		}

		// Attempt to make the file unwriteable if using FTP.
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0444'))
		{
			$app->enqueueMessage(JText::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTUNWRITABLE'), 'notice');
		}

		return true;
	}
}
