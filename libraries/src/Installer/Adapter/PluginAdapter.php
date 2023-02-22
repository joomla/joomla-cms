<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer\Adapter;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\Update;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin installer
 *
 * @since  3.1
 */
class PluginAdapter extends InstallerAdapter
{
    /**
     * Group of the plugin
     *
     * @var    string
     * @since  4.2.7
     */
    protected $group;

    /**
     * `<scriptfile>` element of the extension manifest
     *
     * @var    object
     * @since  3.1
     */
    protected $scriptElement = null;

    /**
     * `<files>` element of the old extension manifest
     *
     * @var    object
     * @since  3.1
     */
    protected $oldFiles = null;

    /**
     * Method to check if the extension is already present in the database
     *
     * @return  void
     *
     * @since   3.4
     * @throws  \RuntimeException
     */
    protected function checkExistingExtension()
    {
        try {
            $this->currentExtensionId = $this->extension->find(
                ['type' => $this->type, 'element' => $this->element, 'folder' => $this->group]
            );
        } catch (\RuntimeException $e) {
            // Install failed, roll back changes
            throw new \RuntimeException(
                Text::sprintf(
                    'JLIB_INSTALLER_ABORT_ROLLBACK',
                    Text::_('JLIB_INSTALLER_' . $this->route),
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }
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
        // Copy all necessary files
        if ($this->parent->parseFiles($this->getManifest()->files, -1, $this->oldFiles) === false) {
            throw new \RuntimeException(
                Text::sprintf(
                    'JLIB_INSTALLER_ABORT_PLG_COPY_FILES',
                    Text::_('JLIB_INSTALLER_' . $this->route)
                )
            );
        }

        // If there is a manifest script, let's copy it.
        if ($this->manifest_script) {
            $path['src']  = $this->parent->getPath('source') . '/' . $this->manifest_script;
            $path['dest'] = $this->parent->getPath('extension_root') . '/' . $this->manifest_script;

            if ($this->parent->isOverwrite() || !file_exists($path['dest'])) {
                if (!$this->parent->copyFiles([$path])) {
                    // Install failed, rollback changes
                    throw new \RuntimeException(
                        Text::sprintf(
                            'JLIB_INSTALLER_ABORT_MANIFEST',
                            Text::_('JLIB_INSTALLER_' . $this->route)
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
        // Run the common create code first
        parent::createExtensionRoot();

        // If we're updating at this point when there is always going to be an extension_root find the old XML files
        if ($this->route === 'update') {
            // Create a new installer because findManifest sets stuff; side effects!
            $tmpInstaller = new Installer();
            $tmpInstaller->setDatabase($this->getDatabase());

            // Look in the extension root
            $tmpInstaller->setPath('source', $this->parent->getPath('extension_root'));

            if ($tmpInstaller->findManifest()) {
                $old_manifest   = $tmpInstaller->getManifest();
                $this->oldFiles = $old_manifest->files;
            }
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
        // Clobber any possible pending updates
        /** @var Update $update */
        $update = Table::getInstance('update');
        $uid = $update->find(
            [
                'element' => $this->element,
                'type'    => $this->type,
                'folder'  => $this->group,
            ]
        );

        if ($uid) {
            $update->delete($uid);
        }

        // Lastly, we will copy the manifest file to its appropriate place.
        if ($this->route !== 'discover_install') {
            if (!$this->parent->copyManifest(-1)) {
                // Install failed, rollback changes
                throw new \RuntimeException(
                    Text::sprintf(
                        'JLIB_INSTALLER_ABORT_COPY_SETUP',
                        Text::_('JLIB_INSTALLER_' . strtoupper($this->route))
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
            ->delete('#__schemas')
            ->where('extension_id = :extension_id')
            ->bind(':extension_id', $extensionId, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();

        // Now we will no longer need the plugin object, so let's delete it
        $this->extension->delete($this->extension->extension_id);

        // Remove the plugin's folder
        Folder::delete($this->parent->getPath('extension_root'));

        return true;
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
        if ($element || !$this->getManifest()) {
            return $element;
        }

        // Backward Compatibility
        // @todo Deprecate in future version
        if (!\count($this->getManifest()->files->children())) {
            return $element;
        }

        $type = (string) $this->getManifest()->attributes()->type;

        foreach ($this->getManifest()->files->children() as $file) {
            if ((string) $file->attributes()->$type) {
                $element = (string) $file->attributes()->$type;

                break;
            }
        }

        return $element;
    }

    /**
     * Get the class name for the install adapter script.
     *
     * @return  string  The class name.
     *
     * @since   3.4
     */
    protected function getScriptClassName()
    {
        return 'Plg' . str_replace('-', '', $this->group) . $this->element . 'InstallerScript';
    }

    /**
     * Custom loadLanguage method
     *
     * @param   string  $path  The path where to find language files.
     *
     * @return  void
     *
     * @since   3.1
     */
    public function loadLanguage($path = null)
    {
        $source = $this->parent->getPath('source');

        if (!$source) {
            $this->parent->setPath(
                'source',
                JPATH_PLUGINS . '/' . $this->parent->extension->folder . '/' . $this->parent->extension->element
            );
        }

        $element = $this->getManifest()->files;

        if ($element) {
            $group = strtolower((string) $this->getManifest()->attributes()->group);
            $name = '';

            if (\count($element->children())) {
                foreach ($element->children() as $file) {
                    if ((string) $file->attributes()->plugin) {
                        $name = strtolower((string) $file->attributes()->plugin);
                        break;
                    }
                }
            }

            if ($name) {
                $extension = "plg_{$group}_{$name}";
                $source = $path ?: JPATH_PLUGINS . "/$group/$name";
                $folder = (string) $element->attributes()->folder;

                if ($folder && file_exists("$path/$folder")) {
                    $source = "$path/$folder";
                }

                $this->doLoadLanguage($extension, $source, JPATH_ADMINISTRATOR);
            }
        }
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
        // Parse optional tags -- media and language files for plugins go in admin app
        $this->parent->parseMedia($this->getManifest()->media, 1);
        $this->parent->parseLanguages($this->getManifest()->languages, 1);
    }

    /**
     * Prepares the adapter for a discover_install task
     *
     * @return  void
     *
     * @since   3.4
     */
    public function prepareDiscoverInstall()
    {
        $client       = ApplicationHelper::getClientInfo($this->extension->client_id);
        $basePath     = $client->path . '/plugins/' . $this->extension->folder;
        $manifestPath = $basePath . '/' . $this->extension->element . '/' . $this->extension->element . '.xml';

        $this->parent->manifest = $this->parent->isManifest($manifestPath);
        $this->parent->setPath('manifest', $manifestPath);
        $this->setManifest($this->parent->getManifest());
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
        // Remove the plugin files
        $this->parent->removeFiles($this->getManifest()->files, -1);

        // Remove all media and languages as well
        $this->parent->removeFiles($this->getManifest()->media);
        $this->parent->removeFiles($this->getManifest()->languages, 1);
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
        $this->group = (string) $this->getManifest()->attributes()->group;

        if (empty($this->element) && empty($this->group)) {
            throw new \RuntimeException(
                Text::sprintf(
                    'JLIB_INSTALLER_ABORT_PLG_INSTALL_NO_FILE',
                    Text::_('JLIB_INSTALLER_' . $this->route)
                )
            );
        }

        $this->parent->setPath('extension_root', JPATH_PLUGINS . '/' . $this->group . '/' . $this->element);
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
        // Get the plugin folder so we can properly build the plugin path
        if (trim($this->extension->folder) === '') {
            throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_PLG_UNINSTALL_FOLDER_FIELD_EMPTY'));
        }

        // Set the plugin root path
        $this->parent->setPath('extension_root', JPATH_PLUGINS . '/' . $this->extension->folder . '/' . $this->extension->element);

        $this->parent->setPath('source', $this->parent->getPath('extension_root'));

        $this->parent->findManifest();
        $this->setManifest($this->parent->getManifest());

        if ($this->getManifest()) {
            $this->group = (string) $this->getManifest()->attributes()->group;
        }

        // Attempt to load the language file; might have uninstall strings
        $this->loadLanguage($this->parent->getPath('source'));
    }

    /**
     * Method to store the extension to the database
     *
     * @return  void
     *
     * @since   3.4
     * @throws  \RuntimeException
     */
    protected function storeExtension()
    {
        // Discover installs are stored a little differently
        if ($this->route === 'discover_install') {
            $manifest_details = Installer::parseXMLInstallFile($this->parent->getPath('manifest'));

            $this->extension->manifest_cache = json_encode($manifest_details);
            $this->extension->state          = 0;
            $this->extension->name           = $manifest_details['name'];
            $this->extension->enabled        = 'editors' === $this->extension->folder ? 1 : 0;
            $this->extension->params         = $this->parent->getParams();
            $this->extension->changelogurl   = (string) $this->manifest->changelogurl;

            if (!$this->extension->store()) {
                // Install failed, roll back changes
                throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_PLG_DISCOVER_STORE_DETAILS'));
            }

            return;
        }

        // Was there a plugin with the same name already installed?
        if ($this->currentExtensionId) {
            if (!$this->parent->isOverwrite()) {
                // Install failed, roll back changes
                throw new \RuntimeException(
                    Text::sprintf(
                        'JLIB_INSTALLER_ABORT_ALREADY_EXISTS',
                        Text::_('JLIB_INSTALLER_' . $this->route),
                        $this->name
                    )
                );
            }

            // Load the entry and update the manifest_cache
            $this->extension->load($this->currentExtensionId);

            // Update name
            $this->extension->name = $this->name;

            // Update namespace
            $this->extension->namespace = (string) $this->manifest->namespace;

            // Update changelogurl
            $this->extension->changelogurl = (string) $this->manifest->changelogurl;

            // Update manifest
            $this->extension->manifest_cache = $this->parent->generateManifestCache();

            // Update the manifest cache and name
            $this->extension->store();
        } else {
            // Store in the extensions table (1.6)
            $this->extension->name         = $this->name;
            $this->extension->type         = 'plugin';
            $this->extension->ordering     = 0;
            $this->extension->element      = $this->element;
            $this->extension->folder       = $this->group;
            $this->extension->enabled      = 0;
            $this->extension->protected    = 0;
            $this->extension->access       = 1;
            $this->extension->client_id    = 0;
            $this->extension->params       = $this->parent->getParams();
            $this->extension->changelogurl = $this->changelogurl;

            // Update the manifest cache for the entry
            $this->extension->manifest_cache = $this->parent->generateManifestCache();

            // Editor plugins are published by default
            if ($this->group === 'editors') {
                $this->extension->enabled = 1;
            }

            if (!$this->extension->store()) {
                // Install failed, roll back changes
                throw new \RuntimeException(
                    Text::sprintf(
                        'JLIB_INSTALLER_ABORT_PLG_INSTALL_ROLLBACK',
                        Text::_('JLIB_INSTALLER_' . $this->route),
                        $this->extension->getError()
                    )
                );
            }

            // Since we have created a plugin item, we add it to the installation step stack
            // so that if we have to rollback the changes we can undo it.
            $this->parent->pushStep(['type' => 'extension', 'id' => $this->extension->extension_id]);
        }
    }

    /**
     * Custom discover method
     *
     * @return  array  Extension) list of extensions available
     *
     * @since   3.1
     */
    public function discover()
    {
        $results = [];
        $folder_list = Folder::folders(JPATH_SITE . '/plugins');

        foreach ($folder_list as $folder) {
            $file_list = Folder::files(JPATH_SITE . '/plugins/' . $folder, '\.xml$');

            foreach ($file_list as $file) {
                $manifest_details = Installer::parseXMLInstallFile(JPATH_SITE . '/plugins/' . $folder . '/' . $file);
                $file = File::stripExt($file);

                // Ignore example plugins
                if ($file === 'example' || $manifest_details === false) {
                    continue;
                }

                $element = empty($manifest_details['filename']) ? $file : $manifest_details['filename'];

                $extension = Table::getInstance('extension');
                $extension->set('type', 'plugin');
                $extension->set('client_id', 0);
                $extension->set('element', $element);
                $extension->set('folder', $folder);
                $extension->set('name', $manifest_details['name']);
                $extension->set('state', -1);
                $extension->set('manifest_cache', json_encode($manifest_details));
                $extension->set('params', '{}');
                $results[] = $extension;
            }

            $folder_list = Folder::folders(JPATH_SITE . '/plugins/' . $folder);

            foreach ($folder_list as $plugin_folder) {
                $file_list = Folder::files(JPATH_SITE . '/plugins/' . $folder . '/' . $plugin_folder, '\.xml$');

                foreach ($file_list as $file) {
                    $manifest_details = Installer::parseXMLInstallFile(
                        JPATH_SITE . '/plugins/' . $folder . '/' . $plugin_folder . '/' . $file
                    );
                    $file = File::stripExt($file);

                    if ($file === 'example' || $manifest_details === false) {
                        continue;
                    }

                    $element = empty($manifest_details['filename']) ? $file : $manifest_details['filename'];

                    // Ignore example plugins
                    $extension = Table::getInstance('extension');
                    $extension->set('type', 'plugin');
                    $extension->set('client_id', 0);
                    $extension->set('element', $element);
                    $extension->set('folder', $folder);
                    $extension->set('name', $manifest_details['name']);
                    $extension->set('state', -1);
                    $extension->set('manifest_cache', json_encode($manifest_details));
                    $extension->set('params', '{}');
                    $results[] = $extension;
                }
            }
        }

        return $results;
    }

    /**
     * Refreshes the extension table cache.
     *
     * @return  boolean  Result of operation, true if updated, false on failure.
     *
     * @since   3.1
     */
    public function refreshManifestCache()
    {
        /*
         * Plugins use the extensions table as their primary store
         * Similar to modules and templates, rather easy
         * If it's not in the extensions table we just add it
         */
        $client = ApplicationHelper::getClientInfo($this->parent->extension->client_id);
        $manifestPath = $client->path . '/plugins/' . $this->parent->extension->folder . '/' . $this->parent->extension->element . '/'
            . $this->parent->extension->element . '.xml';
        $this->parent->manifest = $this->parent->isManifest($manifestPath);
        $this->parent->setPath('manifest', $manifestPath);
        $manifest_details = Installer::parseXMLInstallFile($this->parent->getPath('manifest'));
        $this->parent->extension->manifest_cache = json_encode($manifest_details);

        $this->parent->extension->name = $manifest_details['name'];

        if ($this->parent->extension->store()) {
            return true;
        } else {
            Log::add(Text::_('JLIB_INSTALLER_ERROR_PLG_REFRESH_MANIFEST_CACHE'), Log::WARNING, 'jerror');

            return false;
        }
    }
}
