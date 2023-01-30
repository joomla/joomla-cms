<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer\Adapter;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\Manifest\LibraryManifest;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\Update;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Library installer
 *
 * @since  3.1
 */
class LibraryAdapter extends InstallerAdapter
{
    /**
     * Method to check if the extension is present in the filesystem, flags the route as update if so
     *
     * @return  void
     *
     * @since   3.4
     * @throws  \RuntimeException
     */
    protected function checkExtensionInFilesystem()
    {
        if ($this->currentExtensionId) {
            // Already installed, can we upgrade?
            if ($this->parent->isOverwrite() || $this->parent->isUpgrade()) {
                // We can upgrade, so uninstall the old one

                // We don't want to compromise this instance!
                $installer = new Installer();
                $installer->setDatabase($this->getDatabase());
                $installer->setPackageUninstall(true);
                $installer->uninstall('library', $this->currentExtensionId);

                // Clear the cached data
                $this->currentExtensionId = null;
                $this->extension = Table::getInstance('Extension', 'JTable', ['dbo' => $this->getDatabase()]);

                // From this point we'll consider this an update
                $this->setRoute('update');
            } else {
                // Abort the install, no upgrade possible
                throw new \RuntimeException(Text::_('JLIB_INSTALLER_ABORT_LIB_INSTALL_ALREADY_INSTALLED'));
            }
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
        if ($this->parent->parseFiles($this->getManifest()->files, -1) === false) {
            throw new \RuntimeException(Text::sprintf('JLIB_INSTALLER_ABORT_LIB_COPY_FILES', $this->element));
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
        $uid    = $update->find(
            [
                'element' => $this->element,
                'type'    => $this->type,
            ]
        );

        if ($uid) {
            $update->delete($uid);
        }

        // Lastly, we will copy the manifest file to its appropriate place.
        if ($this->route !== 'discover_install') {
            $manifest         = [];
            $manifest['src']  = $this->parent->getPath('manifest');
            $manifest['dest'] = JPATH_MANIFESTS . '/libraries/' . $this->element . '.xml';

            $destFolder = \dirname($manifest['dest']);

            if (!is_dir($destFolder) && !@mkdir($destFolder)) {
                // Install failed, rollback changes
                throw new \RuntimeException(
                    Text::sprintf(
                        'JLIB_INSTALLER_ABORT_COPY_SETUP',
                        Text::_('JLIB_INSTALLER_' . strtoupper($this->route))
                    )
                );
            }

            if (!$this->parent->copyFiles([$manifest], true)) {
                // Install failed, rollback changes
                throw new \RuntimeException(
                    Text::sprintf(
                        'JLIB_INSTALLER_ABORT_COPY_SETUP',
                        Text::_('JLIB_INSTALLER_' . strtoupper($this->route))
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
                                Text::_('JLIB_INSTALLER_' . strtoupper($this->route))
                            )
                        );
                    }
                }
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

        // Clobber any possible pending updates
        $update = Table::getInstance('update');
        $uid    = $update->find(
            [
                'element' => $this->extension->element,
                'type'    => $this->type,
            ]
        );

        if ($uid) {
            $update->delete($uid);
        }

        $this->extension->delete();

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
        if (!$element) {
            $element  = (string) $this->getManifest()->libraryname;
        }

        return $element;
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
            $this->parent->setPath('source', JPATH_PLATFORM . '/' . $this->getElement());
        }

        $extension   = 'lib_' . str_replace('/', '_', $this->getElement());
        $librarypath = (string) $this->getManifest()->libraryname;
        $source      = $path ?: JPATH_PLATFORM . '/' . $librarypath;

        $this->doLoadLanguage($extension, $source, JPATH_SITE);
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
        $this->parent->parseLanguages($this->getManifest()->languages);
        $this->parent->parseMedia($this->getManifest()->media);
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
        $manifestPath           = JPATH_MANIFESTS . '/libraries/' . $this->extension->element . '.xml';
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
        $this->parent->removeFiles($this->getManifest()->files, -1);
        File::delete(JPATH_MANIFESTS . '/libraries/' . $this->extension->element . '.xml');

        // @todo: Change this so it walked up the path backwards so we clobber multiple empties
        // If the folder is empty, let's delete it
        if (Folder::exists($this->parent->getPath('extension_root'))) {
            if (is_dir($this->parent->getPath('extension_root'))) {
                $files = Folder::files($this->parent->getPath('extension_root'));

                if (!\count($files)) {
                    Folder::delete($this->parent->getPath('extension_root'));
                }
            }
        }

        $this->parent->removeFiles($this->getManifest()->media);
        $this->parent->removeFiles($this->getManifest()->languages);

        $elementParts = explode('/', $this->extension->element);

        // Delete empty vendor folders
        if (2 === \count($elementParts)) {
            $folders = Folder::folders(JPATH_PLATFORM . '/' . $elementParts[0]);

            if (empty($folders)) {
                Folder::delete(JPATH_MANIFESTS . '/libraries/' . $elementParts[0]);
                Folder::delete(JPATH_PLATFORM . '/' . $elementParts[0]);
            }
        }
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
        $group = (string) $this->getManifest()->libraryname;

        if (!$group) {
            throw new \RuntimeException(Text::_('JLIB_INSTALLER_ABORT_LIB_INSTALL_NOFILE'));
        }

        // Don't install libraries which would override core folders
        $restrictedFolders = ['php-encryption', 'phpass', 'src', 'vendor'];

        if (in_array($group, $restrictedFolders)) {
            throw new \RuntimeException(Text::_('JLIB_INSTALLER_ABORT_LIB_INSTALL_CORE_FOLDER'));
        }

        $this->parent->setPath('extension_root', JPATH_PLATFORM . '/' . implode(DIRECTORY_SEPARATOR, explode('/', $group)));
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
        $manifestFile = JPATH_MANIFESTS . '/libraries/' . $this->extension->element . '.xml';

        // Because libraries may not have their own folders we cannot use the standard method of finding an installation manifest
        if (!file_exists($manifestFile)) {
            // Remove this row entry since its invalid
            $this->extension->delete($this->extension->extension_id);

            throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_LIB_UNINSTALL_INVALID_NOTFOUND_MANIFEST'));
        }

        $manifest = new LibraryManifest($manifestFile);

        // Set the library root path
        $this->parent->setPath('extension_root', JPATH_PLATFORM . '/' . $manifest->libraryname);

        // Set the source path to the library root, the manifest script may be found
        $this->parent->setPath('source', $this->parent->getPath('extension_root'));

        $xml = simplexml_load_file($manifestFile);

        // If we cannot load the XML file return null
        if (!$xml) {
            throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_LIB_UNINSTALL_LOAD_MANIFEST'));
        }

        // Check for a valid XML root tag.
        if ($xml->getName() !== 'extension') {
            throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_LIB_UNINSTALL_INVALID_MANIFEST'));
        }

        $this->setManifest($xml);

        // Attempt to load the language file; might have uninstall strings
        $this->loadLanguage();
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
            $this->extension->enabled        = 1;
            $this->extension->params         = $this->parent->getParams();

            if (!$this->extension->store()) {
                // Install failed, roll back changes
                throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_LIB_DISCOVER_STORE_DETAILS'));
            }

            return;
        }

        $this->extension->name         = $this->name;
        $this->extension->type         = 'library';
        $this->extension->element      = $this->element;
        $this->extension->changelogurl = $this->changelogurl;

        // There is no folder for libraries
        $this->extension->folder    = '';
        $this->extension->enabled   = 1;
        $this->extension->protected = 0;
        $this->extension->access    = 1;
        $this->extension->client_id = 0;
        $this->extension->params    = $this->parent->getParams();

        // Update the manifest cache for the entry
        $this->extension->manifest_cache = $this->parent->generateManifestCache();

        if (!$this->extension->store()) {
            // Install failed, roll back changes
            throw new \RuntimeException(
                Text::sprintf(
                    'JLIB_INSTALLER_ABORT_LIB_INSTALL_ROLLBACK',
                    $this->extension->getError()
                )
            );
        }

        // Since we have created a library item, we add it to the installation step stack
        // so that if we have to rollback the changes we can undo it.
        $this->parent->pushStep(['type' => 'extension', 'id' => $this->extension->extension_id]);
    }

    /**
     * Custom discover method
     *
     * @return  array  Extension  list of extensions available
     *
     * @since   3.1
     */
    public function discover()
    {
        $results = [];

        $mainFolder = JPATH_MANIFESTS . '/libraries';
        $folder = new \RecursiveDirectoryIterator($mainFolder);
        $iterator = new \RegexIterator(
            new \RecursiveIteratorIterator($folder),
            '/\.xml$/i',
            \RecursiveRegexIterator::GET_MATCH
        );

        foreach ($iterator as $file => $pattern) {
            $element       = str_replace([$mainFolder . DIRECTORY_SEPARATOR, '.xml'], '', $file);
            $manifestCache = Installer::parseXMLInstallFile($file);

            $extension = Table::getInstance('extension');
            $extension->set('type', 'library');
            $extension->set('client_id', 0);
            $extension->set('element', $element);
            $extension->set('folder', '');
            $extension->set('name', $element);
            $extension->set('state', -1);
            $extension->set('manifest_cache', json_encode($manifestCache));
            $extension->set('params', '{}');
            $results[] = $extension;
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
        $manifestPath           = JPATH_MANIFESTS . '/libraries/' . $this->parent->extension->element . '.xml';
        $this->parent->manifest = $this->parent->isManifest($manifestPath);
        $this->parent->setPath('manifest', $manifestPath);

        $manifest_details                        = Installer::parseXMLInstallFile($this->parent->getPath('manifest'));
        $this->parent->extension->manifest_cache = json_encode($manifest_details);
        $this->parent->extension->name           = $manifest_details['name'];

        try {
            return $this->parent->extension->store();
        } catch (\RuntimeException $e) {
            Log::add(Text::_('JLIB_INSTALLER_ERROR_LIB_REFRESH_MANIFEST_CACHE'), Log::WARNING, 'jerror');

            return false;
        }
    }
}
