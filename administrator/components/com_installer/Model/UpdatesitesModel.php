<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Database\ParameterType;

/**
 * Installer Update Sites Model
 *
 * @since  3.4
 */
class UpdatesitesModel extends InstallerModel
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @see     \Joomla\CMS\MVC\Model\ListModel
	 * @since   1.6
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
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

		parent::__construct($config, $factory);
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
	 * @param   array  $eid    Extension ids to un/publish
	 * @param   int    $value  Publish value
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.4
	 *
	 * @throws  \Exception on ACL error
	 */
	public function publish(&$eid = array(), $value = 1)
	{
		if (!Factory::getUser()->authorise('core.edit.state', 'com_installer'))
		{
			throw new \Exception(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 403);
		}

		$result = true;

		// Ensure eid is an array of extension ids
		if (!is_array($eid))
		{
			$eid = array($eid);
		}

		// Get a table object for the extension type
		$table = new \Joomla\CMS\Table\UpdateSite($this->getDbo());

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
	 * @throws  \Exception on ACL error
	 */
	public function delete($ids = array())
	{
		if (!Factory::getUser()->authorise('core.delete', 'com_installer'))
		{
			throw new \Exception(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 403);
		}

		// Ensure eid is an array of extension ids
		if (!is_array($ids))
		{
			$ids = array($ids);
		}

		$db  = $this->getDbo();
		$app = Factory::getApplication();

		$count = 0;

		// Gets the update site names.
		$query = $db->getQuery(true)
			->select($db->quoteName(array('update_site_id', 'name')))
			->from($db->quoteName('#__update_sites'))
			->whereIn($db->quoteName('update_site_id'), $ids);
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
				$app->enqueueMessage(Text::sprintf('COM_INSTALLER_MSG_UPDATESITES_DELETE_CANNOT_DELETE', $updateSitesNames[$id]->name), 'error');
				continue;
			}

			// Delete the update site from all tables.
			$id = (int) $id;

			try
			{
				$query = $db->getQuery(true)
					->delete($db->quoteName('#__update_sites'))
					->where($db->quoteName('update_site_id') . ' = :id')
					->bind(':id', $id, ParameterType::INTEGER);
				$db->setQuery($query);
				$db->execute();

				$query = $db->getQuery(true)
					->delete($db->quoteName('#__update_sites_extensions'))
					->where($db->quoteName('update_site_id') . ' = :id')
					->bind(':id', $id, ParameterType::INTEGER);
				$db->setQuery($query);
				$db->execute();

				$query = $db->getQuery(true)
					->delete($db->quoteName('#__updates'))
					->where($db->quoteName('update_site_id') . ' = :id')
					->bind(':id', $id, ParameterType::INTEGER);
				$db->setQuery($query);
				$db->execute();

				$count++;
			}
			catch (\RuntimeException $e)
			{
				$app->enqueueMessage(Text::sprintf('COM_INSTALLER_MSG_UPDATESITES_DELETE_ERROR', $updateSitesNames[$id]->name, $e->getMessage()), 'error');
			}
		}

		if ($count > 0)
		{
			$app->enqueueMessage(Text::plural('COM_INSTALLER_MSG_UPDATESITES_N_DELETE_UPDATESITES_DELETED', $count), 'message');
		}
	}

	/**
	 * Rebuild update sites tables.
	 *
	 * @return  void
	 *
	 * @since   3.6
	 *
	 * @throws  \Exception on ACL error
	 */
	public function rebuild()
	{
		if (!Factory::getUser()->authorise('core.admin', 'com_installer'))
		{
			throw new \Exception(Text::_('COM_INSTALLER_MSG_UPDATESITES_REBUILD_NOT_PERMITTED'), 403);
		}

		$db  = $this->getDbo();
		$app = Factory::getApplication();

		// Check if Joomla Extension plugin is enabled.
		if (!PluginHelper::isEnabled('extension', 'joomla'))
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('extension_id'))
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
				->where($db->quoteName('element') . ' = ' . $db->quote('joomla'))
				->where($db->quoteName('folder') . ' = ' . $db->quote('extension'));
			$db->setQuery($query);

			$pluginId = (int) $db->loadResult();

			$link = Route::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . $pluginId);
			$app->enqueueMessage(Text::sprintf('COM_INSTALLER_MSG_UPDATESITES_REBUILD_EXTENSION_PLUGIN_NOT_ENABLED', $link), 'error');

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
		$joomlaUpdateSitesIds = $this->getJoomlaUpdateSitesIds(0);

		// Delete from all tables (except joomla core update sites).
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__update_sites'))
			->whereNotIn($db->quoteName('update_site_id'), $joomlaUpdateSitesIds);
		$db->setQuery($query);
		$db->execute();

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__update_sites_extensions'))
			->whereNotIn($db->quoteName('update_site_id'), $joomlaUpdateSitesIds);
		$db->setQuery($query);
		$db->execute();

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__updates'))
			->whereNotIn($db->quoteName('update_site_id'), $joomlaUpdateSitesIds);
		$db->setQuery($query);
		$db->execute();

		$count = 0;

		// Gets Joomla core extension Ids.
		$joomlaCoreExtensionIds = $this->getJoomlaUpdateSitesIds(1);

		// Search for updateservers in manifest files inside the folders to search.
		foreach ($pathsToSearch as $extensionFolderPath)
		{
			$tmpInstaller = new Installer;

			$tmpInstaller->setPath('source', $extensionFolderPath);

			// Main folder manifests (higher priority)
			$parentXmlfiles = Folder::files($tmpInstaller->getPath('source'), '.xml$', false, true);

			// Search for children manifests (lower priority)
			$allXmlFiles    = Folder::files($tmpInstaller->getPath('source'), '.xml$', 1, true);

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
							->extendWhere(
								'AND',
								$db->quoteName('name') . ' = :name',
								$db->quoteName('name') . ' = :pkgname',
								'OR'
							)
							->where($db->quoteName('type') . ' = :type')
							->whereNotIn($db->quoteName('extension_id'), $joomlaCoreExtensionIds)
							->where($db->quoteName('state') . ' != -1')
							->bind(':name', $manifest->name)
							->bind(':pkgname', $manifest->packagename)
							->bind(':type', $manifest['type']);
						$db->setQuery($query);

						$eid = (int) $db->loadResult();

						if ($eid && $manifest->updateservers)
						{
							// Set the manifest object and path
							$tmpInstaller->manifest = $manifest;
							$tmpInstaller->setPath('manifest', $file);

							// Load the extension plugin (if not loaded yet).
							PluginHelper::importPlugin('extension', 'joomla');

							// Fire the onExtensionAfterUpdate
							$app->triggerEvent('onExtensionAfterUpdate', array('installer' => $tmpInstaller, 'eid' => $eid));

							$count++;
						}
					}
				}
			}
		}

		if ($count > 0)
		{
			$app->enqueueMessage(Text::_('COM_INSTALLER_MSG_UPDATESITES_REBUILD_SUCCESS'), 'message');
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_INSTALLER_MSG_UPDATESITES_REBUILD_MESSAGE'), 'message');
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
		$db  = $this->getDbo();

		// Fetch the Joomla core update sites ids and their extension ids. We search for all except the core joomla extension with update sites.
		$query = $db->getQuery(true)
			->select($db->quoteName(array('use.update_site_id', 'e.extension_id')))
			->from($db->quoteName('#__update_sites_extensions', 'use'))
			->join(
				'LEFT',
				$db->quoteName('#__update_sites', 'us'),
				$db->quoteName('us.update_site_id') . ' = ' . $db->quoteName('use.update_site_id')
			)
			->join(
				'LEFT',
				$db->quoteName('#__extensions', 'e'),
				$db->quoteName('e.extension_id') . ' = ' . $db->quoteName('use.extension_id')
			)
			->where('('
				. '(' . $db->quoteName('e.type') . ' = ' . $db->quote('file') . ' AND ' . $db->quoteName('e.element') . ' = ' . $db->quote('joomla') . ')'
				. ' OR (' . $db->quoteName('e.type') . ' = ' . $db->quote('package') . ' AND ' . $db->quoteName('e.element')
				. ' = ' . $db->quote('pkg_en-GB') . ') OR (' . $db->quoteName('e.type') . ' = ' . $db->quote('component')
				. ' AND ' . $db->quoteName('e.element') . ' = ' . $db->quote('com_joomlaupdate') . ')'
				. ')'
			);

		$db->setQuery($query);

		return $db->loadColumn($column);
	}

	/**
	 * Method to get the database query
	 *
	 * @return  \JDatabaseQuery  The database query
	 *
	 * @since   3.4
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(
				[
					$db->quoteName('s.update_site_id'),
					$db->quoteName('s.name', 'update_site_name'),
					$db->quoteName('s.type', 'update_site_type'),
					$db->quoteName('s.location'),
					$db->quoteName('s.enabled'),
					$db->quoteName('e.extension_id'),
					$db->quoteName('e.name'),
					$db->quoteName('e.type'),
					$db->quoteName('e.element'),
					$db->quoteName('e.folder'),
					$db->quoteName('e.client_id'),
					$db->quoteName('e.state'),
					$db->quoteName('e.manifest_cache'),
				]
			)
			->from($db->quoteName('#__update_sites', 's'))
			->join(
				'INNER',
				$db->quoteName('#__update_sites_extensions', 'se'),
				$db->quoteName('se.update_site_id') . ' = ' . $db->quoteName('s.update_site_id')
			)
			->join(
				'INNER',
				$db->quoteName('#__extensions', 'e'),
				$db->quoteName('e.extension_id') . ' = ' . $db->quoteName('se.extension_id')
			)
			->where($db->quoteName('state') . ' = 0');

		// Process select filters.
		$enabled  = $this->getState('filter.enabled');
		$type     = $this->getState('filter.type');
		$clientId = $this->getState('filter.client_id');
		$folder   = $this->getState('filter.folder');

		if ($enabled != '')
		{
			$enabled = (int) $enabled;
			$query->where($db->quoteName('s.enabled') . ' = :enabled')
				->bind(':enabled', $enabled, ParameterType::INTEGER);
		}

		if ($type)
		{
			$query->where($db->quoteName('e.type') . ' = :type')
				->bind(':type', $type);
		}

		if ($clientId != '')
		{
			$clientId = (int) $clientId;
			$query->where($db->quoteName('e.client_id') . ' = :clientid')
				->bind(':clientid', $clientId, ParameterType::INTEGER);
		}

		if ($folder != '' && in_array($type, array('plugin', 'library', '')))
		{
			$folder == '*' ? '' : $folder;
			$query->where($db->quoteName('e.folder') . ' = :folder')
				->bind(':folder', $folder);
		}

		// Process search filter (update site id).
		$search = $this->getState('filter.search');

		if (!empty($search) && stripos($search, 'id:') === 0)
		{
			$uid = (int) substr($search, 3);
			$query->where($db->quoteName('s.update_site_id') . ' = :uid')
				->bind(':uid', $uid, ParameterType::INTEGER);
		}

		// Note: The search for name, ordering and pagination are processed by the parent InstallerModel class (in extension.php).

		return $query;
	}
}
