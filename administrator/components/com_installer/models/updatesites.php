<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('InstallerModel', __DIR__ . '/extension.php');

/**
 * Installer Update Sites Model
 *
 * @since  3.4
 */
class InstallerModelUpdatesites extends InstallerModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   3.4
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'update_site_name',
				'name',
				'client_id',
				'client', 'client_translated',
				'status',
				'type', 'type_translated',
				'folder', 'folder_translated',
				'update_site_id',
				'enabled',
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
	 * @since   3.4
	 */
	protected function populateState($ordering = 'name', $direction = 'asc')
	{
		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.client_id', $this->getUserStateFromRequest($this->context . '.filter.client_id', 'filter_client_id', null, 'int'));
		$this->setState('filter.enabled', $this->getUserStateFromRequest($this->context . '.filter.enabled', 'filter_enabled', '', 'string'));
		$this->setState('filter.type', $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '', 'string'));
		$this->setState('filter.folder', $this->getUserStateFromRequest($this->context . '.filter.folder', 'filter_folder', '', 'string'));

		parent::populateState($ordering, $direction);
	}

	/**
	 * Enable/Disable an extension.
	 *
	 * @param   array  &$eid   Extension ids to un/publish
	 * @param   int    $value  Publish value
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.4
	 *
	 * @throws  Exception on ACL error
	 */
	public function publish(&$eid = array(), $value = 1)
	{
		if (!JFactory::getUser()->authorise('core.edit.state', 'com_installer'))
		{
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 403);
		}

		$result = true;

		// Ensure eid is an array of extension ids
		if (!is_array($eid))
		{
			$eid = array($eid);
		}

		// Get a table object for the extension type
		$table = JTable::getInstance('Updatesite');

		// Enable the update site in the table and store it in the database
		foreach ($eid as $i => $id)
		{
			$table->load($id);
			$table->enabled = $value;

			if (!$table->store())
			{
				$this->setError($table->getError());
				$result = false;
			}
		}

		return $result;
	}

	/**
	 * Deletes an update site.
	 *
	 * @param   array  $ids  Extension ids to delete.
	 *
	 * @return  void
	 *
	 * @since   3.6
	 *
	 * @throws  Exception on ACL error
	 */
	public function delete($ids = array())
	{
		if (!JFactory::getUser()->authorise('core.delete', 'com_installer'))
		{
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 403);
		}

		// Ensure eid is an array of extension ids
		if (!is_array($ids))
		{
			$ids = array($ids);
		}

		$db  = JFactory::getDbo();
		$app = JFactory::getApplication();

		$count = 0;

		// Gets the update site names.
		$query = $db->getQuery(true)
			->select($db->qn(array('update_site_id', 'name')))
			->from($db->qn('#__update_sites'))
			->where($db->qn('update_site_id') . ' IN (' . implode(', ', $ids) . ')');
		$db->setQuery($query);
		$updateSitesNames = $db->loadObjectList('update_site_id');

		// Gets Joomla core update sites Ids.
		$joomlaUpdateSitesIds = $this->getJoomlaUpdateSitesIds(0);

		// Enable the update site in the table and store it in the database
		foreach ($ids as $i => $id)
		{
			// Don't allow to delete Joomla Core update sites.
			if (in_array((int) $id, $joomlaUpdateSitesIds))
			{
				$app->enqueueMessage(JText::sprintf('COM_INSTALLER_MSG_UPDATESITES_DELETE_CANNOT_DELETE', $updateSitesNames[$id]->name), 'error');
				continue;
			}

			// Delete the update site from all tables.
			try
			{
				$query = $db->getQuery(true)
					->delete($db->qn('#__update_sites'))
					->where($db->qn('update_site_id') . ' = ' . (int) $id);
				$db->setQuery($query);
				$db->execute();

				$query = $db->getQuery(true)
					->delete($db->qn('#__update_sites_extensions'))
					->where($db->qn('update_site_id') . ' = ' . (int) $id);
				$db->setQuery($query);
				$db->execute();

				$query = $db->getQuery(true)
					->delete($db->qn('#__updates'))
					->where($db->qn('update_site_id') . ' = ' . (int) $id);
				$db->setQuery($query);
				$db->execute();

				$count++;
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage(JText::sprintf('COM_INSTALLER_MSG_UPDATESITES_DELETE_ERROR', $updateSitesNames[$id]->name, $e->getMessage()), 'error');
			}
		}

		if ($count > 0)
		{
			$app->enqueueMessage(JText::plural('COM_INSTALLER_MSG_UPDATESITES_N_DELETE_UPDATESITES_DELETED', $count), 'message');
		}
	}

	/**
	 * Rebuild update sites tables.
	 *
	 * @return  void
	 *
	 * @since   3.6
	 *
	 * @throws  Exception on ACL error
	 */
	public function rebuild()
	{
		if (!JFactory::getUser()->authorise('core.admin', 'com_installer'))
		{
			throw new Exception(JText::_('COM_INSTALLER_MSG_UPDATESITES_REBUILD_NOT_PERMITTED'), 403);
		}

		$db  = JFactory::getDbo();
		$app = JFactory::getApplication();

		// Check if Joomla Extension plugin is enabled.
		if (!JPluginHelper::isEnabled('extension', 'joomla'))
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('extension_id'))
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
				->where($db->quoteName('element') . ' = ' . $db->quote('joomla'))
				->where($db->quoteName('folder') . ' = ' . $db->quote('extension'));
			$db->setQuery($query);

			$pluginId = (int) $db->loadResult();

			$link = JRoute::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . $pluginId);
			$app->enqueueMessage(JText::sprintf('COM_INSTALLER_MSG_UPDATESITES_REBUILD_EXTENSION_PLUGIN_NOT_ENABLED', $link), 'error');

			return;
		}

		$clients               = array(JPATH_SITE, JPATH_ADMINISTRATOR);
		$extensionGroupFolders = array('components', 'modules', 'plugins', 'templates', 'language', 'manifests');

		$pathsToSearch = array();

		// Identifies which folders to search for manifest files.
		foreach ($clients as $clientPath)
		{
			foreach ($extensionGroupFolders as $extensionGroupFolderName)
			{
				// Components, modules, plugins, templates, languages and manifest (files, libraries, etc)
				if ($extensionGroupFolderName != 'plugins')
				{
					foreach (glob($clientPath . '/' . $extensionGroupFolderName . '/*', GLOB_NOSORT | GLOB_ONLYDIR) as $extensionFolderPath)
					{
						$pathsToSearch[] = $extensionFolderPath;
					}
				}

				// Plugins (another directory level is needed)
				else
				{
					foreach (glob($clientPath . '/' . $extensionGroupFolderName . '/*', GLOB_NOSORT | GLOB_ONLYDIR) as $pluginGroupFolderPath)
					{
						foreach (glob($pluginGroupFolderPath . '/*', GLOB_NOSORT | GLOB_ONLYDIR) as $extensionFolderPath)
						{
							$pathsToSearch[] = $extensionFolderPath;
						}
					}
				}
			}
		}

		// Gets Joomla core update sites Ids.
		$joomlaUpdateSitesIds = implode(', ', $this->getJoomlaUpdateSitesIds(0));

		// Delete from all tables (except joomla core update sites).
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__update_sites'))
			->where($db->quoteName('update_site_id') . ' NOT IN (' . $joomlaUpdateSitesIds . ')');
		$db->setQuery($query);
		$db->execute();

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__update_sites_extensions'))
			->where($db->quoteName('update_site_id') . ' NOT IN (' . $joomlaUpdateSitesIds . ')');
		$db->setQuery($query);
		$db->execute();

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__updates'))
			->where($db->quoteName('update_site_id') . ' NOT IN (' . $joomlaUpdateSitesIds . ')');
		$db->setQuery($query);
		$db->execute();

		$count = 0;

		// Gets Joomla core extension Ids.
		$joomlaCoreExtensionIds = implode(', ', $this->getJoomlaUpdateSitesIds(1));

		// Search for updateservers in manifest files inside the folders to search.
		foreach ($pathsToSearch as $extensionFolderPath)
		{
			$tmpInstaller = new JInstaller;

			$tmpInstaller->setPath('source', $extensionFolderPath);

			// Main folder manifests (higher priority)
			$parentXmlfiles = JFolder::files($tmpInstaller->getPath('source'), '.xml$', false, true);

			// Search for children manifests (lower priority)
			$allXmlFiles    = JFolder::files($tmpInstaller->getPath('source'), '.xml$', 1, true);

			// Create an unique array of files ordered by priority
			$xmlfiles = array_unique(array_merge($parentXmlfiles, $allXmlFiles));

			if (!empty($xmlfiles))
			{
				foreach ($xmlfiles as $file)
				{
					// Is it a valid Joomla installation manifest file?
					$manifest = $tmpInstaller->isManifest($file);

					if (!is_null($manifest))
					{
						// Search if the extension exists in the extensions table. Excluding joomla core extensions (id < 10000) and discovered extensions.
						$query = $db->getQuery(true)
							->select($db->quoteName('extension_id'))
							->from($db->quoteName('#__extensions'))
							->where($db->quoteName('name') . ' = ' . $db->quote($manifest->name))
							->where($db->quoteName('type') . ' = ' . $db->quote($manifest['type']))
							->where($db->quoteName('extension_id') . ' NOT IN (' . $joomlaCoreExtensionIds . ')')
							->where($db->quoteName('state') . ' != -1');
						$db->setQuery($query);

						$eid = (int) $db->loadResult();

						if ($eid && $manifest->updateservers)
						{
							// Set the manifest object and path
							$tmpInstaller->manifest = $manifest;
							$tmpInstaller->setPath('manifest', $file);

							// Load the extension plugin (if not loaded yet).
							JPluginHelper::importPlugin('extension', 'joomla');

							// Fire the onExtensionAfterUpdate
							JEventDispatcher::getInstance()->trigger('onExtensionAfterUpdate', array('installer' => $tmpInstaller, 'eid' => $eid));

							$count++;
						}
					}
				}
			}
		}

		if ($count > 0)
		{
			$app->enqueueMessage(JText::_('COM_INSTALLER_MSG_UPDATESITES_REBUILD_SUCCESS'), 'message');
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_INSTALLER_MSG_UPDATESITES_REBUILD_MESSAGE'), 'message');
		}
	}

	/**
	 * Fetch the Joomla update sites ids.
	 *
	 * @param   integer  $column  Column to return. 0 for update site ids, 1 for extension ids.
	 *
	 * @return  array  Array with joomla core update site ids.
	 *
	 * @since   3.6.0
	 */
	protected function getJoomlaUpdateSitesIds($column = 0)
	{
		$db  = JFactory::getDbo();

		// Fetch the Joomla core update sites ids and their extension ids. We search for all except the core joomla extension with update sites.
		$query = $db->getQuery(true)
			->select($db->quoteName(array('use.update_site_id', 'e.extension_id')))
			->from($db->quoteName('#__update_sites_extensions', 'use'))
			->join('LEFT', $db->quoteName('#__update_sites', 'us') . ' ON ' . $db->qn('us.update_site_id') . ' = ' . $db->qn('use.update_site_id'))
			->join('LEFT', $db->quoteName('#__extensions', 'e') . ' ON ' . $db->qn('e.extension_id') . ' = ' . $db->qn('use.extension_id'))
			->where('('
				. '(' . $db->qn('e.type') . ' = ' . $db->quote('file') . ' AND ' . $db->qn('e.element') . ' = ' . $db->quote('joomla') . ')'
				. ' OR (' . $db->qn('e.type') . ' = ' . $db->quote('package') . ' AND ' . $db->qn('e.element') . ' = ' . $db->quote('pkg_en-GB') . ')'
				. ' OR (' . $db->qn('e.type') . ' = ' . $db->quote('component') . ' AND ' . $db->qn('e.element') . ' = ' . $db->quote('com_joomlaupdate') . ')'
				. ')'
			);

		$db->setQuery($query);

		return $db->loadColumn($column);
	}

	/**
	 * Method to get the database query
	 *
	 * @return  JDatabaseQuery  The database query
	 *
	 * @since   3.4
	 */
	protected function getListQuery()
	{
		$query = JFactory::getDbo()->getQuery(true)
			->select(
				array(
					's.update_site_id',
					's.name AS update_site_name',
					's.type AS update_site_type',
					's.location',
					's.enabled',
					'e.extension_id',
					'e.name',
					'e.type',
					'e.element',
					'e.folder',
					'e.client_id',
					'e.state',
					'e.manifest_cache',
				)
			)
			->from('#__update_sites AS s')
			->innerJoin('#__update_sites_extensions AS se ON (se.update_site_id = s.update_site_id)')
			->innerJoin('#__extensions AS e ON (e.extension_id = se.extension_id)')
			->where('state = 0');

		// Process select filters.
		$enabled  = $this->getState('filter.enabled');
		$type     = $this->getState('filter.type');
		$clientId = $this->getState('filter.client_id');
		$folder   = $this->getState('filter.folder');

		if ($enabled != '')
		{
			$query->where('s.enabled = ' . (int) $enabled);
		}

		if ($type)
		{
			$query->where('e.type = ' . $this->_db->quote($type));
		}

		if ($clientId != '')
		{
			$query->where('e.client_id = ' . (int) $clientId);
		}

		if ($folder != '' && in_array($type, array('plugin', 'library', '')))
		{
			$query->where('e.folder = ' . $this->_db->quote($folder == '*' ? '' : $folder));
		}

		// Process search filter (update site id).
		$search = $this->getState('filter.search');

		if (!empty($search) && stripos($search, 'id:') === 0)
		{
			$query->where('s.update_site_id = ' . (int) substr($search, 3));
		}

		// Note: The search for name, ordering and pagination are processed by the parent InstallerModel class (in extension.php).

		return $query;
	}
}
