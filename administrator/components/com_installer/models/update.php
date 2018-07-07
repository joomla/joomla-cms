<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.updater.update');

use Joomla\Utilities\ArrayHelper;

/**
 * Installer Update Model
 *
 * @since  1.6
 */
class InstallerModelUpdate extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'name', 'u.name',
				'client_id', 'u.client_id', 'client_translated',
				'type', 'u.type', 'type_translated',
				'folder', 'u.folder', 'folder_translated',
				'extension_id', 'u.extension_id',
			);
		}

		parent::__construct($config);
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
	protected function populateState($ordering = 'u.name', $direction = 'asc')
	{
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.client_id', $this->getUserStateFromRequest($this->context . '.filter.client_id', 'filter_client_id', null, 'int'));
		$this->setState('filter.type', $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '', 'string'));
		$this->setState('filter.folder', $this->getUserStateFromRequest($this->context . '.filter.folder', 'filter_folder', '', 'string'));

		$app = JFactory::getApplication();
		$this->setState('message', $app->getUserState('com_installer.message'));
		$this->setState('extension_message', $app->getUserState('com_installer.extension_message'));
		$app->setUserState('com_installer.message', '');
		$app->setUserState('com_installer.extension_message', '');

		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get the database query
	 *
	 * @return  JDatabaseQuery  The database query
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();

		// Grab updates ignoring new installs
		$query = $db->getQuery(true)
			->select('u.*')
			->select($db->quoteName('e.manifest_cache'))
			->from($db->quoteName('#__updates', 'u'))
			->join('LEFT', $db->quoteName('#__extensions', 'e') . ' ON ' . $db->quoteName('e.extension_id') . ' = ' . $db->quoteName('u.extension_id'))
			->where($db->quoteName('u.extension_id') . ' != ' . $db->quote(0));

		// Process select filters.
		$clientId    = $this->getState('filter.client_id');
		$type        = $this->getState('filter.type');
		$folder      = $this->getState('filter.folder');
		$extensionId = $this->getState('filter.extension_id');

		if ($type)
		{
			$query->where($db->quoteName('u.type') . ' = ' . $db->quote($type));
		}

		if ($clientId != '')
		{
			$query->where($db->quoteName('u.client_id') . ' = ' . (int) $clientId);
		}

		if ($folder != '' && in_array($type, array('plugin', 'library', '')))
		{
			$query->where($db->quoteName('u.folder') . ' = ' . $db->quote($folder == '*' ? '' : $folder));
		}

		if ($extensionId)
		{
			$query->where($db->quoteName('u.extension_id') . ' = ' . $db->quote((int) $extensionId));
		}
		else
		{
			$query->where($db->quoteName('u.extension_id') . ' != ' . $db->quote(0))
				->where($db->quoteName('u.extension_id') . ' != ' . $db->quote(700));
		}

		// Process search filter.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'eid:') !== false)
			{
				$query->where($db->quoteName('u.extension_id') . ' = ' . (int) substr($search, 4));
			}
			else
			{
				if (stripos($search, 'uid:') !== false)
				{
					$query->where($db->quoteName('u.update_site_id') . ' = ' . (int) substr($search, 4));
				}
				elseif (stripos($search, 'id:') !== false)
				{
					$query->where($db->quoteName('u.update_id') . ' = ' . (int) substr($search, 3));
				}
				else
				{
					$query->where($db->quoteName('u.name') . ' LIKE ' . $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true)) . '%'));
				}
			}
		}

		return $query;
	}

	/**
	 * Translate a list of objects
	 *
	 * @param   array  $items  The array of objects
	 *
	 * @return  array The array of translated objects
	 *
	 * @since   3.5
	 */
	protected function translate(&$items)
	{
		foreach ($items as &$item)
		{
			$item->client_translated  = $item->client_id ? JText::_('JADMINISTRATOR') : JText::_('JSITE');
			$manifest                 = json_decode($item->manifest_cache);
			$item->current_version    = isset($manifest->version) ? $manifest->version : JText::_('JLIB_UNKNOWN');
			$item->type_translated    = JText::_('COM_INSTALLER_TYPE_' . strtoupper($item->type));
			$item->folder_translated  = $item->folder ?: JText::_('COM_INSTALLER_TYPE_NONAPPLICABLE');
			$item->install_type       = $item->extension_id ? JText::_('COM_INSTALLER_MSG_UPDATE_UPDATE') : JText::_('COM_INSTALLER_NEW_INSTALL');
		}

		return $items;
	}

	/**
	 * Returns an object list
	 *
	 * @param   string  $query       The query
	 * @param   int     $limitstart  Offset
	 * @param   int     $limit       The number of records
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$db = $this->getDbo();
		$listOrder = $this->getState('list.ordering', 'u.name');
		$listDirn  = $this->getState('list.direction', 'asc');

		// Process ordering.
		if (in_array($listOrder, array('client_translated', 'folder_translated', 'type_translated')))
		{
			$db->setQuery($query);
			$result = $db->loadObjectList();
			$this->translate($result);
			$result = ArrayHelper::sortObjects($result, $listOrder, strtolower($listDirn) === 'desc' ? -1 : 1, true, true);
			$total = count($result);

			if ($total < $limitstart)
			{
				$limitstart = 0;
				$this->setState('list.start', 0);
			}

			return array_slice($result, $limitstart, $limit ?: null);
		}
		else
		{
			$query->order($db->quoteName($listOrder) . ' ' . $db->escape($listDirn));

			$result = parent::_getList($query, $limitstart, $limit);
			$this->translate($result);

			return $result;
		}
	}

	/**
	 * Get the count of disabled update sites
	 *
	 * @return  integer
	 *
	 * @since   3.4
	 */
	public function getDisabledUpdateSites()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->quoteName('#__update_sites'))
			->where($db->quoteName('enabled') . ' = 0');

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Finds updates for an extension.
	 *
	 * @param   int  $eid                Extension identifier to look for
	 * @param   int  $cache_timeout      Cache timout
	 * @param   int  $minimum_stability  Minimum stability for updates {@see JUpdater} (0=dev, 1=alpha, 2=beta, 3=rc, 4=stable)
	 *
	 * @return  boolean Result
	 *
	 * @since   1.6
	 */
	public function findUpdates($eid = 0, $cache_timeout = 0, $minimum_stability = JUpdater::STABILITY_STABLE)
	{
		// Purge the updates list
		$this->purge();

		JUpdater::getInstance()->findUpdates($eid, $cache_timeout, $minimum_stability);

		return true;
	}

	/**
	 * Removes all of the updates from the table.
	 *
	 * @return  boolean result of operation
	 *
	 * @since   1.6
	 */
	public function purge()
	{
		$db = $this->getDbo();

		// Note: TRUNCATE is a DDL operation
		// This may or may not mean depending on your database
		$db->setQuery('TRUNCATE TABLE #__updates');

		try
		{
			$db->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			$this->_message = JText::_('JLIB_INSTALLER_FAILED_TO_PURGE_UPDATES');

			return false;
		}

		// Reset the last update check timestamp
		$query = $db->getQuery(true)
			->update($db->quoteName('#__update_sites'))
			->set($db->quoteName('last_check_timestamp') . ' = ' . $db->quote(0));
		$db->setQuery($query);
		$db->execute();
		$this->_message = JText::_('JLIB_INSTALLER_PURGED_UPDATES');

		return true;
	}

	/**
	 * Enables any disabled rows in #__update_sites table
	 *
	 * @return  boolean result of operation
	 *
	 * @since   1.6
	 */
	public function enableSites()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->update($db->quoteName('#__update_sites'))
			->set($db->quoteName('enabled') . ' = 1')
			->where($db->quoteName('enabled') . ' = 0');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			$this->_message .= JText::_('COM_INSTALLER_FAILED_TO_ENABLE_UPDATES');

			return false;
		}

		if ($rows = $db->getAffectedRows())
		{
			$this->_message .= JText::plural('COM_INSTALLER_ENABLED_UPDATES', $rows);
		}

		return true;
	}

	/**
	 * Update function.
	 *
	 * Sets the "result" state with the result of the operation.
	 *
	 * @param   array  $uids               Array[int] List of updates to apply
	 * @param   int    $minimum_stability  The minimum allowed stability for installed updates {@see JUpdater}
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function update($uids, $minimum_stability = JUpdater::STABILITY_STABLE)
	{
		$result = true;

		foreach ($uids as $uid)
		{
			$update = new JUpdate;
			$instance = JTable::getInstance('update');
			$instance->load($uid);
			$update->loadFromXml($instance->detailsurl, $minimum_stability);
			$update->set('extra_query', $instance->extra_query);

			$this->preparePreUpdate($update, $instance);

			// Install sets state and enqueues messages
			$res = $this->install($update);

			if ($res)
			{
				$instance->delete($uid);
			}

			$result = $res & $result;
		}

		// Clear the cached extension data and menu cache
		$this->cleanCache('_system', 0);
		$this->cleanCache('_system', 1);
		$this->cleanCache('com_modules', 0);
		$this->cleanCache('com_modules', 1);
		$this->cleanCache('com_plugins', 0);
		$this->cleanCache('com_plugins', 1);
		$this->cleanCache('mod_menu', 0);
		$this->cleanCache('mod_menu', 1);

		// Set the final state
		$this->setState('result', $result);
	}

	/**
	 * Handles the actual update installation.
	 *
	 * @param   JUpdate  $update  An update definition
	 *
	 * @return  boolean   Result of install
	 *
	 * @since   1.6
	 */
	private function install($update)
	{
		$app = JFactory::getApplication();

		if (!isset($update->get('downloadurl')->_data))
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_INVALID_EXTENSION_UPDATE'));

			return false;
		}

		$url     = $update->downloadurl->_data;
		$sources = $update->get('downloadSources', array());

		if ($extra_query = $update->get('extra_query'))
		{
			$url .= (strpos($url, '?') === false) ? '?' : '&amp;';
			$url .= $extra_query;
		}

		$mirror = 0;

		while (!($p_file = JInstallerHelper::downloadPackage($url)) && isset($sources[$mirror]))
		{
			$name = $sources[$mirror];
			$url  = $name->url;

			if ($extra_query)
			{
				$url .= (strpos($url, '?') === false) ? '?' : '&amp;';
				$url .= $extra_query;
			}

			$mirror++;
		}

		// Was the package downloaded?
		if (!$p_file)
		{
			JError::raiseWarning('', JText::sprintf('COM_INSTALLER_PACKAGE_DOWNLOAD_FAILED', $url));

			return false;
		}

		$config   = JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path');

		// Unpack the downloaded package file
		$package = JInstallerHelper::unpack($tmp_dest . '/' . $p_file);

		// Get an installer instance
		$installer = JInstaller::getInstance();
		$update->set('type', $package['type']);

		// Install the package
		if (!$installer->update($package['dir']))
		{
			// There was an error updating the package
			$msg    = JText::sprintf('COM_INSTALLER_MSG_UPDATE_ERROR', JText::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
			$result = false;
		}
		else
		{
			// Package updated successfully
			$msg    = JText::sprintf('COM_INSTALLER_MSG_UPDATE_SUCCESS', JText::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
			$result = true;
		}

		// Quick change
		$this->type = $package['type'];

		// Set some model state values
		$app->enqueueMessage($msg);

		// TODO: Reconfigure this code when you have more battery life left
		$this->setState('name', $installer->get('name'));
		$this->setState('result', $result);
		$app->setUserState('com_installer.message', $installer->message);
		$app->setUserState('com_installer.extension_message', $installer->get('extension_message'));

		// Cleanup the install files
		if (!is_file($package['packagefile']))
		{
			$config = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

		return $result;
	}

	/**
	 * Method to get the row form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since	2.5.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		$form = JForm::getInstance('com_installer.update', 'update', array('load_data' => $loadData));

		// Check for an error.
		if ($form == false)
		{
			$this->setError($form->getMessage());

			return false;
		}

		// Check the session for previously entered form data.
		$data = $this->loadFormData();

		// Bind the form data if present.
		if (!empty($data))
		{
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since	2.5.2
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState($this->context, array());

		return $data;
	}

	/**
	 * Method to add parameters to the update
	 *
	 * @param   JUpdate       $update  An update definition
	 * @param   JTableUpdate  $table   The update instance from the database
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function preparePreUpdate($update, $table)
	{
		jimport('joomla.filesystem.file');

		switch ($table->type)
		{
			// Components could have a helper which adds additional data
			case 'component':
				$ename = str_replace('com_', '', $table->element);
				$fname = $ename . '.php';
				$cname = ucfirst($ename) . 'Helper';

				$path = JPATH_ADMINISTRATOR . '/components/' . $table->element . '/helpers/' . $fname;

				if (JFile::exists($path))
				{
					require_once $path;

					if (class_exists($cname) && is_callable(array($cname, 'prepareUpdate')))
					{
						call_user_func_array(array($cname, 'prepareUpdate'), array(&$update, &$table));
					}
				}

				break;

			// Modules could have a helper which adds additional data
			case 'module':
				$cname = str_replace('_', '', $table->element) . 'Helper';
				$path = ($table->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/modules/' . $table->element . '/helper.php';

				if (JFile::exists($path))
				{
					require_once $path;

					if (class_exists($cname) && is_callable(array($cname, 'prepareUpdate')))
					{
						call_user_func_array(array($cname, 'prepareUpdate'), array(&$update, &$table));
					}
				}

				break;

			// If we have a plugin, we can use the plugin trigger "onInstallerBeforePackageDownload"
			// But we should make sure, that our plugin is loaded, so we don't need a second "installer" plugin
			case 'plugin':
				$cname = str_replace('plg_', '', $table->element);
				JPluginHelper::importPlugin($table->folder, $cname);
				break;
		}
	}
}
