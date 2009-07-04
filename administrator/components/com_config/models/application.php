<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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
	 * @return	mixed		A JForm object on success, false on failure.
	 * @since	1.6
	 */
	public function getForm()
	{
		// Get the form.
		$form = parent::getForm('application', array('array' => 'jform', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
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
			$data['sitename'] = htmlspecialchars($data['sitename'], ENT_COMPAT, 'UTF-8');
		}

		// Escape the MetaDesc if present.
		if (isset($data['MetaDesc'])) {
			$data['MetaDesc'] = htmlspecialchars($data['MetaDesc'], ENT_COMPAT, 'UTF-8');
		}

		// Escape the MetaKeys if present.
		if (isset($data['MetaKeys'])) {
			$data['MetaKeys'] = htmlspecialchars($data['MetaKeys'], ENT_COMPAT, 'UTF-8');
		}

		// Escape the offline message if present.
		if (isset($data['offline_message'])) {
			$data['offline_message']	= JFilterOutput::ampReplace($data['offline_message']);
			$data['offline_message']	= str_replace('"', '&quot;', $data['offline_message']);
			$data['offline_message']	= str_replace("'", '&#039;', $data['offline_message']);
		}

		// Purge the database session table if we are changing to the database handler.
		if ($prev['session_handler'] != 'database' && $data['session_handler'] == 'database')
		{
			$table = JTable::getInstance('session');
			$table->purge(-1);
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
		$file = JPATH_CONFIGURATION.DS.'configuration.php';

		// Overwrite the old FTP credentials with the new ones.
		$temp = JFactory::getConfig();
		$temp->setValue('config.ftp_enable', $data['ftp_enable']);
		$temp->setValue('config.ftp_host', $data['ftp_host']);
		$temp->setValue('config.ftp_port', $data['ftp_port']);
		$temp->setValue('config.ftp_user', $data['ftp_user']);
		$temp->setValue('config.ftp_pass', $data['ftp_pass']);
		$temp->setValue('config.ftp_root', $data['ftp_root']);

		// Get the new FTP credentials.
		$ftp = JClientHelper::getCredentials('ftp', true);

		// Attempt to make the file writeable if using FTP.
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0644')) {
			JError::raiseNotice('SOME_ERROR_CODE', JText::_('Config_File_Could_Not_Make_Writable'));
		}

		// Attempt to write the configuration file as a PHP class named JConfig.
		if (!JFile::write($file, $config->toString('PHP', 'config', array('class' => 'JConfig', 'closingtag' => false)))) {
			$this->setError(JText::_('Config_File_Write_Failed'));
			return false;
		}

		// Attempt to make the file unwriteable if using FTP.
		if ($data['ftp_enable'] == 0 && !$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0444')) {
			JError::raiseNotice('SOME_ERROR_CODE', JText::_('Config_File_Could_Not_Make_Unwritable'));
		}

		return true;
	}
}