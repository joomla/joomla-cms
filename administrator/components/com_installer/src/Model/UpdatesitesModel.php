<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\UpdateSite as UpdateSiteTable;
use Joomla\Component\Installer\Administrator\Helper\InstallerHelper;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @since   1.6
     * @see     \Joomla\CMS\MVC\Model\ListModel
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'update_site_name',
                'name',
                'client_id',
                'client',
                'client_translated',
                'status',
                'type',
                'type_translated',
                'folder',
                'folder_translated',
                'update_site_id',
                'enabled',
                'supported',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Enable/Disable an extension.
     *
     * @param   array  $eid    Extension ids to un/publish
     * @param   int    $value  Publish value
     *
     * @return  boolean  True on success
     *
     * @throws  \Exception on ACL error
     * @since   3.4
     *
     */
    public function publish(&$eid = [], $value = 1)
    {
        if (!$this->getCurrentUser()->authorise('core.edit.state', 'com_installer')) {
            throw new \Exception(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 403);
        }

        $result = true;

        // Ensure eid is an array of extension ids
        if (!\is_array($eid)) {
            $eid = [$eid];
        }

        // Get a table object for the extension type
        $table = new UpdateSiteTable($this->getDatabase());

        // Enable the update site in the table and store it in the database
        foreach ($eid as $i => $id) {
            $table->load($id);
            $table->enabled = $value;

            if (!$table->store()) {
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
     * @throws  \Exception on ACL error
     * @since   3.6
     *
     */
    public function delete($ids = [])
    {
        if (!$this->getCurrentUser()->authorise('core.delete', 'com_installer')) {
            throw new \Exception(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 403);
        }

        // Ensure eid is an array of extension ids
        if (!\is_array($ids)) {
            $ids = [$ids];
        }

        $db  = $this->getDatabase();
        $app = Factory::getApplication();

        $count = 0;

        // Gets the update site names.
        $query = $db->getQuery(true)
            ->select($db->quoteName(['update_site_id', 'name']))
            ->from($db->quoteName('#__update_sites'))
            ->whereIn($db->quoteName('update_site_id'), $ids);
        $db->setQuery($query);
        $updateSitesNames = $db->loadObjectList('update_site_id');

        // Gets Joomla core update sites Ids.
        $joomlaUpdateSitesIds = $this->getJoomlaUpdateSitesIds(0);

        // Enable the update site in the table and store it in the database
        foreach ($ids as $i => $id) {
            // Don't allow to delete Joomla Core update sites.
            if (\in_array((int) $id, $joomlaUpdateSitesIds)) {
                $app->enqueueMessage(Text::sprintf('COM_INSTALLER_MSG_UPDATESITES_DELETE_CANNOT_DELETE', $updateSitesNames[$id]->name), 'error');
                continue;
            }

            // Delete the update site from all tables.
            try {
                $id    = (int) $id;
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
            } catch (\RuntimeException $e) {
                $app->enqueueMessage(
                    Text::sprintf(
                        'COM_INSTALLER_MSG_UPDATESITES_DELETE_ERROR',
                        $updateSitesNames[$id]->name,
                        $e->getMessage()
                    ),
                    'error'
                );
            }
        }

        if ($count > 0) {
            $app->enqueueMessage(Text::plural('COM_INSTALLER_MSG_UPDATESITES_N_DELETE_UPDATESITES_DELETED', $count), 'message');
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
        $db = $this->getDatabase();

        // Fetch the Joomla core update sites ids and their extension ids. We search for all except the core joomla extension with update sites.
        $query = $db->getQuery(true)
            ->select($db->quoteName(['use.update_site_id', 'e.extension_id']))
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
                . '(' . $db->quoteName('e.type') . ' = ' . $db->quote('file') .
                ' AND ' . $db->quoteName('e.element') . ' = ' . $db->quote('joomla') . ')'
                . ' OR (' . $db->quoteName('e.type') . ' = ' . $db->quote('package') . ' AND ' . $db->quoteName('e.element')
                . ' = ' . $db->quote('pkg_en-GB') . ') OR (' . $db->quoteName('e.type') . ' = ' . $db->quote('component')
                . ' AND ' . $db->quoteName('e.element') . ' = ' . $db->quote('com_joomlaupdate') . ')'
                . ')');

        $db->setQuery($query);

        return $db->loadColumn($column);
    }

    /**
     * Rebuild update sites tables.
     *
     * @return  void
     *
     * @throws  \Exception on ACL error
     * @since   3.6
     *
     */
    public function rebuild(): void
    {
        if (!$this->getCurrentUser()->authorise('core.admin', 'com_installer')) {
            throw new \Exception(Text::_('COM_INSTALLER_MSG_UPDATESITES_REBUILD_NOT_PERMITTED'), 403);
        }

        $db  = $this->getDatabase();
        $app = Factory::getApplication();

        // Check if Joomla Extension plugin is enabled.
        if (!PluginHelper::isEnabled('extension', 'joomla')) {
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

        $clients               = [JPATH_SITE, JPATH_ADMINISTRATOR, JPATH_API];
        $extensionGroupFolders = ['components', 'modules', 'plugins', 'templates', 'language', 'manifests'];

        $pathsToSearch = [];

        // Identifies which folders to search for manifest files.
        foreach ($clients as $clientPath) {
            foreach ($extensionGroupFolders as $extensionGroupFolderName) {
                // Components, modules, plugins, templates, languages and manifest (files, libraries, etc)
                if ($extensionGroupFolderName !== 'plugins') {
                    foreach (glob($clientPath . '/' . $extensionGroupFolderName . '/*', GLOB_NOSORT | GLOB_ONLYDIR) as $extensionFolderPath) {
                        $pathsToSearch[] = $extensionFolderPath;
                    }
                } else {
                    // Plugins (another directory level is needed)
                    foreach (
                        glob(
                            $clientPath . '/' . $extensionGroupFolderName . '/*',
                            GLOB_NOSORT | GLOB_ONLYDIR
                        ) as $pluginGroupFolderPath
                    ) {
                        foreach (glob($pluginGroupFolderPath . '/*', GLOB_NOSORT | GLOB_ONLYDIR) as $extensionFolderPath) {
                            $pathsToSearch[] = $extensionFolderPath;
                        }
                    }
                }
            }
        }

        // Gets Joomla core update sites Ids.
        $joomlaUpdateSitesIds = $this->getJoomlaUpdateSitesIds(0);

        // First backup any custom extra_query for the sites
        $query = $db->getQuery(true)
            ->select('TRIM(' . $db->quoteName('location') . ') AS ' . $db->quoteName('location') . ', ' . $db->quoteName('extra_query'))
            ->from($db->quoteName('#__update_sites'));
        $db->setQuery($query);
        $backupExtraQuerys = $db->loadAssocList('location');

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
        foreach ($pathsToSearch as $extensionFolderPath) {
            $tmpInstaller = new Installer();
            $tmpInstaller->setDatabase($this->getDatabase());

            $tmpInstaller->setPath('source', $extensionFolderPath);

            // Main folder manifests (higher priority)
            $parentXmlfiles = Folder::files($tmpInstaller->getPath('source'), '.xml$', false, true);

            // Search for children manifests (lower priority)
            $allXmlFiles = Folder::files($tmpInstaller->getPath('source'), '.xml$', 1, true);

            // Create a unique array of files ordered by priority
            $xmlfiles = array_unique(array_merge($parentXmlfiles, $allXmlFiles));

            if (!empty($xmlfiles)) {
                foreach ($xmlfiles as $file) {
                    // Is it a valid Joomla installation manifest file?
                    $manifest = $tmpInstaller->isManifest($file);

                    if ($manifest !== null) {
                        /**
                         * Search if the extension exists in the extensions table. Excluding Joomla
                         * core extensions and discovered but not yet installed extensions.
                         */

                        $name    = (string) $manifest->name;
                        $pkgName = (string) $manifest->packagename;
                        $type    = (string) $manifest['type'];

                        $query = $db->getQuery(true)
                            ->select($db->quoteName('extension_id'))
                            ->from($db->quoteName('#__extensions'))
                            ->where(
                                [
                                    $db->quoteName('type') . ' = :type',
                                    $db->quoteName('state') . ' != -1',
                                ]
                            )
                            ->extendWhere(
                                'AND',
                                [
                                    $db->quoteName('name') . ' = :name',
                                    $db->quoteName('name') . ' = :pkgname',
                                ],
                                'OR'
                            )
                            ->whereNotIn($db->quoteName('extension_id'), $joomlaCoreExtensionIds)
                            ->bind(':name', $name)
                            ->bind(':pkgname', $pkgName)
                            ->bind(':type', $type);
                        $db->setQuery($query);

                        $eid = (int) $db->loadResult();

                        if ($eid && $manifest->updateservers) {
                            // Set the manifest object and path
                            $tmpInstaller->manifest = $manifest;
                            $tmpInstaller->setPath('manifest', $file);

                            // Remove last extra_query as we are in a foreach
                            $tmpInstaller->extraQuery = '';

                            if (
                                $tmpInstaller->manifest->updateservers
                                && $tmpInstaller->manifest->updateservers->server
                                && isset($backupExtraQuerys[trim((string) $tmpInstaller->manifest->updateservers->server)])
                            ) {
                                $tmpInstaller->extraQuery = $backupExtraQuerys[trim((string) $tmpInstaller->manifest->updateservers->server)]['extra_query'];
                            }

                            // Load the extension plugin (if not loaded yet).
                            PluginHelper::importPlugin('extension', 'joomla');

                            // Fire the onExtensionAfterUpdate
                            $app->triggerEvent('onExtensionAfterUpdate', ['installer' => $tmpInstaller, 'eid' => $eid]);

                            $count++;
                        }
                    }
                }
            }
        }

        if ($count > 0) {
            $app->enqueueMessage(Text::_('COM_INSTALLER_MSG_UPDATESITES_REBUILD_SUCCESS'), 'message');
        } else {
            $app->enqueueMessage(Text::_('COM_INSTALLER_MSG_UPDATESITES_REBUILD_MESSAGE'), 'message');
        }

        // Flush the system cache to ensure extra_query is correctly loaded next time.
        $this->cleanCache('_system');
    }

    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   4.0.0
     */
    public function getItems()
    {
        $items = parent::getItems();

        array_walk(
            $items,
            static function ($item) {
                $item->downloadKey = InstallerHelper::getDownloadKey($item);
            }
        );

        return $items;
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
        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('search');
        $id .= ':' . $this->getState('client_id');
        $id .= ':' . $this->getState('enabled');
        $id .= ':' . $this->getState('type');
        $id .= ':' . $this->getState('folder');
        $id .= ':' . $this->getState('supported');

        return parent::getStoreId($id);
    }

    /**
     * Method to get the database query
     *
     * @return  QueryInterface  The database query
     *
     * @since   3.4
     */
    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                $db->quoteName(
                    [
                        's.update_site_id',
                        's.name',
                        's.type',
                        's.location',
                        's.enabled',
                        's.checked_out',
                        's.checked_out_time',
                        's.extra_query',
                        'e.extension_id',
                        'e.name',
                        'e.type',
                        'e.element',
                        'e.folder',
                        'e.client_id',
                        'e.state',
                        'e.manifest_cache',
                        'u.name',
                    ],
                    [
                        'update_site_id',
                        'update_site_name',
                        'update_site_type',
                        'location',
                        'enabled',
                        'checked_out',
                        'checked_out_time',
                        'extra_query',
                        'extension_id',
                        'name',
                        'type',
                        'element',
                        'folder',
                        'client_id',
                        'state',
                        'manifest_cache',
                        'editor',
                    ]
                )
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
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u'),
                $db->quoteName('s.checked_out') . ' = ' . $db->quoteName('u.id')
            )
            ->where($db->quoteName('state') . ' = 0');

        // Process select filters.
        $supported = $this->getState('filter.supported');
        $enabled   = $this->getState('filter.enabled', '');
        $type      = $this->getState('filter.type');
        $clientId  = $this->getState('filter.client_id');
        $folder    = $this->getState('filter.folder');

        if ($enabled !== '') {
            $enabled = (int) $enabled;
            $query->where($db->quoteName('s.enabled') . ' = :enabled')
                ->bind(':enabled', $enabled, ParameterType::INTEGER);
        }

        if ($type) {
            $query->where($db->quoteName('e.type') . ' = :type')
                ->bind(':type', $type);
        }

        if ($clientId !== null && $clientId !== '') {
            $clientId = (int) $clientId;
            $query->where($db->quoteName('e.client_id') . ' = :clientId')
                ->bind(':clientId', $clientId, ParameterType::INTEGER);
        }

        if ($folder !== '' && \in_array($type, ['plugin', 'library', ''], true)) {
            $folderForBinding = $folder === '*' ? '' : $folder;
            $query->where($db->quoteName('e.folder') . ' = :folder')
                ->bind(':folder', $folderForBinding);
        }

        // Process search filter (update site id).
        $search = $this->getState('filter.search');

        if (!empty($search) && stripos($search, 'id:') === 0) {
            $uid = (int) substr($search, 3);
            $query->where($db->quoteName('s.update_site_id') . ' = :siteId')
                ->bind(':siteId', $uid, ParameterType::INTEGER);
        }

        if (is_numeric($supported)) {
            switch ($supported) {
                case 1:
                    // Show Update Sites which support Download Keys
                    $supportedIDs = InstallerHelper::getDownloadKeySupportedSites($enabled);
                    break;

                case -1:
                    // Show Update Sites which are missing Download Keys
                    $supportedIDs = InstallerHelper::getDownloadKeyExistsSites(false, $enabled);
                    break;

                case 2:
                    // Show Update Sites which have valid Download Keys
                    $supportedIDs = InstallerHelper::getDownloadKeyExistsSites(true, $enabled);
                    break;
            }

            if (!empty($supportedIDs)) {
                // Don't remove array_values(). whereIn expect a zero-based array.
                $query->whereIn($db->quoteName('s.update_site_id'), array_values($supportedIDs));
            } else {
                // In case of an empty list of IDs we apply a fake filter to effectively return no data
                $query->where($db->quoteName('s.update_site_id') . ' = 0');
            }
        }

        /**
         * Note: The search for name, ordering and pagination are processed by the parent InstallerModel class (in
         * extension.php).
         */

        return $query;
    }
}
