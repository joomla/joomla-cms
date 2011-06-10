<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_config
 */
class ConfigModelApplication extends JModelForm
{
	/**
	 * Method to get a form object.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_config.application', 'application', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
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
	 * @return	array		An array containg all global config data.
	 * @since	1.6
	 */
	public function getData()
	{
		// Get the config data.
		$config	= new JConfig();
		$data	= JArrayHelper::fromObject($config);

		// Prime the asset_id for the rules.
		$data['asset_id'] = 1;

		// Check for data in the session.
		$app	= JFactory::getApplication();
		$temp	= $app->getUserState('com_config.config.global.data');

		// Merge in the session data.
		if (!empty($temp)) {
			$data = array_merge($data, $temp);
		}

		return $data;
	}

	/**
	 * Method to save the configuration data.
	 *
	 * @param	array	An array containing all global config data.
	 * @return	bool	True on success, false on failure.
	 * @since	1.6
	 */
	public function save($data)
	{
		// Save the rules
		if (isset($data['rules']))
		{
			jimport('joomla.access.rules');
			$rules	= new JRules($data['rules']);

			// Check that we aren't removing our Super User permission
			// Need to get groups from database, since they might have changed
			$myGroups = JAccess::getGroupsByUser(JFactory::getUser()->get('id'));
			$myRules = $rules->getData();
			$hasSuperAdmin = $myRules['core.admin']->allow($myGroups);
			if (!$hasSuperAdmin) {
				$this->setError(JText::_('COM_CONFIG_ERROR_REMOVING_SUPER_ADMIN'));
				return false;
			}


			$asset	= JTable::getInstance('asset');
			if ($asset->loadByName('root.1'))
			{
				$asset->rules = (string) $rules;

				if (!$asset->check() || !$asset->store()) {
					JError::raiseNotice('SOME_ERROR_CODE', $asset->getError());
				}
			}
			else
			{
				$this->setError(JText::_('COM_CONFIG_ERROR_ROOT_ASSET_NOT_FOUND'));
				return false;
			}
			unset($data['rules']);
		}

		// Get the previous configuration.
		$prev = new JConfig();
		$prev = JArrayHelper::fromObject($prev);

		// Merge the new data in. We do this to preserve values that were not in the form.
		$data = array_merge($prev, $data);

		/*
		 * Perform miscellaneous options based on configuration settings/changes.
		 */
		// Escape the sitename if present.
		if (isset($data['sitename'])) {
			$data['sitename'] = $data['sitename'];
		}

		// Escape the MetaDesc if present.
		if (isset($data['MetaDesc'])) {
			$data['MetaDesc'] = $data['MetaDesc'];
		}

		// Escape the MetaKeys if present.
		if (isset($data['MetaKeys'])) {
			$data['MetaKeys'] = $data['MetaKeys'];
		}

		// Escape the offline message if present.
		if (isset($data['offline_message'])) {
			$data['offline_message']	= JFilterOutput::ampReplace($data['offline_message']);
		}

		// Purge the database session table if we are changing to the database handler.
		if ($prev['session_handler'] != 'database' && $data['session_handler'] == 'database')
		{
			$table = JTable::getInstance('session');
			$table->purge(-1);
		}

		if (empty($data['cache_handler'])) {
			$data['caching'] = 0;
		}

		// Clean the cache if disabled but previously enabled.
		if (!$data['caching'] && $prev['caching']) {
			$cache = JFactory::getCache();
			$cache->clean();
		}

		// Create the new configuration object.
		$config = new JRegistry('config');
		$config->loadArray($data);

		/*
		 * Write the configuration file.
		 */
		jimport('joomla.filesystem.path');
		jimport('joomla.filesystem.file');

		// Set the configuration file path.
		$file = JPATH_CONFIGURATION . '/configuration.php';

		// Overwrite the old FTP credentials with the new ones.
		$temp = JFactory::getConfig();
		$temp->set('ftp_enable', $data['ftp_enable']);
		$temp->set('ftp_host', $data['ftp_host']);
		$temp->set('ftp_port', $data['ftp_port']);
		$temp->set('ftp_user', $data['ftp_user']);
		$temp->set('ftp_pass', $data['ftp_pass']);
		$temp->set('ftp_root', $data['ftp_root']);

		// Get the new FTP credentials.
		$ftp = JClientHelper::getCredentials('ftp', true);

		// Attempt to make the file writeable if using FTP.
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0644')) {
			JError::raiseNotice('SOME_ERROR_CODE', JText::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTWRITABLE'));
		}

		// Attempt to write the configuration file as a PHP class named JConfig.
		$configString = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));
		if (!JFile::write($file, $configString)) {
			$this->setError(JText::_('COM_CONFIG_ERROR_WRITE_FAILED'));
			return false;
		}

		// Attempt to make the file unwriteable if using FTP.
		if ($data['ftp_enable'] == 0 && !$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0444')) {
			JError::raiseNotice('SOME_ERROR_CODE', JText::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTUNWRITABLE'));
		}

		return true;
	}

	/**
	 * Method to unset the root_user value from configuration data.
	 *
	 * This method will load the global configuration data straight from
	 * JConfig and remove the root_user value for security, then save the configuration.
	 *
	 * @since	1.6
	 */
	function removeroot()
	{
		// Include client helper
		jimport('joomla.client.helper');

		// Get the previous configuration.
		$prev = new JConfig();
		$prev = JArrayHelper::fromObject($prev);

		// Clean the cache if disabled but previously enabled.
		if ($prev['caching']) {
			$cache = JFactory::getCache();
			$cache->clean();
		}

		// Create the new configuration object, and unset the root_user property
		$config = new JRegistry('config');
		unset($prev['root_user']);
		$config->loadArray($prev);

		/*
		 * Write the configuration file.
		 */
		jimport('joomla.filesystem.path');
		jimport('joomla.filesystem.file');

		// Set the configuration file path.
		$file = JPATH_CONFIGURATION . '/configuration.php';

		// Overwrite the old FTP credentials with the new ones.
		$temp = JFactory::getConfig();
		$temp->set('ftp_enable', $prev['ftp_enable']);
		$temp->set('ftp_host', $prev['ftp_host']);
		$temp->set('ftp_port', $prev['ftp_port']);
		$temp->set('ftp_user', $prev['ftp_user']);
		$temp->set('ftp_pass', $prev['ftp_pass']);
		$temp->set('ftp_root', $prev['ftp_root']);

		// Get the new FTP credentials.
		$ftp = JClientHelper::getCredentials('ftp', true);

		// Attempt to make the file writeable if using FTP.
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0644')) {
			JError::raiseNotice('SOME_ERROR_CODE', JText::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTWRITABLE'));
		}

		// Attempt to write the configuration file as a PHP class named JConfig.
		if (!JFile::write($file, $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false)))) {
			$this->setError(JText::_('COM_CONFIG_ERROR_WRITE_FAILED'));
			return false;
		}

		// Attempt to make the file unwriteable if using FTP.
		if ($prev['ftp_enable'] == 0 && !$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0444')) {
			JError::raiseNotice('SOME_ERROR_CODE', JText::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTUNWRITABLE'));
		}

		return true;
	}
}
