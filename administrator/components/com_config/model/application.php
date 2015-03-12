<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class ConfigModelApplication extends ConfigModelConfig
{
	public function getItem($pk = null, $class = 'JRegistry')
	{
		// Get the config data.
		$config	= new JConfig;
		$data	= JArrayHelper::fromObject($config);

		// Prime the asset_id for the rules.
		$data['asset_id'] = 1;

		// Get the text filter data
		$params = JComponentHelper::getParams('com_config');
		$data['filters'] = JArrayHelper::fromObject($params->get('filters'));

		// Check for data in the session.
		$temp = JFactory::getApplication()->getUserState('com_config.config.global.data');

		// Merge in the session data.
		if (!empty($temp))
		{
			$data = array_merge($data, $temp);
		}

		$item = new $class($data);

		return $item;
	}

	public function getKeyName($tablePrefix = null, $tableName = null, $config = array())
	{
		return;
	}

	/**
	 * Method to update the application configuration
	 * @param array $data        from the form
	 *
	 * @return boolean
	 * @throws ErrorException
	 */
	public function update($data)
	{
		if(isset($data['rules']))
		{
			$this->updatePermissions($data['rules']);
			unset($data['rules']);
		}

		if(isset($data['filters']))
		{
			$this->updateFilters($data['filters']);
			unset($data['filters']);
		}

		// Get the old configuration and merge with new data.
		// We do this to preserve values that were not in the form.
		$oldConfig = new JConfig();
		$oldConfig = JArrayHelper::fromObject($oldConfig);
		$data = array_merge($oldConfig, $data);

		// Escape the offline message if present.
		if (isset($data['offline_message']))
		{
			$data['offline_message'] = JFilterOutput::ampReplace($data['offline_message']);
		}

		$hasNewHandler = ($oldConfig['session_handler'] != 'database' && $data['session_handler'] == 'database');
		if($hasNewHandler)
		{
			$this->purgeSession();
		}

		$data['caching'] = $this->getCaching($oldConfig['caching'], $data['caching'],$data['cache_handler']);

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

		$this->updateConfigFile($config);

		return true;
	}

	/**
	 * Method to update the root ACL permissions
	 *
	 * @param array $rules
	 *
	 * @throws ErrorException
	 */
	protected function updatePermissions($rules)
	{
		$rules = new JAccessRules($rules);

		// Check that we aren't removing Super User permission
		// Need to get groups from database, since they might have changed
		$myGroups = JAccess::getGroupsByUser(JFactory::getUser()->id);
		$myRules = $rules->getData();
		$isSuperAdmin = $myRules['core.admin']->allow($myGroups);

		if(!$isSuperAdmin)
		{
			throw new ErrorException(JText::_('COM_CONFIG_ERROR_REMOVING_SUPER_ADMIN'));
		}

		$asset = JTable::getInstance('asset');

		// Ideally the assets table would throw these two exceptions and the model would just use the interface
		// As is the model knows too much about the table here.
		if(!$asset->loadByName('root.1'))
		{
			throw new ErrorException(JText::_('COM_CONFIG_ERROR_ROOT_ASSET_NOT_FOUND'));
		}

		$asset->rules = (string) $rules;

		if(!$asset->check() || !$asset->store())
		{
			throw new ErrorException(JText::_('ERROR_STORING_PERMISSIONS'));
		}
	}

	/**
	 * Method to update the application filter settings
	 *
	 * @param $filters array
	 *
	 * @throws ErrorException
	 */
	protected function updateFilters($filters)
	{
		$registry = new JRegistry();
		$registry->loadArray(array('filters' => $filters));

		$extension = JTable::getInstance('extension');

		// Get extension_id
		$extension_id = $extension->find(array('name' => 'com_config'));

		// See my comment about the assets table. Same applies here. The extension table should be throwing these exceptions
		if(!$extension->load((int) $extension_id))
		{
			throw new ErrorException(JText::_('COM_CONFIG_ERROR_CONFIG_EXTENSION_NOT_FOUND'));
		}

		$extension->params = (string) $registry;

		if (!$extension->check() || !$extension->store())
		{
			throw new ErrorException(JText::_('COM_CONFIG_ERROR_UPDATING_FILTERS'));
		}
	}

	/**
	 * Method to purge the session table
	 */
	protected function purgeSession()
	{
		$table = JTable::getInstance('session');
		$table->purge(-1);
	}

	/**
	 * Method to determine the caching value
	 *
	 * @param int     $oldValue
	 * @param int     $newValue
	 * @param string  $handler
	 *
	 * @return int
	 */
	protected function getCaching($oldValue, $newValue, $handler)
	{
		if (empty($handler))
		{
			$newValue = 0;
		}

		// Give a warning if the cache-folder can not be opened
		$path = JPATH_SITE.'/cache';
		if ($newValue > 0 && $handler == 'file' && @opendir($path) == false)
		{
			JLog::add(JText::sprintf('COM_CONFIG_ERROR_CACHE_PATH_NOTWRITABLE', $path), JLog::WARNING, 'jerror');
			$newValue = 0;
		}

		// Clean the cache previously enabled, but now disabled.
		if ($oldValue != 0 && $newValue == 0)
		{
			$cache = JFactory::getCache();
			$cache->clean();
		}

		return $newValue;
	}

	/**
	 * Method to update configuration.php file
	 *
	 * @param JRegistry $config
	 *
	 * @return bool
	 * @throws Exception
	 */
	protected function updateConfigFile(JRegistry $config)
	{
		jimport('joomla.filesystem.path');
		jimport('joomla.filesystem.file');

		// Set the configuration file path.
		$file = JPATH_CONFIGURATION . '/configuration.php';

		// Get the new FTP credentials.
		$ftp = JClientHelper::getCredentials('ftp', true);

		$app = JFactory::getApplication();

		// Attempt to make the file writable if using FTP.
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