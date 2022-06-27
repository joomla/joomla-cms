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
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;

/**
 * File installer
 *
 * @since  3.1
 */
class FileAdapter extends InstallerAdapter
{
    /**
     * `<scriptfile>` element of the extension manifest
     *
     * @var    object
     * @since  3.1
     */
    protected $scriptElement = null;

    /**
     * Flag if the adapter supports discover installs
     *
     * Adapters should override this and set to false if discover install is unsupported
     *
     * @var    boolean
     * @since  3.4
     */
    protected $supportsDiscoverInstall = false;

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
        // Populate File and Folder List to copy
        $this->populateFilesAndFolderList();

        // Now that we have folder list, lets start creating them
        foreach ($this->folderList as $folder) {
            if (!Folder::exists($folder)) {
                if (!$created = Folder::create($folder)) {
                    throw new \RuntimeException(
                        Text::sprintf('JLIB_INSTALLER_ABORT_FILE_INSTALL_FAIL_SOURCE_DIRECTORY', $folder)
                    );
                }

                // Since we created a directory and will want to remove it if we have to roll back.
                // The installation due to some errors, let's add it to the installation step stack.
                if ($created) {
                    $this->parent->pushStep(array('type' => 'folder', 'path' => $folder));
                }
            }
        }

        // Now that we have file list, let's start copying them
        $this->parent->copyFiles($this->fileList);
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
        $update = Table::getInstance('update');

        $uid = $update->find(
            array(
                'element' => $this->element,
                'type' => $this->type,
            )
        );

        if ($uid) {
            $update->delete($uid);
        }

        // Lastly, we will copy the manifest file to its appropriate place.
        $manifest = array();
        $manifest['src'] = $this->parent->getPath('manifest');
        $manifest['dest'] = JPATH_MANIFESTS . '/files/' . basename($this->parent->getPath('manifest'));

        if (!$this->parent->copyFiles(array($manifest), true)) {
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
            // First, we have to create a folder for the script if one isn't present
            if (!file_exists($this->parent->getPath('extension_root'))) {
                Folder::create($this->parent->getPath('extension_root'));
            }

            $path['src'] = $this->parent->getPath('source') . '/' . $this->manifest_script;
            $path['dest'] = $this->parent->getPath('extension_root') . '/' . $this->manifest_script;

            if ($this->parent->isOverwrite() || !file_exists($path['dest'])) {
                if (!$this->parent->copyFiles(array($path))) {
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
        File::delete(JPATH_MANIFESTS . '/files/' . $this->extension->element . '.xml');

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
            $manifestPath = Path::clean($this->parent->getPath('manifest'));
            $element = preg_replace('/\.xml/', '', basename($manifestPath));
        }

        return $element;
    }

    /**
     * Custom loadLanguage method
     *
     * @param   string  $path  The path on which to find language files.
     *
     * @return  void
     *
     * @since   3.1
     */
    public function loadLanguage($path)
    {
        $extension = 'files_' . strtolower(str_replace('files_', '', $this->getElement()));

        $this->doLoadLanguage($extension, $path, JPATH_SITE);
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
        $this->parent->parseLanguages($this->getManifest()->languages);
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
        // Loop through all elements and get list of files and folders
        foreach ($this->getManifest()->fileset->files as $eFiles) {
            $target = (string) $eFiles->attributes()->target;

            // Create folder path
            if (empty($target)) {
                $targetFolder = JPATH_ROOT;
            } else {
                $targetFolder = JPATH_ROOT . '/' . $target;
            }

            $folderList = [];

            // Check if all children exists
            if (\count($eFiles->children()) > 0) {
                // Loop through all filenames elements
                foreach ($eFiles->children() as $eFileName) {
                    if ($eFileName->getName() === 'folder') {
                        $folderList[] = $targetFolder . '/' . $eFileName;
                    } else {
                        $fileName = $targetFolder . '/' . $eFileName;
                        File::delete($fileName);
                    }
                }
            }

            // Delete any folders that don't have any content in them.
            foreach ($folderList as $folder) {
                $files = Folder::files($folder);

                if ($files !== false && !\count($files)) {
                    Folder::delete($folder);
                }
            }
        }

        // Lastly, remove the extension_root
        $folder = $this->parent->getPath('extension_root');

        if (Folder::exists($folder)) {
            Folder::delete($folder);
        }

        $this->parent->removeFiles($this->getManifest()->languages);
    }

    /**
     * Method to do any prechecks and setup the install paths for the extension
     *
     * @return  void
     *
     * @since   3.4
     */
    protected function setupInstallPaths()
    {
        // Set the file root path
        if ($this->name === 'files_joomla') {
            // If we are updating the Joomla core, set the root path to the root of Joomla
            $this->parent->setPath('extension_root', JPATH_ROOT);
        } else {
            $this->parent->setPath('extension_root', JPATH_MANIFESTS . '/files/' . $this->element);
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
        $manifestFile = JPATH_MANIFESTS . '/files/' . $this->extension->element . '.xml';

        // Because libraries may not have their own folders we cannot use the standard method of finding an installation manifest
        if (!file_exists($manifestFile)) {
            // Remove this row entry since its invalid
            $this->extension->delete($this->extension->extension_id);

            throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_INVALID_NOTFOUND_MANIFEST'));
        }

        // Set the files root path
        $this->parent->setPath('extension_root', JPATH_MANIFESTS . '/files/' . $this->extension->element);

        // Set the source path for compatibility with the API
        $this->parent->setPath('source', $this->parent->getPath('extension_root'));

        $xml = simplexml_load_file($manifestFile);

        // If we cannot load the XML file return null
        if (!$xml) {
            throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_LOAD_MANIFEST'));
        }

        // Check for a valid XML root tag.
        if ($xml->getName() !== 'extension') {
            throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_FILE_UNINSTALL_INVALID_MANIFEST'));
        }

        $this->setManifest($xml);

        // Attempt to load the language file; might have uninstall strings
        $this->loadLanguage(JPATH_MANIFESTS . '/files');
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
        if ($this->currentExtensionId) {
            // Load the entry and update the manifest_cache
            $this->extension->load($this->currentExtensionId);

            // Update name
            $this->extension->name = $this->name;

            // Update manifest
            $this->extension->manifest_cache = $this->parent->generateManifestCache();

            if (!$this->extension->store()) {
                // Install failed, roll back changes
                throw new \RuntimeException(
                    Text::sprintf(
                        'JLIB_INSTALLER_ABORT_ROLLBACK',
                        Text::_('JLIB_INSTALLER_' . strtoupper($this->route)),
                        $this->extension->getError()
                    )
                );
            }
        } else {
            // Add an entry to the extension table with a whole heap of defaults
            $this->extension->name         = $this->name;
            $this->extension->type         = 'file';
            $this->extension->element      = $this->element;
            $this->extension->changelogurl = $this->changelogurl;

            // There is no folder for files so leave it blank
            $this->extension->folder    = '';
            $this->extension->enabled   = 1;
            $this->extension->protected = 0;
            $this->extension->access    = 0;
            $this->extension->client_id = 0;
            $this->extension->params    = '';

            // Update the manifest cache for the entry
            $this->extension->manifest_cache = $this->parent->generateManifestCache();

            if (!$this->extension->store()) {
                // Install failed, roll back changes
                throw new \RuntimeException(
                    Text::sprintf(
                        'JLIB_INSTALLER_ABORT_ROLLBACK',
                        Text::_('JLIB_INSTALLER_' . strtoupper($this->route)),
                        $this->extension->getError()
                    )
                );
            }

            // Since we have created a module item, we add it to the installation step stack
            // so that if we have to rollback the changes we can undo it.
            $this->parent->pushStep(array('type' => 'extension', 'extension_id' => $this->extension->extension_id));
        }
    }

    /**
     * Function used to check if extension is already installed
     *
     * @param   string  $extension  The element name of the extension to install
     *
     * @return  boolean  True if extension exists
     *
     * @since   3.1
     */
    protected function extensionExistsInSystem($extension = null)
    {
        // Get a database connector object
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('file'))
            ->where($db->quoteName('element') . ' = :extension')
            ->bind(':extension', $extension);
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (\RuntimeException $e) {
            // Install failed, rollback changes - error logged by the installer
            return false;
        }

        $id = $db->loadResult();

        if (empty($id)) {
            return false;
        }

        return true;
    }

    /**
     * Function used to populate files and folder list
     *
     * @return  boolean  none
     *
     * @since   3.1
     */
    protected function populateFilesAndFolderList()
    {
        // Initialise variable
        $this->folderList = array();
        $this->fileList = array();

        // Set root folder names
        $packagePath = $this->parent->getPath('source');
        $jRootPath = Path::clean(JPATH_ROOT);

        // Loop through all elements and get list of files and folders
        foreach ($this->getManifest()->fileset->files as $eFiles) {
            // Check if the element is files element
            $folder = (string) $eFiles->attributes()->folder;
            $target = (string) $eFiles->attributes()->target;

            // Split folder names into array to get folder names. This will help in creating folders
            $arrList = preg_split("#/|\\/#", $target);

            $folderName = $jRootPath;

            foreach ($arrList as $dir) {
                if (empty($dir)) {
                    continue;
                }

                $folderName .= '/' . $dir;

                // Check if folder exists, if not then add to the array for folder creation
                if (!Folder::exists($folderName)) {
                    $this->folderList[] = $folderName;
                }
            }

            // Create folder path
            $sourceFolder = empty($folder) ? $packagePath : $packagePath . '/' . $folder;
            $targetFolder = empty($target) ? $jRootPath : $jRootPath . '/' . $target;

            // Check if source folder exists
            if (!Folder::exists($sourceFolder)) {
                Log::add(Text::sprintf('JLIB_INSTALLER_ABORT_FILE_INSTALL_FAIL_SOURCE_DIRECTORY', $sourceFolder), Log::WARNING, 'jerror');

                // If installation fails, rollback
                $this->parent->abort();

                return false;
            }

            // Check if all children exists
            if (\count($eFiles->children())) {
                // Loop through all filenames elements
                foreach ($eFiles->children() as $eFileName) {
                    $path['src'] = $sourceFolder . '/' . $eFileName;
                    $path['dest'] = $targetFolder . '/' . $eFileName;
                    $path['type'] = 'file';

                    if ($eFileName->getName() === 'folder') {
                        $folderName         = $targetFolder . '/' . $eFileName;
                        $this->folderList[] = $folderName;
                        $path['type']       = 'folder';
                    }

                    $this->fileList[] = $path;
                }
            } else {
                $files = Folder::files($sourceFolder);

                foreach ($files as $file) {
                    $path['src'] = $sourceFolder . '/' . $file;
                    $path['dest'] = $targetFolder . '/' . $file;

                    $this->fileList[] = $path;
                }
            }
        }
    }

    /**
     * Refreshes the extension table cache
     *
     * @return  boolean result of operation, true if updated, false on failure
     *
     * @since   3.1
     */
    public function refreshManifestCache()
    {
        // Need to find to find where the XML file is since we don't store this normally
        $manifestPath = JPATH_MANIFESTS . '/files/' . $this->parent->extension->element . '.xml';
        $this->parent->manifest = $this->parent->isManifest($manifestPath);
        $this->parent->setPath('manifest', $manifestPath);

        $manifest_details = Installer::parseXMLInstallFile($this->parent->getPath('manifest'));
        $this->parent->extension->manifest_cache = json_encode($manifest_details);
        $this->parent->extension->name = $manifest_details['name'];

        try {
            return $this->parent->extension->store();
        } catch (\RuntimeException $e) {
            Log::add(Text::_('JLIB_INSTALLER_ERROR_PACK_REFRESH_MANIFEST_CACHE'), Log::WARNING, 'jerror');

            return false;
        }
    }
}
