<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('InstallerModel', __DIR__ . '/extension.php');
JLoader::register('JoomlaInstallerScript', JPATH_ADMINISTRATOR . '/components/com_admin/script.php');

/**
 * Extension Manager Templates Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       1.6
 */
class InstallerModelService extends InstallerModel
{
	protected $_context = 'com_installer.service';

	/**
	 * Extension Type
	 * @var	string
	 */
	public $type = 'warnings';

	/**
	 * Return the byte value of a particular string.
	 *
	 * @param   string  $val  String optionally with G, M or K suffix
	 *
	 * @return  integer   size in bytes
	 *
	 * @since 1.6
	 */
	public function return_bytes($val)
	{
		$val = trim($val);
		$last = strtolower($val{strlen($val) - 1});
		switch ($last)
		{
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		$this->setState('message', $app->getUserState('com_installer.message'));
		$this->setState('extension_message', $app->getUserState('com_installer.extension_message'));
		$app->setUserState('com_installer.message', '');
		$app->setUserState('com_installer.extension_message', '');
		parent::populateState('name', 'asc');
	}

	/**
	 * Fixes database problems
	 *
	 * @return  void
	 */
	public function fix()
	{
		if (!$changeSet = $this->getChangeset())
		{
			return false;
		}
		$changeSet->fix();
		$this->fixSchemaVersion($changeSet);
		$this->fixUpdateVersion();
		$installer = new JoomlaInstallerScript;
		$installer->deleteUnexistingFiles();
		$this->fixDefaultTextFilters();
	}

	/**
	 * Load the data.
	 *
	 * @return  array  Messages
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		static $messages;
		if ($messages)
		{
			return $messages;
		}
		$messages = array();

		$file_uploads = ini_get('file_uploads');
		if (!$file_uploads)
		{
			$messages[] = array('message' => JText::_('COM_INSTALLER_MSG_WARNINGS_FILEUPLOADSDISABLED'),
					'description' => JText::_('COM_INSTALLER_MSG_WARNINGS_FILEUPLOADISDISABLEDDESC'));
		}

		$upload_dir = ini_get('upload_tmp_dir');
		if (!$upload_dir)
		{
			$messages[] = array('message' => JText::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTSET'),
					'description' => JText::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTSETDESC'));
		}
		else
		{
			if (!is_writeable($upload_dir))
			{
				$messages[] = array('message' => JText::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTWRITEABLE'),
						'description' => JText::sprintf('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTWRITEABLEDESC', $upload_dir));
			}
		}

		$config = JFactory::getConfig();
		$tmp_path = $config->get('tmp_path');
		if (!$tmp_path)
		{
			$messages[] = array('message' => JText::_('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTSET'),
					'description' => JText::_('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTSETDESC'));
		}
		else
		{
			if (!is_writeable($tmp_path))
			{
				$messages[] = array('message' => JText::_('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTWRITEABLE'),
						'description' => JText::sprintf('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTWRITEABLEDESC', $tmp_path));
			}
		}

		$memory_limit = $this->return_bytes(ini_get('memory_limit'));
		if ($memory_limit < (8 * 1024 * 1024) && $memory_limit != -1)
		{
			// 8MB
			$messages[] = array('message' => JText::_('COM_INSTALLER_MSG_WARNINGS_LOWMEMORYWARN'),
					'description' => JText::_('COM_INSTALLER_MSG_WARNINGS_LOWMEMORYDESC'));
		}
		elseif ($memory_limit < (16 * 1024 * 1024) && $memory_limit != -1)
		{
			// 16MB
			$messages[] = array('message' => JText::_('COM_INSTALLER_MSG_WARNINGS_MEDMEMORYWARN'),
					'description' => JText::_('COM_INSTALLER_MSG_WARNINGS_MEDMEMORYDESC'));
		}

		$post_max_size = $this->return_bytes(ini_get('post_max_size'));
		$upload_max_filesize = $this->return_bytes(ini_get('upload_max_filesize'));

		if ($post_max_size < $upload_max_filesize)
		{
			$messages[] = array('message' => JText::_('COM_INSTALLER_MSG_WARNINGS_UPLOADBIGGERTHANPOST'),
					'description' => JText::_('COM_INSTALLER_MSG_WARNINGS_UPLOADBIGGERTHANPOSTDESC'));
		}

		if ($post_max_size < (8 * 1024 * 1024)) // 8MB
		{
			$messages[] = array('message' => JText::_('COM_INSTALLER_MSG_WARNINGS_SMALLPOSTSIZE'),
					'description' => JText::_('COM_INSTALLER_MSG_WARNINGS_SMALLPOSTSIZEDESC'));
		}

		if ($upload_max_filesize < (8 * 1024 * 1024)) // 8MB
		{
			$messages[] = array('message' => JText::_('COM_INSTALLER_MSG_WARNINGS_SMALLUPLOADSIZE'),
					'description' => JText::_('COM_INSTALLER_MSG_WARNINGS_SMALLUPLOADSIZEDESC'));
		}

		return $messages;
	}

	/**
	 * Gets the changeset object
	 *
	 * @return  JSchemaChangeset
	 */
	public function getChangeset()
	{
		$folder = JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/';

		try
		{
			$changeSet = JSchemaChangeset::getInstance(JFactory::getDbo(), $folder);
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
			return false;
		}
		return $changeSet;
	}

	/**
	 * Get version from #__schemas table
	 *
	 * @return  mixed  the return value from the query, or null if the query fails
	 *
	 * @throws Exception
	 */
	public function getSchemaVersion()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('version_id')
			->from($db->quoteName('#__schemas'))
			->where('extension_id = 700');
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Fix schema version if wrong
	 *
	 * @param   JSchemaChangeSet  $changeSet  Schema change set
	 *
	 * @return   mixed  string schema version if success, false if fail
	 */
	public function fixSchemaVersion($changeSet)
	{
		// Get correct schema version -- last file in array
		$schema = $changeSet->getSchema();
		$db = JFactory::getDbo();
		$result = false;

		// Check value. If ok, don't do update
		$version = $this->getSchemaVersion();
		if ($version == $schema)
		{
			$result = $version;
		}
		else
		{
			// Delete old row
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__schemas'))
				->where($db->quoteName('extension_id') . ' = 700');
			$db->setQuery($query);
			$db->execute();

			// Add new row
			$query->clear()
				->insert($db->quoteName('#__schemas'))
				->set($db->quoteName('extension_id') . '= 700')
				->set($db->quoteName('version_id') . '= ' . $db->quote($schema));
			$db->setQuery($query);
			if ($db->execute())
			{
				$result = $schema;
			}
		}
		return $result;
	}

	/**
	 * Get current version from #__extensions table
	 *
	 * @return  mixed   version if successful, false if fail
	 */

	public function getUpdateVersion()
	{
		$table = JTable::getInstance('Extension');
		$table->load('700');
		$cache = new JRegistry($table->manifest_cache);
		return $cache->get('version');
	}

	/**
	 * Fix Joomla version in #__extensions table if wrong (doesn't equal JVersion short version)
	 *
	 * @return   mixed  string update version if success, false if fail
	 */
	public function fixUpdateVersion()
	{
		$table = JTable::getInstance('Extension');
		$table->load('700');
		$cache = new JRegistry($table->manifest_cache);
		$updateVersion = $cache->get('version');
		$cmsVersion = new JVersion;
		if ($updateVersion == $cmsVersion->getShortVersion())
		{
			return $updateVersion;
		}
		else
		{
			$cache->set('version', $cmsVersion->getShortVersion());
			$table->manifest_cache = $cache->toString();
			if ($table->store())
			{
				return $cmsVersion->getShortVersion();
			}
			else
			{
				return false;
			}

		}
	}

	/**
	 * For version 2.5.x only
	 * Check if com_config parameters are blank.
	 *
	 * @return  string  default text filters (if any)
	 */
	public function getDefaultTextFilters()
	{
		$table = JTable::getInstance('Extension');
		$table->load($table->find(array('name' => 'com_config')));
		return $table->params;
	}
	/**
	 * For version 2.5.x only
	 * Check if com_config parameters are blank. If so, populate with com_content text filters.
	 *
	 * @return  mixed  boolean true if params are updated, null otherwise
	 */
	public function fixDefaultTextFilters()
	{
		$table = JTable::getInstance('Extension');
		$table->load($table->find(array('name' => 'com_config')));

		// Check for empty $config and non-empty content filters
		if (!$table->params)
		{
			// Get filters from com_content and store if you find them
			$contentParams = JComponentHelper::getParams('com_content');
			if ($contentParams->get('filters'))
			{
				$newParams = new JRegistry;
				$newParams->set('filters', $contentParams->get('filters'));
				$table->params = (string) $newParams;
				$table->store();
				return true;
			}
		}
	}
}
