<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer\Adapter;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Asset;
use Joomla\CMS\Table\Extension;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\Update;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Component installer
 *
 * @since  3.1
 */
class ComponentAdapter extends InstallerAdapter
{
    /**
     * The list of current files for the Joomla! CMS administrator that are installed and is read
     * from the manifest on disk in the update area to handle doing a diff
     * and deleting files that are in the old files list and not in the new
     * files list.
     *
     * @var    array
     * @since  3.1
     * */
    protected $oldAdminFiles = null;

    /**
     * The list of current files for the Joomla! CMS API that are installed and is read
     * from the manifest on disk in the update area to handle doing a diff
     * and deleting files that are in the old files list and not in the new
     * files list.
     *
     * @var    array
     * @since  4.0.0
     * */
    protected $oldApiFiles = null;

    /**
     * The list of current files that are installed and is read
     * from the manifest on disk in the update area to handle doing a diff
     * and deleting files that are in the old files list and not in the new
     * files list.
     *
     * @var    array
     * @since  3.1
     * */
    protected $oldFiles = null;

    /**
     * A path to the PHP file that the scriptfile declaration in
     * the manifest refers to.
     *
     * @var    string
     * @since  3.1
     * */
    protected $manifest_script = null;

    /**
     * For legacy installations this is a path to the PHP file that the scriptfile declaration in the
     * manifest refers to.
     *
     * @var    string
     * @since  3.1
     * */
    protected $install_script = null;

    /**
     * Method to check if the extension is present in the filesystem
     *
     * @return  boolean
     *
     * @since   3.4
     * @throws  \RuntimeException
     */
    protected function checkExtensionInFilesystem()
    {
        /*
         * If the component site or admin directory already exists, then we will assume that the component is already
         * installed or another component is using that directory.
         */
        if (
            file_exists($this->parent->getPath('extension_site'))
            || file_exists($this->parent->getPath('extension_administrator'))
            || file_exists($this->parent->getPath('extension_api'))
        ) {
            // Look for an update function or update tag
            $updateElement = $this->getManifest()->update;

            // Upgrade manually set or update function available or update tag detected
            if (
                $updateElement || $this->parent->isUpgrade()
                || ($this->parent->manifestClass && method_exists($this->parent->manifestClass, 'update'))
            ) {
                // If there is a matching extension mark this as an update
                $this->setRoute('update');
            } elseif (!$this->parent->isOverwrite()) {
                // We didn't have overwrite set, find an update function or find an update tag so lets call it safe
                if (file_exists($this->parent->getPath('extension_site'))) {
                    // If the site exists say so.
                    throw new \RuntimeException(
                        Text::sprintf(
                            'JLIB_INSTALLER_ERROR_COMP_INSTALL_DIR_SITE',
                            $this->parent->getPath('extension_site')
                        )
                    );
                }

                if (file_exists($this->parent->getPath('extension_administrator'))) {
                    // If the admin exists say so
                    throw new \RuntimeException(
                        Text::sprintf(
                            'JLIB_INSTALLER_ERROR_COMP_INSTALL_DIR_ADMIN',
                            $this->parent->getPath('extension_administrator')
                        )
                    );
                }

                // If the API exists say so
                throw new \RuntimeException(
                    Text::sprintf(
                        'JLIB_INSTALLER_ERROR_COMP_INSTALL_DIR_API',
                        $this->parent->getPath('extension_api')
                    )
                );
            }
        }

        return false;
    }

    /**
     * Method to copy the extension's base files from the `<files>` tag(s) and the manifest file
     *
     * @return  void
     *
     * @since   3.4
     * @throws  \RuntimeException
     */
    protected function copyBaseFiles()
    {
        // Copy site files
        if ($this->getManifest()->files) {
            if ($this->route === 'update') {
                $result = $this->parent->parseFiles($this->getManifest()->files, 0, $this->oldFiles);
            } else {
                $result = $this->parent->parseFiles($this->getManifest()->files);
            }

            if ($result === false) {
                throw new \RuntimeException(
                    Text::sprintf(
                        'JLIB_INSTALLER_ABORT_COMP_FAIL_SITE_FILES',
                        Text::_('JLIB_INSTALLER_' . strtoupper($this->route))
                    )
                );
            }
        }

        // Copy admin files
        if ($this->getManifest()->administration->files) {
            if ($this->route === 'update') {
                $result = $this->parent->parseFiles($this->getManifest()->administration->files, 1, $this->oldAdminFiles);
            } else {
                $result = $this->parent->parseFiles($this->getManifest()->administration->files, 1);
            }

            if ($result === false) {
                throw new \RuntimeException(
                    Text::sprintf(
                        'JLIB_INSTALLER_ABORT_COMP_FAIL_ADMIN_FILES',
                        Text::_('JLIB_INSTALLER_' . strtoupper($this->route))
                    )
                );
            }
        }

        // Copy API files
        if ($this->getManifest()->api->files) {
            if ($this->route === 'update') {
                $result = $this->parent->parseFiles($this->getManifest()->api->files, 3, $this->oldApiFiles);
            } else {
                $result = $this->parent->parseFiles($this->getManifest()->api->files, 3);
            }

            if ($result === false) {
                throw new \RuntimeException(
                    Text::sprintf(
                        'JLIB_INSTALLER_ABORT_COMP_FAIL_API_FILES',
                        Text::_('JLIB_INSTALLER_' . strtoupper($this->route))
                    )
                );
            }
        }

        // If there is a manifest script, let's copy it.
        if ($this->manifest_script) {
            $path         = [];
            $path['src']  = $this->parent->getPath('source') . '/' . $this->manifest_script;
            $path['dest'] = $this->parent->getPath('extension_administrator') . '/' . $this->manifest_script;

            if ($this->parent->isOverwrite() || !file_exists($path['dest'])) {
                if (!$this->parent->copyFiles([$path])) {
                    throw new \RuntimeException(
                        Text::sprintf(
                            'JLIB_INSTALLER_ABORT_MANIFEST',
                            Text::_('JLIB_INSTALLER_' . strtoupper($this->route))
                        )
                    );
                }
            }
        }
    }

    /**
     * Method to create the extension root path if necessary
     *
     * @return  void
     *
     * @since   3.4
     * @throws  \RuntimeException
     */
    protected function createExtensionRoot()
    {
        // If the component directory does not exist, let's create it
        $created = false;

        if (!file_exists($this->parent->getPath('extension_site'))) {
            if (!$created = Folder::create($this->parent->getPath('extension_site'))) {
                throw new \RuntimeException(
                    Text::sprintf(
                        'JLIB_INSTALLER_ABORT_CREATE_DIRECTORY',
                        Text::_('JLIB_INSTALLER_' . strtoupper($this->route)),
                        $this->parent->getPath('extension_site')
                    )
                );
            }
        }

        /*
         * Since we created the component directory and we will want to remove it if we have to roll back
         * the installation, let's add it to the installation step stack
         */
        if ($created) {
            $this->parent->pushStep(
                [
                    'type' => 'folder',
                    'path' => $this->parent->getPath('extension_site'),
                ]
            );
        }

        // If the component admin directory does not exist, let's create it
        $created = false;

        if (!file_exists($this->parent->getPath('extension_administrator'))) {
            if (!$created = Folder::create($this->parent->getPath('extension_administrator'))) {
                throw new \RuntimeException(
                    Text::sprintf(
                        'JLIB_INSTALLER_ABORT_CREATE_DIRECTORY',
                        Text::_('JLIB_INSTALLER_' . strtoupper($this->route)),
                        $this->parent->getPath('extension_administrator')
                    )
                );
            }
        }

        /*
         * Since we created the component admin directory and we will want to remove it if we have to roll
         * back the installation, let's add it to the installation step stack
         */
        if ($created) {
            $this->parent->pushStep(
                [
                    'type' => 'folder',
                    'path' => $this->parent->getPath('extension_administrator'),
                ]
            );
        }

        // If the component API directory does not exist, let's create it
        $created = false;

        if (!file_exists($this->parent->getPath('extension_api'))) {
            if (!$created = Folder::create($this->parent->getPath('extension_api'))) {
                throw new \RuntimeException(
                    Text::sprintf(
                        'JLIB_INSTALLER_ABORT_CREATE_DIRECTORY',
                        Text::_('JLIB_INSTALLER_' . strtoupper($this->route)),
                        $this->parent->getPath('extension_api')
                    )
                );
            }
        }

        /*
         * Since we created the component API directory and we will want to remove it if we have to roll
         * back the installation, let's add it to the installation step stack
         */
        if ($created) {
            $this->parent->pushStep(
                [
                    'type' => 'folder',
                    'path' => $this->parent->getPath('extension_api'),
                ]
            );
        }
    }

    /**
     * Method to finalise the installation processing
     *
     * @return  void
     *
     * @since   3.4
     * @throws  \RuntimeException
     */
    protected function finaliseInstall()
    {
        /** @var Update $update */
        $update = Table::getInstance('update');

        // Clobber any possible pending updates
        $uid = $update->find(
            [
                'element'   => $this->element,
                'type'      => $this->extension->type,
                'client_id' => 1,
            ]
        );

        if ($uid) {
            $update->delete($uid);
        }

        // We will copy the manifest file to its appropriate place.
        if ($this->route !== 'discover_install') {
            if (!$this->parent->copyManifest()) {
                // Install failed, roll back changes
                throw new \RuntimeException(
                    Text::sprintf(
                        'JLIB_INSTALLER_ABORT_COPY_SETUP',
                        Text::_('JLIB_INSTALLER_' . strtoupper($this->route))
                    )
                );
            }
        }

        // Time to build the admin menus
        if (!$this->_buildAdminMenus($this->extension->extension_id)) {
            Log::add(Text::_('JLIB_INSTALLER_ABORT_COMP_BUILDADMINMENUS_FAILED'), Log::WARNING, 'jerror');
        }

        // Make sure that menu items pointing to the component have correct component id assigned to them.
        // Prevents message "Component 'com_extension' does not exist." after uninstalling / re-installing component.
        if (!$this->_updateMenus($this->extension->extension_id)) {
            Log::add(Text::_('JLIB_INSTALLER_ABORT_COMP_UPDATESITEMENUS_FAILED'), Log::WARNING, 'jerror');
        }

        /** @var Asset $asset */
        $asset = Table::getInstance('Asset');

        // Check if an asset already exists for this extension and create it if not
        if (!$asset->loadByName($this->extension->element)) {
            // Register the component container just under root in the assets table.
            $asset->name      = $this->extension->element;
            $asset->parent_id = 1;
            $asset->rules     = '{}';
            $asset->title     = $this->extension->name;
            $asset->setLocation(1, 'last-child');

            if (!$asset->store()) {
                // Install failed, roll back changes
                throw new \RuntimeException(
                    Text::sprintf(
                        'JLIB_INSTALLER_ABORT_ROLLBACK',
                        Text::_('JLIB_INSTALLER_' . strtoupper($this->route)),
                        $this->extension->getError()
                    )
                );
            }
        }
    }

    /**
     * Method to finalise the uninstallation processing
     *
     * @return  boolean
     *
     * @since   4.0.0
     * @throws  \RuntimeException
     */
    protected function finaliseUninstall(): bool
    {
        $extensionId = $this->extension->extension_id;

        $db = $this->getDatabase();

        // Remove the schema version
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__schemas'))
            ->where($db->quoteName('extension_id') . ' = :extension_id')
            ->bind(':extension_id', $extensionId, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();

        // Remove the component container in the assets table.
        $asset = Table::getInstance('Asset');

        if ($asset->loadByName($this->getElement())) {
            $asset->delete();
        }

        $extensionName             = $this->element;
        $extensionNameWithWildcard = $extensionName . '.%';

        // Remove categories for this component
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__categories'))
            ->where(
                [
                    $db->quoteName('extension') . ' = :extension',
                    $db->quoteName('extension') . ' LIKE :wildcard',
                ],
                'OR'
            )
            ->bind(':extension', $extensionName)
            ->bind(':wildcard', $extensionNameWithWildcard);
        $db->setQuery($query);
        $db->execute();

        // Rebuild the categories for correct lft/rgt
        Table::getInstance('category')->rebuild();

        // Clobber any possible pending updates
        $update = Table::getInstance('update');
        $uid    = $update->find(
            [
                'element'   => $this->extension->element,
                'type'      => 'component',
                'client_id' => 1,
                'folder'    => '',
            ]
        );

        if ($uid) {
            $update->delete($uid);
        }

        // Now we need to delete the installation directories. This is the final step in uninstalling the component.
        if (trim($this->extension->element)) {
            $retval = true;

            // Delete the component site directory
            if (is_dir($this->parent->getPath('extension_site'))) {
                if (!Folder::delete($this->parent->getPath('extension_site'))) {
                    Log::add(Text::_('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_FAILED_REMOVE_DIRECTORY_SITE'), Log::WARNING, 'jerror');
                    $retval = false;
                }
            }

            // Delete the component admin directory
            if (is_dir($this->parent->getPath('extension_administrator'))) {
                if (!Folder::delete($this->parent->getPath('extension_administrator'))) {
                    Log::add(Text::_('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_FAILED_REMOVE_DIRECTORY_ADMIN'), Log::WARNING, 'jerror');
                    $retval = false;
                }
            }

            // Delete the component API directory
            if (is_dir($this->parent->getPath('extension_api'))) {
                if (!Folder::delete($this->parent->getPath('extension_api'))) {
                    Log::add(Text::_('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_FAILED_REMOVE_DIRECTORY_API'), Log::WARNING, 'jerror');
                    $retval = false;
                }
            }

            // Now we will no longer need the extension object, so let's delete it
            $this->extension->delete($this->extension->extension_id);

            return $retval;
        }

        // No component option defined... cannot delete what we don't know about
        Log::add(Text::_('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_NO_OPTION'), Log::WARNING, 'jerror');

        return false;
    }

    /**
     * Get the filtered extension element from the manifest
     *
     * @param   string  $element  Optional element name to be converted
     *
     * @return  string  The filtered element
     *
     * @since   3.4
     */
    public function getElement($element = null)
    {
        $element = parent::getElement($element);

        if (strpos($element, 'com_') !== 0) {
            $element = 'com_' . $element;
        }

        return $element;
    }

    /**
     * Custom loadLanguage method
     *
     * @param   string  $path  The path language files are on.
     *
     * @return  void
     *
     * @since   3.1
     */
    public function loadLanguage($path = null)
    {
        $source = $this->parent->getPath('source');

        switch ($this->parent->extension->client_id) {
            case 0:
                $client = JPATH_SITE;

                break;

            case 1:
                $client = JPATH_ADMINISTRATOR;

                break;

            case 3:
                $client = JPATH_API;

                break;

            default:
                throw new \InvalidArgumentException(
                    \sprintf(
                        'Unsupported client ID %d for component %s',
                        $this->parent->extension->client_id,
                        $this->parent->extension->element
                    )
                );
        }

        if (!$source) {
            $this->parent->setPath('source', $client . '/components/' . $this->parent->extension->element);
        }

        $extension = $this->getElement();
        $source    = $path ?: $client . '/components/' . $extension;

        if ($this->getManifest()->administration->files) {
            $element = $this->getManifest()->administration->files;
        } elseif ($this->getManifest()->api->files) {
            $element = $this->getManifest()->api->files;
        } elseif ($this->getManifest()->files) {
            $element = $this->getManifest()->files;
        } else {
            $element = null;
        }

        if ($element) {
            $folder = (string) $element->attributes()->folder;

            if ($folder && file_exists($path . '/' . $folder)) {
                $source = $path . '/' . $folder;
            }
        }

        $this->doLoadLanguage($extension, $source);
    }

    /**
     * Method to parse optional tags in the manifest
     *
     * @return  void
     *
     * @since   3.4
     */
    protected function parseOptionalTags()
    {
        // Parse optional tags
        $this->parent->parseMedia($this->getManifest()->media);
        $this->parent->parseLanguages($this->getManifest()->languages);
        $this->parent->parseLanguages($this->getManifest()->administration->languages, 1);
    }

    /**
     * Method to parse the queries specified in the `<sql>` tags
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \RuntimeException
     */
    protected function parseQueries()
    {
        parent::parseQueries();

        // We have extra tasks to run for the uninstall path
        if ($this->route === 'uninstall') {
            $this->_removeAdminMenus($this->extension->extension_id);
        }
    }

    /**
     * Prepares the adapter for a discover_install task
     *
     * @return  void
     *
     * @since   3.4
     * @throws  \RuntimeException
     */
    public function prepareDiscoverInstall()
    {
        // Need to find to find where the XML file is since we don't store this normally
        $client                 = ApplicationHelper::getClientInfo($this->extension->client_id);
        $short_element          = str_replace('com_', '', $this->extension->element);
        $manifestPath           = $client->path . '/components/' . $this->extension->element . '/' . $short_element . '.xml';
        $this->parent->manifest = $this->parent->isManifest($manifestPath);
        $this->parent->setPath('manifest', $manifestPath);
        $this->parent->setPath('source', $client->path . '/components/' . $this->extension->element);
        $this->parent->setPath('extension_root', $this->parent->getPath('source'));
        $this->setManifest($this->parent->getManifest());

        $manifest_details                = Installer::parseXMLInstallFile($this->parent->getPath('manifest'));
        $this->extension->manifest_cache = json_encode($manifest_details);
        $this->extension->state          = 0;
        $this->extension->name           = $manifest_details['name'];
        $this->extension->enabled        = 1;
        $this->extension->params         = $this->parent->getParams();

        $stored = false;

        try {
            $this->extension->store();
            $stored = true;
        } catch (\RuntimeException $e) {
            $name    = $this->extension->name;
            $type    = $this->extension->type;
            $element = $this->extension->element;

            // Try to delete existing failed records before retrying
            $db = $this->getDatabase();

            $query = $db->getQuery(true)
                ->select($db->quoteName('extension_id'))
                ->from($db->quoteName('#__extensions'))
                ->where(
                    [
                        $db->quoteName('name') . ' = :name',
                        $db->quoteName('type') . ' = :type',
                        $db->quoteName('element') . ' = :element',
                    ]
                )
                ->bind(':name', $name)
                ->bind(':type', $type)
                ->bind(':element', $element);

            $db->setQuery($query);

            $extension_ids = $db->loadColumn();

            if (!empty($extension_ids)) {
                foreach ($extension_ids as $eid) {
                    // Remove leftover admin menus for this extension ID
                    $this->_removeAdminMenus($eid);

                    // Remove the extension record itself
                    /** @var Extension $extensionTable */
                    $extensionTable = Table::getInstance('extension');
                    $extensionTable->delete($eid);
                }
            }
        }

        if (!$stored) {
            try {
                $this->extension->store();
            } catch (\RuntimeException $e) {
                throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_COMP_DISCOVER_STORE_DETAILS'), $e->getCode(), $e);
            }
        }
    }

    /**
     * Removes this extension's files
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \RuntimeException
     */
    protected function removeExtensionFiles()
    {
        // Let's remove those language files and media in the JROOT/images/ folder that are associated with the component we are uninstalling
        $this->parent->removeFiles($this->getManifest()->media);
        $this->parent->removeFiles($this->getManifest()->languages);
        $this->parent->removeFiles($this->getManifest()->administration->languages, 1);
    }

    /**
     * Method to do any prechecks and setup the install paths for the extension
     *
     * @return  void
     *
     * @since   3.4
     * @throws  \RuntimeException
     */
    protected function setupInstallPaths()
    {
        // Set the installation target paths
        $this->parent->setPath('extension_site', Path::clean(JPATH_SITE . '/components/' . $this->element));
        $this->parent->setPath('extension_administrator', Path::clean(JPATH_ADMINISTRATOR . '/components/' . $this->element));
        $this->parent->setPath('extension_api', Path::clean(JPATH_API . '/components/' . $this->element));

        // Copy the admin path as it's used as a common base
        $this->parent->setPath('extension_root', $this->parent->getPath('extension_administrator'));

        // Make sure that we have an admin element
        if (!$this->getManifest()->administration) {
            throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_COMP_INSTALL_ADMIN_ELEMENT'));
        }
    }

    /**
     * Method to do any prechecks and setup the uninstall job
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function setupUninstall()
    {
        // Get the admin and site paths for the component
        $this->parent->setPath('extension_administrator', Path::clean(JPATH_ADMINISTRATOR . '/components/' . $this->extension->element));
        $this->parent->setPath('extension_api', Path::clean(JPATH_API . '/components/' . $this->extension->element));
        $this->parent->setPath('extension_site', Path::clean(JPATH_SITE . '/components/' . $this->extension->element));

        // Copy the admin path as it's used as a common base
        $this->parent->setPath('extension_root', $this->parent->getPath('extension_administrator'));

        // Find and load the XML install file for the component
        $this->parent->setPath('source', $this->parent->getPath('extension_administrator'));

        // Get the package manifest object
        // We do findManifest to avoid problem when uninstalling a list of extension: getManifest cache its manifest file
        $this->parent->findManifest();
        $this->setManifest($this->parent->getManifest());

        if (!$this->getManifest()) {
            // Make sure we delete the folders if no manifest exists
            Folder::delete($this->parent->getPath('extension_administrator'));
            Folder::delete($this->parent->getPath('extension_api'));
            Folder::delete($this->parent->getPath('extension_site'));

            // Remove the menu
            $this->_removeAdminMenus($this->extension->extension_id);

            // Raise a warning
            throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_ERRORREMOVEMANUALLY'));
        }

        // Attempt to load the admin language file; might have uninstall strings
        $this->loadLanguage(JPATH_ADMINISTRATOR . '/components/' . $this->extension->element);
    }

    /**
     * Method to setup the update routine for the adapter
     *
     * @return  void
     *
     * @since   3.4
     */
    protected function setupUpdates()
    {
        // Hunt for the original XML file
        $old_manifest = null;

        // Use a temporary instance due to side effects; start in the administrator first
        $tmpInstaller = new Installer();
        $tmpInstaller->setDatabase($this->getDatabase());
        $tmpInstaller->setPath('source', $this->parent->getPath('extension_administrator'));

        if (!$tmpInstaller->findManifest()) {
            // Then the site
            $tmpInstaller->setPath('source', $this->parent->getPath('extension_site'));

            if ($tmpInstaller->findManifest()) {
                $old_manifest = $tmpInstaller->getManifest();
            }
        } else {
            $old_manifest = $tmpInstaller->getManifest();
        }

        if ($old_manifest) {
            $this->oldAdminFiles = $old_manifest->administration->files;
            $this->oldApiFiles   = $old_manifest->api->files;
            $this->oldFiles      = $old_manifest->files;
        }
    }

    /**
     * Method to store the extension to the database
     *
     * @param   bool  $deleteExisting  Should I try to delete existing records of the same component?
     *
     * @return  void
     *
     * @since   3.4
     * @throws  \RuntimeException
     */
    protected function storeExtension($deleteExisting = false)
    {
        // The extension is stored during prepareDiscoverInstall for discover installs
        if ($this->route === 'discover_install') {
            return;
        }

        // Add or update an entry to the extension table
        $this->extension->name         = $this->name;
        $this->extension->type         = 'component';
        $this->extension->element      = $this->element;
        $this->extension->changelogurl = $this->changelogurl;

        // If we are told to delete existing extension entries then do so.
        if ($deleteExisting) {
            $name    = $this->extension->name;
            $type    = $this->extension->type;
            $element = $this->extension->element;

            // Try to delete existing failed records before retrying
            $db = $this->getDatabase();

            $query = $db->getQuery(true)
                ->select($db->quoteName('extension_id'))
                ->from($db->quoteName('#__extensions'))
                ->where(
                    [
                        $db->quoteName('name') . ' = :name',
                        $db->quoteName('type') . ' = :type',
                        $db->quoteName('element') . ' = :element',
                    ]
                )
                ->bind(':name', $name)
                ->bind(':type', $type)
                ->bind(':element', $element);

            $db->setQuery($query);

            $extension_ids = $db->loadColumn();

            if (!empty($extension_ids)) {
                foreach ($extension_ids as $eid) {
                    // Remove leftover admin menus for this extension ID
                    $this->_removeAdminMenus($eid);

                    // Remove the extension record itself
                    /** @var Extension $extensionTable */
                    $extensionTable = Table::getInstance('extension');
                    $extensionTable->delete($eid);
                }
            }
        }

        // Namespace is optional
        if (isset($this->manifest->namespace)) {
            $this->extension->namespace = (string) $this->manifest->namespace;
        }

        // If there is not already a row, generate a heap of defaults
        if (!$this->currentExtensionId) {
            $this->extension->folder    = '';
            $this->extension->enabled   = 1;
            $this->extension->protected = 0;
            $this->extension->access    = 0;
            $this->extension->client_id = 1;
            $this->extension->params    = $this->parent->getParams();
        }

        $this->extension->manifest_cache = $this->parent->generateManifestCache();

        $couldStore = $this->extension->store();

        if (!$couldStore && $deleteExisting) {
            // Install failed, roll back changes
            throw new \RuntimeException(
                Text::sprintf(
                    'JLIB_INSTALLER_ABORT_COMP_INSTALL_ROLLBACK',
                    $this->extension->getError()
                )
            );
        }

        if (!$couldStore && !$deleteExisting) {
            // Maybe we have a failed installation (e.g. timeout). Let's retry after deleting old records.
            $this->storeExtension(true);
        }
    }

    /**
     * Method to build menu database entries for a component
     *
     * @param   int|null  $componentId  The component ID for which I'm building menus
     *
     * @return  boolean  True if successful
     *
     * @since   3.1
     */
    protected function _buildAdminMenus($componentId = null)
    {
        $db     = $this->getDatabase();
        $option = $this->element;

        // If a component exists with this option in the table within the protected menutype 'main' then we don't need to add menus
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('m.id'),
                    $db->quoteName('e.extension_id'),
                ]
            )
            ->from($db->quoteName('#__menu', 'm'))
            ->join('LEFT', $db->quoteName('#__extensions', 'e'), $db->quoteName('m.component_id') . ' = ' . $db->quoteName('e.extension_id'))
            ->where(
                [
                    $db->quoteName('m.parent_id') . ' = 1',
                    $db->quoteName('m.client_id') . ' = 1',
                    $db->quoteName('m.menutype') . ' = ' . $db->quote('main'),
                    $db->quoteName('e.element') . ' = :element',
                ]
            )
            ->bind(':element', $option);

        $db->setQuery($query);

        // In case of a failed installation (e.g. timeout error) we may have duplicate menu item and extension records.
        $componentrows = $db->loadObjectList();

        // Check if menu items exist
        if (!empty($componentrows)) {
            // Don't do anything if overwrite has not been enabled
            if (!$this->parent->isOverwrite()) {
                return true;
            }

            // Remove all menu items
            foreach ($componentrows as $componentrow) {
                // Remove existing menu items if overwrite has been enabled
                if ($option) {
                    // If something goes wrong, there's no way to rollback @todo: Search for better solution
                    $this->_removeAdminMenus($componentrow->extension_id);
                }
            }
        }

        // Only try to detect the component ID if it's not provided
        if (empty($componentId)) {
            // Lets find the extension id
            $query->clear()
                ->select($db->quoteName('e.extension_id'))
                ->from($db->quoteName('#__extensions', 'e'))
                ->where(
                    [
                        $db->quoteName('e.type') . ' = ' . $db->quote('component'),
                        $db->quoteName('e.element') . ' = :element',
                    ]
                )
                ->bind(':element', $option);

            $db->setQuery($query);
            $componentId = $db->loadResult();
        }

        // Ok, now its time to handle the menus.  Start with the component root menu, then handle submenus.
        $menuElement = $this->getManifest()->administration->menu;

        // Just do not create the menu if $menuElement not exist
        if (!$menuElement) {
            return true;
        }

        // If the menu item is hidden do nothing more, just return
        if (\in_array((string) $menuElement['hidden'], ['true', 'hidden'])) {
            return true;
        }

        // Let's figure out what the menu item data should look like
        $data = [];

        // I have a menu element, use this information
        $data['menutype']     = 'main';
        $data['client_id']    = 1;
        $data['title']        = (string) trim($menuElement);
        $data['alias']        = (string) $menuElement;
        $data['type']         = 'component';
        $data['published']    = 1;
        $data['parent_id']    = 1;
        $data['component_id'] = $componentId;
        $data['img']          = ((string) $menuElement->attributes()->img) ?: 'class:component';
        $data['home']         = 0;
        $data['path']         = '';
        $data['params']       = '';

        if ($params = $menuElement->params) {
            // Pass $params through Registry to convert to JSON.
            $params         = new Registry($params);
            $data['params'] = $params->toString();
        }

        // Set the menu link
        $request = [];

        if ((string) $menuElement->attributes()->task) {
            $request[] = 'task=' . $menuElement->attributes()->task;
        }

        if ((string) $menuElement->attributes()->view) {
            $request[] = 'view=' . $menuElement->attributes()->view;
        }

        $qstring      = \count($request) ? '&' . implode('&', $request) : '';
        $data['link'] = 'index.php?option=' . $option . $qstring;

        // Try to create the menu item in the database
        $parent_id = $this->_createAdminMenuItem($data, 1);

        if ($parent_id === false) {
            return false;
        }

        /*
         * Process SubMenus
         */

        if (!$this->getManifest()->administration->submenu) {
            // No submenu? We're done.
            return true;
        }

        foreach ($this->getManifest()->administration->submenu->menu as $child) {
            $data                 = [];
            $data['menutype']     = 'main';
            $data['client_id']    = 1;
            $data['title']        = (string) trim($child);
            $data['alias']        = ((string) $child->attributes()->alias) ?: (string) $child;
            $data['type']         = ((string) $child->attributes()->type) ?: 'component';
            $data['published']    = 1;
            $data['parent_id']    = $parent_id;
            $data['component_id'] = $componentId;
            $data['img']          = ((string) $child->attributes()->img) ?: 'class:component';
            $data['home']         = 0;
            $data['params']       = '';

            if ($params = $child->params) {
                // Pass $params through Registry to convert to JSON.
                $params         = new Registry($params);
                $data['params'] = $params->toString();
            }

            // Set the sub menu link
            if ((string) $child->attributes()->link) {
                $data['link'] = 'index.php?' . $child->attributes()->link;
            } else {
                $request = [];

                if ((string) $child->attributes()->act) {
                    $request[] = 'act=' . $child->attributes()->act;
                }

                if ((string) $child->attributes()->task) {
                    $request[] = 'task=' . $child->attributes()->task;
                }

                if ((string) $child->attributes()->controller) {
                    $request[] = 'controller=' . $child->attributes()->controller;
                }

                if ((string) $child->attributes()->view) {
                    $request[] = 'view=' . $child->attributes()->view;
                }

                if ((string) $child->attributes()->layout) {
                    $request[] = 'layout=' . $child->attributes()->layout;
                }

                if ((string) $child->attributes()->sub) {
                    $request[] = 'sub=' . $child->attributes()->sub;
                }

                $qstring      = \count($request) ? '&' . implode('&', $request) : '';
                $data['link'] = 'index.php?option=' . $option . $qstring;
            }

            $submenuId = $this->_createAdminMenuItem($data, $parent_id);

            if ($submenuId === false) {
                return false;
            }

            /*
             * Since we have created a menu item, we add it to the installation step stack
             * so that if we have to rollback the changes we can undo it.
             */
            $this->parent->pushStep(['type' => 'menu', 'id' => $componentId]);
        }

        return true;
    }

    /**
     * Method to remove admin menu references to a component
     *
     * @param   int  $id  The ID of the extension whose admin menus will be removed
     *
     * @return  boolean  True if successful.
     *
     * @throws  \Exception
     *
     * @since   3.1
     */
    protected function _removeAdminMenus($id)
    {
        $db = $this->getDatabase();

        /** @var  \Joomla\CMS\Table\Menu  $table */
        $table = Table::getInstance('menu');

        // Get the ids of the menu items
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__menu'))
            ->where(
                [
                    $db->quoteName('client_id') . ' = 1',
                    $db->quoteName('menutype') . ' = ' . $db->quote('main'),
                    $db->quoteName('component_id') . ' = :id',
                ]
            )
            ->bind(':id', $id, ParameterType::INTEGER);

        $db->setQuery($query);

        $ids    = $db->loadColumn();
        $result = true;

        // Check for error
        if (!empty($ids)) {
            // Iterate the items to delete each one.
            foreach ($ids as $menuid) {
                if (!$table->delete((int) $menuid, false)) {
                    Factory::getApplication()->enqueueMessage($table->getError(), 'error');

                    $result = false;
                }
            }

            // Rebuild the whole tree
            $table->rebuild();
        }

        return $result;
    }

    /**
     * Method to update menu database entries for a component in case the component has been uninstalled before.
     * NOTE: This will not update admin menus. Use _updateMenus() instead to update admin menus ase well.
     *
     * @param   int|null  $componentId  The component ID.
     *
     * @return  boolean  True if successful
     *
     * @since   3.4.2
     */
    protected function _updateSiteMenus($componentId = null)
    {
        return $this->_updateMenus($componentId, 0);
    }

    /**
     * Method to update menu database entries for a component in case if the component has been uninstalled before.
     *
     * @param   int|null  $componentId  The component ID.
     * @param   int       $clientId     The client id
     *
     * @return  boolean  True if successful
     *
     * @since   3.7.0
     */
    protected function _updateMenus($componentId, $clientId = null)
    {
        $db        = $this->getDatabase();
        $option    = $this->element;
        $link      = 'index.php?option=' . $option;
        $linkMatch = 'index.php?option=' . $option . '&%';

        // Update all menu items which contain 'index.php?option=com_extension' or 'index.php?option=com_extension&...'
        // to use the new component id.
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__menu'))
            ->set($db->quoteName('component_id') . ' = :componentId')
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
            ->extendWhere(
                'AND',
                [
                    $db->quoteName('link') . ' LIKE :link',
                    $db->quoteName('link') . ' LIKE :linkMatch',
                ],
                'OR'
            )
            ->bind(':componentId', $componentId, ParameterType::INTEGER)
            ->bind(':link', $link)
            ->bind(':linkMatch', $linkMatch);

        if (isset($clientId)) {
            $query->where($db->quoteName('client_id') . ' = :clientId')
                ->bind(':clientId', $clientId, ParameterType::INTEGER);
        }

        try {
            $db->setQuery($query);
            $db->execute();
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }

    /**
     * Custom rollback method
     * - Roll back the component menu item
     *
     * @param   array  $step  Installation step to rollback.
     *
     * @return  boolean  True on success
     *
     * @throws  \Exception
     *
     * @since   3.1
     */
    protected function _rollback_menu($step)
    {
        return $this->_removeAdminMenus($step['id']);
    }

    /**
     * Discover unregistered extensions.
     *
     * @return  array  A list of extensions.
     *
     * @since   3.1
     */
    public function discover()
    {
        $results          = [];
        $site_components  = Folder::folders(JPATH_SITE . '/components');
        $admin_components = Folder::folders(JPATH_ADMINISTRATOR . '/components');
        $api_components   = Folder::folders(JPATH_API . '/components');

        foreach ($site_components as $component) {
            if (file_exists(JPATH_SITE . '/components/' . $component . '/' . str_replace('com_', '', $component) . '.xml')) {
                $manifest_details = Installer::parseXMLInstallFile(
                    JPATH_SITE . '/components/' . $component . '/' . str_replace('com_', '', $component) . '.xml'
                );
                $extension                 = Table::getInstance('extension');
                $extension->type           = 'component';
                $extension->client_id      = 0;
                $extension->element        = $component;
                $extension->folder         = '';
                $extension->name           = $component;
                $extension->state          = -1;
                $extension->manifest_cache = json_encode($manifest_details);
                $extension->params         = '{}';

                $results[] = $extension;
            }
        }

        foreach ($admin_components as $component) {
            if (file_exists(JPATH_ADMINISTRATOR . '/components/' . $component . '/' . str_replace('com_', '', $component) . '.xml')) {
                $manifest_details = Installer::parseXMLInstallFile(
                    JPATH_ADMINISTRATOR . '/components/' . $component . '/' . str_replace('com_', '', $component) . '.xml'
                );
                $extension                 = Table::getInstance('extension');
                $extension->type           = 'component';
                $extension->client_id      = 1;
                $extension->element        = $component;
                $extension->folder         = '';
                $extension->name           = $component;
                $extension->state          = -1;
                $extension->manifest_cache = json_encode($manifest_details);
                $extension->params         = '{}';
                $results[]                 = $extension;
            }
        }

        foreach ($api_components as $component) {
            if (file_exists(JPATH_API . '/components/' . $component . '/' . str_replace('com_', '', $component) . '.xml')) {
                $manifest_details = Installer::parseXMLInstallFile(
                    JPATH_API . '/components/' . $component . '/' . str_replace('com_', '', $component) . '.xml'
                );
                $extension                 = Table::getInstance('extension');
                $extension->type           = 'component';
                $extension->client_id      = 3;
                $extension->element        = $component;
                $extension->folder         = '';
                $extension->name           = $component;
                $extension->state          = -1;
                $extension->manifest_cache = json_encode($manifest_details);
                $extension->params         = '{}';
                $results[]                 = $extension;
            }
        }

        return $results;
    }

    /**
     * Refreshes the extension table cache
     *
     * @return  boolean  Result of operation, true if updated, false on failure
     *
     * @since   3.1
     */
    public function refreshManifestCache()
    {
        // Need to find to find where the XML file is since we don't store this normally
        $client                 = ApplicationHelper::getClientInfo($this->parent->extension->client_id);
        $short_element          = str_replace('com_', '', $this->parent->extension->element);
        $manifestPath           = $client->path . '/components/' . $this->parent->extension->element . '/' . $short_element . '.xml';
        $this->parent->manifest = $this->parent->isManifest($manifestPath);
        $this->parent->setPath('manifest', $manifestPath);

        $manifest_details                        = Installer::parseXMLInstallFile($this->parent->getPath('manifest'));
        $this->parent->extension->manifest_cache = json_encode($manifest_details);
        $this->parent->extension->name           = $manifest_details['name'];

        // Namespace is optional
        if (isset($manifest_details['namespace'])) {
            $this->parent->extension->namespace = $manifest_details['namespace'];
        }

        try {
            return $this->parent->extension->store();
        } catch (\RuntimeException $e) {
            Log::add(Text::_('JLIB_INSTALLER_ERROR_COMP_REFRESH_MANIFEST_CACHE'), Log::WARNING, 'jerror');

            return false;
        }
    }

    /**
     * Creates the menu item in the database. If the item already exists it tries to remove it and create it afresh.
     *
     * @param   array    &$data     The menu item data to create
     * @param   integer  $parentId  The parent menu item ID
     *
     * @return  boolean|integer  Menu item ID on success, false on failure
     *
     * @throws  \Exception
     *
     * @since   3.1
     */
    protected function _createAdminMenuItem(array &$data, $parentId)
    {
        $db = $this->getDatabase();

        /** @var  \Joomla\CMS\Table\Menu  $table */
        $table  = Table::getInstance('menu');

        try {
            $table->setLocation($parentId, 'last-child');
        } catch (\InvalidArgumentException $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jerror');

            return false;
        }

        if (!$table->bind($data) || !$table->check() || !$table->store()) {
            $menutype     = $data['menutype'];
            $link         = $data['link'];
            $type         = $data['type'];
            $menuParentId = $data['parent_id'];
            $home         = $data['home'];

            // The menu item already exists. Delete it and retry instead of throwing an error.
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__menu'))
                ->where(
                    [
                        $db->quoteName('menutype') . ' = :menutype',
                        $db->quoteName('client_id') . ' = 1',
                        $db->quoteName('link') . ' = :link',
                        $db->quoteName('type') . ' = :type',
                        $db->quoteName('parent_id') . ' = :parent_id',
                        $db->quoteName('home') . ' = :home',
                    ]
                )
                ->bind(':menutype', $menutype)
                ->bind(':link', $link)
                ->bind(':type', $type)
                ->bind(':parent_id', $menuParentId, ParameterType::INTEGER)
                ->bind(':home', $home, ParameterType::BOOLEAN);

            $db->setQuery($query);
            $menu_id = $db->loadResult();

            if (!$menu_id) {
                // Oops! Could not get the menu ID. Go back and rollback changes.
                Factory::getApplication()->enqueueMessage($table->getError(), 'error');

                return false;
            }

            /** @var  \Joomla\CMS\Table\Menu $temporaryTable */
            $temporaryTable = Table::getInstance('menu');
            $temporaryTable->delete($menu_id, true);
            $temporaryTable->load($parentId);
            $temporaryTable->rebuild($parentId, $temporaryTable->lft, $temporaryTable->level, $temporaryTable->path);

            // Retry creating the menu item
            $table->setLocation($parentId, 'last-child');

            if (!$table->bind($data) || !$table->check() || !$table->store()) {
                // Install failed, warn user and rollback changes
                Factory::getApplication()->enqueueMessage($table->getError(), 'error');

                return false;
            }
        }

        return $table->id;
    }
}
