<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer\Adapter;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\Update;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Language installer
 *
 * @since  3.1
 */
class LanguageAdapter extends InstallerAdapter
{
    /**
     * Core language pack flag
     *
     * @var    boolean
     * @since  3.0.0
     */
    protected $core = false;

    /**
     * The language tag for the package
     *
     * @var    string
     * @since  4.0.0
     */
    protected $tag;

    /**
     * Flag indicating the uninstall process should not run SQL queries
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $ignoreUninstallQueries = false;

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
        // @todo - Refactor adapter to use common code
    }

    /**
     * Method to finalise the installation processing
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \RuntimeException
     */
    protected function finaliseInstall()
    {
        // @todo - Refactor adapter to use common code
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
        if ($this->ignoreUninstallQueries) {
            return false;
        }

        $this->resetUserLanguage();

        $extensionId = $this->extension->extension_id;

        // Remove the schema version
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__schemas'))
            ->where($db->quoteName('extension_id') . ' = :extension_id')
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

        // Clean installed languages cache.
        Factory::getCache()->clean('com_languages');

        // Remove the extension table entry
        $this->extension->delete();

        return true;
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
        $this->parent->removeFiles($this->getManifest()->media);

        // Construct the path from the client, the language and the extension element name
        $path = ApplicationHelper::getClientInfo($this->extension->client_id)->path . '/language/' . $this->extension->element;

        if (!Folder::delete($path)) {
            // If deleting failed we'll leave the extension entry in tact just in case
            Log::add(Text::_('JLIB_INSTALLER_ERROR_LANG_UNINSTALL_DIRECTORY'), Log::WARNING, 'jerror');

            $this->ignoreUninstallQueries = true;
        }
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
        // @todo - Refactor adapter to use common code
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
        // Grab a copy of the client details
        $client = ApplicationHelper::getClientInfo($this->extension->client_id);

        // Check the element isn't blank to prevent nuking the languages directory...just in case
        if (empty($this->extension->element)) {
            throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_LANG_UNINSTALL_ELEMENT_EMPTY'));
        }

        // Verify that it's not the default language for that client
        $params = ComponentHelper::getParams('com_languages');

        if ($params->get($client->name) === $this->extension->element) {
            throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_LANG_UNINSTALL_DEFAULT'));
        }

        // Construct the path from the client, the language and the extension element name
        $path = $client->path . '/language/' . $this->extension->element;

        // Get the package manifest object and remove media
        $this->parent->setPath('source', $path);

        // Check it exists
        if (!Folder::exists($path)) {
            // If the folder doesn't exist lets just nuke the row as well and presume the user killed it for us
            $this->extension->delete();

            throw new \RuntimeException(Text::_('JLIB_INSTALLER_ERROR_LANG_UNINSTALL_PATH_EMPTY'));
        }

        // We do findManifest to avoid problem when uninstalling a list of extension: getManifest cache its manifest file
        $this->parent->findManifest();
        $this->setManifest($this->parent->getManifest());
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
        // @todo - Refactor adapter to use common code
    }

    /**
     * Custom install method
     *
     * Note: This behaves badly due to hacks made in the middle of 1.5.x to add
     * the ability to install multiple distinct packs in one install. The
     * preferred method is to use a package to install multiple language packs.
     *
     * @return  boolean|integer  The extension ID on success, boolean false on failure
     *
     * @since   3.1
     */
    public function install()
    {
        $source = $this->parent->getPath('source');

        if (!$source) {
            $this->parent
                ->setPath(
                    'source',
                    ApplicationHelper::getClientInfo($this->parent->extension->client_id)->path . '/language/' . $this->parent->extension->element
                );
        }

        $this->setManifest($this->parent->getManifest());

        // Get the client application target
        if ($cname = (string) $this->getManifest()->attributes()->client) {
            // Attempt to map the client to a base path
            $client = ApplicationHelper::getClientInfo($cname, true);

            if ($client === null) {
                $this->parent->abort(Text::sprintf('JLIB_INSTALLER_ABORT', Text::sprintf('JLIB_INSTALLER_ERROR_UNKNOWN_CLIENT_TYPE', $cname)));

                return false;
            }

            $basePath = $client->path;
            $clientId = $client->id;
            $element  = $this->getManifest()->files;

            return $this->_install($cname, $basePath, $clientId, $element);
        } else {
            // No client attribute was found so we assume the site as the client
            $cname    = 'site';
            $basePath = JPATH_SITE;
            $clientId = 0;
            $element  = $this->getManifest()->files;

            return $this->_install($cname, $basePath, $clientId, $element);
        }
    }

    /**
     * Install function that is designed to handle individual clients
     *
     * @param   string   $cname     Cname @todo: not used
     * @param   string   $basePath  The base name.
     * @param   integer  $clientId  The client id.
     * @param   object   &$element  The XML element.
     *
     * @return  boolean|integer  The extension ID on success, boolean false on failure
     *
     * @since   3.1
     */
    protected function _install($cname, $basePath, $clientId, &$element)
    {
        $this->setManifest($this->parent->getManifest());

        // Get the language name
        // Set the extensions name
        $this->name = InputFilter::getInstance()->clean((string) $this->getManifest()->name, 'string');

        // Get the Language tag [ISO tag, eg. en-GB]
        $tag = (string) $this->getManifest()->tag;

        // Check if we found the tag - if we didn't, we may be trying to install from an older language package
        if (!$tag) {
            $this->parent->abort(Text::sprintf('JLIB_INSTALLER_ABORT', Text::_('JLIB_INSTALLER_ERROR_NO_LANGUAGE_TAG')));

            return false;
        }

        $this->tag = $tag;

        // Set the language installation path
        $this->parent->setPath('extension_site', $basePath . '/language/' . $tag);

        // Do we have a meta file in the file list?  In other words... is this a core language pack?
        if ($element && \count($element->children())) {
            $files = $element->children();

            foreach ($files as $file) {
                if ((string) $file->attributes()->file === 'meta') {
                    $this->core = true;
                    break;
                }
            }
        }

        // If the language directory does not exist, let's create it
        $created = false;

        if (!file_exists($this->parent->getPath('extension_site'))) {
            if (!$created = Folder::create($this->parent->getPath('extension_site'))) {
                $this->parent
                    ->abort(
                        Text::sprintf(
                            'JLIB_INSTALLER_ABORT',
                            Text::sprintf('JLIB_INSTALLER_ERROR_CREATE_FOLDER_FAILED', $this->parent->getPath('extension_site'))
                        )
                    );

                return false;
            }
        } else {
            // Look for an update function or update tag
            $updateElement = $this->getManifest()->update;

            // Upgrade manually set or update tag detected
            if ($updateElement || $this->parent->isUpgrade()) {
                // Transfer control to the update function
                return $this->update();
            } elseif (!$this->parent->isOverwrite()) {
                // Overwrite is set
                // We didn't have overwrite set, find an update function or find an update tag so lets call it safe
                if (file_exists($this->parent->getPath('extension_site'))) {
                    // If the site exists say so.
                    Log::add(
                        Text::sprintf('JLIB_INSTALLER_ABORT', Text::sprintf('JLIB_INSTALLER_ERROR_FOLDER_IN_USE', $this->parent->getPath('extension_site'))),
                        Log::WARNING,
                        'jerror'
                    );
                } elseif (file_exists($this->parent->getPath('extension_administrator'))) {
                    // If the admin exists say so.
                    Log::add(
                        Text::sprintf(
                            'JLIB_INSTALLER_ABORT',
                            Text::sprintf('JLIB_INSTALLER_ERROR_FOLDER_IN_USE', $this->parent->getPath('extension_administrator'))
                        ),
                        Log::WARNING,
                        'jerror'
                    );
                } else {
                    // If the api exists say so.
                    Log::add(
                        Text::sprintf(
                            'JLIB_INSTALLER_ABORT',
                            Text::sprintf('JLIB_INSTALLER_ERROR_FOLDER_IN_USE', $this->parent->getPath('extension_api'))
                        ),
                        Log::WARNING,
                        'jerror'
                    );
                }

                return false;
            }
        }

        /*
         * If we created the language directory we will want to remove it if we
         * have to roll back the installation, so let's add it to the installation
         * step stack
         */
        if ($created) {
            $this->parent->pushStep(array('type' => 'folder', 'path' => $this->parent->getPath('extension_site')));
        }

        // Copy all the necessary files
        if ($this->parent->parseFiles($element) === false) {
            // Install failed, rollback changes
            $this->parent->abort();

            return false;
        }

        // Parse optional tags
        $this->parent->parseMedia($this->getManifest()->media);

        // Get the language description
        $description = (string) $this->getManifest()->description;

        if ($description) {
            $this->parent->set('message', Text::_($description));
        } else {
            $this->parent->set('message', '');
        }

        // Add an entry to the extension table with a whole heap of defaults
        $row = Table::getInstance('extension');
        $row->set('name', $this->name);
        $row->set('type', 'language');
        $row->set('element', $this->tag);
        $row->set('changelogurl', (string) $this->getManifest()->changelogurl);

        // There is no folder for languages
        $row->set('folder', '');
        $row->set('enabled', 1);
        $row->set('protected', 0);
        $row->set('access', 0);
        $row->set('client_id', $clientId);
        $row->set('params', $this->parent->getParams());
        $row->set('manifest_cache', $this->parent->generateManifestCache());

        if (!$row->check() || !$row->store()) {
            // Install failed, roll back changes
            $this->parent->abort(Text::sprintf('JLIB_INSTALLER_ABORT', $row->getError()));

            return false;
        }

        if ((int) $clientId === 0) {
            $this->createContentLanguage($this->tag);
        }

        // Clobber any possible pending updates
        /** @var Update $update */
        $update = Table::getInstance('update');
        $uid = $update->find(array('element' => $this->tag, 'type' => 'language', 'folder' => ''));

        if ($uid) {
            $update->delete($uid);
        }

        // Clean installed languages cache.
        Factory::getCache()->clean('com_languages');

        return $row->get('extension_id');
    }

    /**
     * Gets a unique language SEF string.
     *
     * This function checks other existing language with the same code, if they exist provides a unique SEF name.
     * For instance: en-GB, en-US and en-AU will share the same SEF code by default: www.mywebsite.com/en/
     * To avoid this conflict, this function creates an specific SEF in case of existing conflict:
     * For example: www.mywebsite.com/en-au/
     *
     * @param   string  $itemLanguageTag  Language Tag.
     *
     * @return  string
     *
     * @since   3.7.0
     */
    protected function getSefString($itemLanguageTag)
    {
        $langs               = explode('-', $itemLanguageTag);
        $prefixToFind        = $langs[0];
        $numberPrefixesFound = 0;

        // Get the sef value of all current content languages.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('sef'))
            ->from($db->quoteName('#__languages'));
        $db->setQuery($query);

        $siteLanguages = $db->loadObjectList();

        foreach ($siteLanguages as $siteLang) {
            if ($siteLang->sef === $prefixToFind) {
                $numberPrefixesFound++;
            }
        }

        return $numberPrefixesFound === 0 ? $prefixToFind : strtolower($itemLanguageTag);
    }

    /**
     * Custom update method
     *
     * @return  boolean  True on success, false on failure
     *
     * @since   3.1
     */
    public function update()
    {
        $xml = $this->parent->getManifest();

        $this->setManifest($xml);

        $cname = $xml->attributes()->client;

        // Attempt to map the client to a base path
        $client = ApplicationHelper::getClientInfo($cname, true);

        if ($client === null || (empty($cname) && $cname !== 0)) {
            $this->parent->abort(Text::sprintf('JLIB_INSTALLER_ABORT', Text::sprintf('JLIB_INSTALLER_ERROR_UNKNOWN_CLIENT_TYPE', $cname)));

            return false;
        }

        $basePath = $client->path;
        $clientId = $client->id;

        // Get the language name
        // Set the extensions name
        $name = (string) $this->getManifest()->name;
        $name = InputFilter::getInstance()->clean($name, 'string');
        $this->name = $name;

        // Get the Language tag [ISO tag, eg. en-GB]
        $tag = (string) $xml->tag;

        // Check if we found the tag - if we didn't, we may be trying to install from an older language package
        if (!$tag) {
            $this->parent->abort(Text::sprintf('JLIB_INSTALLER_ABORT', Text::_('JLIB_INSTALLER_ERROR_NO_LANGUAGE_TAG')));

            return false;
        }

        $this->tag = $tag;

        // Set the language installation path
        $this->parent->setPath('extension_site', $basePath . '/language/' . $tag);

        // Do we have a meta file in the file list?  In other words... is this a core language pack?
        if (\count($xml->files->children())) {
            foreach ($xml->files->children() as $file) {
                if ((string) $file->attributes()->file === 'meta') {
                    $this->core = true;
                    break;
                }
            }
        }

        // Copy all the necessary files
        if ($this->parent->parseFiles($xml->files) === false) {
            // Install failed, rollback changes
            $this->parent->abort();

            return false;
        }

        // Parse optional tags
        $this->parent->parseMedia($xml->media);

        // Get the language description and set it as message
        $this->parent->set('message', (string) $xml->description);

        /**
         * ---------------------------------------------------------------------------------------------
         * Finalization and Cleanup Section
         * ---------------------------------------------------------------------------------------------
         */

        // Clobber any possible pending updates
        $update = Table::getInstance('update');
        $uid = $update->find(array('element' => $this->tag, 'type' => 'language', 'client_id' => $clientId));

        if ($uid) {
            $update->delete($uid);
        }

        // Update an entry to the extension table
        $row = Table::getInstance('extension');
        $eid = $row->find(array('element' => $this->tag, 'type' => 'language', 'client_id' => $clientId));

        if ($eid) {
            $row->load($eid);
        } else {
            // Set the defaults

            // There is no folder for language
            $row->set('folder', '');
            $row->set('enabled', 1);
            $row->set('protected', 0);
            $row->set('access', 0);
            $row->set('client_id', $clientId);
            $row->set('params', $this->parent->getParams());
        }

        $row->set('name', $this->name);
        $row->set('type', 'language');
        $row->set('element', $this->tag);
        $row->set('manifest_cache', $this->parent->generateManifestCache());
        $row->set('changelogurl', (string) $this->getManifest()->changelogurl);

        // Clean installed languages cache.
        Factory::getCache()->clean('com_languages');

        if (!$row->check() || !$row->store()) {
            // Install failed, roll back changes
            $this->parent->abort(Text::sprintf('JLIB_INSTALLER_ABORT', $row->getError()));

            return false;
        }

        if ($clientId === 0) {
            $this->createContentLanguage($this->tag);
        }

        return $row->get('extension_id');
    }

    /**
     * Custom discover method
     * Finds language files
     *
     * @return  \Joomla\CMS\Table\Extension[]  Array of discovered extensions.
     *
     * @since  3.1
     */
    public function discover()
    {
        $results = [];
        $clients = [0 => JPATH_SITE, 1 => JPATH_ADMINISTRATOR, 3 => JPATH_API];

        foreach ($clients as $clientId => $basePath) {
            $languages = Folder::folders($basePath . '/language');

            foreach ($languages as $language) {
                $manifestfile = $basePath . '/language/' . $language . '/langmetadata.xml';

                if (!is_file($manifestfile)) {
                    $manifestfile = $basePath . '/language/' . $language . '/' . $language . '.xml';

                    if (!is_file($manifestfile)) {
                        continue;
                    }
                }

                $manifest_details = Installer::parseXMLInstallFile($manifestfile);
                $extension = Table::getInstance('extension');
                $extension->set('type', 'language');
                $extension->set('client_id', $clientId);
                $extension->set('element', $language);
                $extension->set('folder', '');
                $extension->set('name', $language);
                $extension->set('state', -1);
                $extension->set('manifest_cache', json_encode($manifest_details));
                $extension->set('params', '{}');
                $results[] = $extension;
            }
        }

        return $results;
    }

    /**
     * Custom discover install method
     * Basically updates the manifest cache and leaves everything alone
     *
     * @return  integer  The extension id
     *
     * @since   3.1
     */
    public function discover_install()
    {
        // Need to find to find where the XML file is since we don't store this normally
        $client                 = ApplicationHelper::getClientInfo($this->parent->extension->client_id);
        $short_element          = $this->parent->extension->element;
        $manifestPath           = $client->path . '/language/' . $short_element . '/langmetadata.xml';

        if (!is_file($manifestPath)) {
            $manifestPath = $client->path . '/language/' . $short_element . '/' . $short_element . '.xml';
        }

        $this->parent->manifest = $this->parent->isManifest($manifestPath);
        $this->parent->setPath('manifest', $manifestPath);
        $this->parent->setPath('source', $client->path . '/language/' . $short_element);
        $this->parent->setPath('extension_root', $this->parent->getPath('source'));
        $manifest_details                        = Installer::parseXMLInstallFile($this->parent->getPath('manifest'));
        $this->parent->extension->manifest_cache = json_encode($manifest_details);
        $this->parent->extension->state          = 0;
        $this->parent->extension->name           = $manifest_details['name'];
        $this->parent->extension->enabled        = 1;

        // @todo remove code: $this->parent->extension->params = $this->parent->getParams();
        try {
            $this->parent->extension->check();
            $this->parent->extension->store();
        } catch (\RuntimeException $e) {
            Log::add(Text::_('JLIB_INSTALLER_ERROR_LANG_DISCOVER_STORE_DETAILS'), Log::WARNING, 'jerror');

            return false;
        }

        if ($client->id === 0) {
            $this->createContentLanguage($short_element);
        }

        // Clean installed languages cache.
        Factory::getCache()->clean('com_languages');

        return $this->parent->extension->get('extension_id');
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
        $client       = ApplicationHelper::getClientInfo($this->parent->extension->client_id);
        $manifestPath = $client->path . '/language/' . $this->parent->extension->element . '/langmetadata.xml';

        if (!is_file($manifestPath)) {
            $manifestPath = $client->path . '/language/' . $this->parent->extension->element . '/' . $this->parent->extension->element . '.xml';
        }

        $this->parent->manifest = $this->parent->isManifest($manifestPath);
        $this->parent->setPath('manifest', $manifestPath);
        $manifest_details                        = Installer::parseXMLInstallFile($this->parent->getPath('manifest'));
        $this->parent->extension->manifest_cache = json_encode($manifest_details);
        $this->parent->extension->name           = $manifest_details['name'];

        if ($this->parent->extension->store()) {
            return true;
        }

        Log::add(Text::_('JLIB_INSTALLER_ERROR_MOD_REFRESH_MANIFEST_CACHE'), Log::WARNING, 'jerror');

        return false;
    }

    /**
     * Resets user language to default language
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function resetUserLanguage(): void
    {
        $client = ApplicationHelper::getClientInfo($this->extension->client_id);

        if ($client->name !== 'site' && $client->name !== 'administrator') {
            return;
        }

        // Setting the language of users which have this language as the default language
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('id'),
                    $db->quoteName('params'),
                ]
            )
            ->from($db->quoteName('#__users'));
        $db->setQuery($query);
        $users = $db->loadObjectList();

        if ($client->name === 'administrator') {
            $param_name = 'admin_language';
        } else {
            $param_name = 'language';
        }

        $count = 0;

        // Prepare the query.
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__users'))
            ->set($db->quoteName('params') . ' = :registry')
            ->where($db->quoteName('id') . ' = :userId')
            ->bind(':registry', $registry)
            ->bind(':userId', $userId, ParameterType::INTEGER);
        $db->setQuery($query);

        foreach ($users as $user) {
            $registry = new Registry($user->params);

            if ($registry->get($param_name) === $this->extension->element) {
                // Update query parameters.
                $registry->set($param_name, '');
                $userId = $user->id;

                $db->execute();
                $count++;
            }
        }

        if (!empty($count)) {
            Log::add(Text::plural('JLIB_INSTALLER_NOTICE_LANG_RESET_USERS', $count), Log::NOTICE, 'jerror');
        }
    }

    /**
     * Create an unpublished content language.
     *
     * @param  $tag  string  The language tag
     *
     * @throws \Exception
     * @since   4.0.0
     */
    protected function createContentLanguage($tag)
    {
        $tableLanguage = Table::getInstance('language');

        // Check if content language already exists.
        if ($tableLanguage->load(array('lang_code' => $tag))) {
            return;
        }

        $manifestfile = JPATH_SITE . '/language/' . $tag . '/langmetadata.xml';

        if (!is_file($manifestfile)) {
            $manifestfile = JPATH_SITE . '/language/' . $tag . '/' . $tag . '.xml';
        }

        // Load the site language manifest.
        $siteLanguageManifest = LanguageHelper::parseXMLLanguageFile($manifestfile);

        // Set the content language title as the language metadata name.
        $contentLanguageTitle = $siteLanguageManifest['name'];

        // Set, as fallback, the content language native title to the language metadata name.
        $contentLanguageNativeTitle = $contentLanguageTitle;

        // If exist, load the native title from the language xml metadata.
        if (isset($siteLanguageManifest['nativeName']) && $siteLanguageManifest['nativeName']) {
            $contentLanguageNativeTitle = $siteLanguageManifest['nativeName'];
        }

        // Try to load a language string from the installation language var. Will be removed in 4.0.
        if ($contentLanguageNativeTitle === $contentLanguageTitle) {
            $manifestfile = JPATH_INSTALLATION . '/language/' . $tag . '/langmetadata.xml';

            if (!is_file($manifestfile)) {
                $manifestfile = JPATH_INSTALLATION . '/language/' . $tag . '/' . $tag . '.xml';
            }

            if (file_exists($manifestfile)) {
                $installationLanguage = new Language($tag);
                $installationLanguage->load('', JPATH_INSTALLATION);

                if ($installationLanguage->hasKey('INSTL_DEFAULTLANGUAGE_NATIVE_LANGUAGE_NAME')) {
                    // Make sure it will not use the en-GB fallback.
                    $defaultLanguage = new Language('en-GB');
                    $defaultLanguage->load('', JPATH_INSTALLATION);

                    $defaultLanguageNativeTitle      = $defaultLanguage->_('INSTL_DEFAULTLANGUAGE_NATIVE_LANGUAGE_NAME');
                    $installationLanguageNativeTitle = $installationLanguage->_('INSTL_DEFAULTLANGUAGE_NATIVE_LANGUAGE_NAME');

                    if ($defaultLanguageNativeTitle !== $installationLanguageNativeTitle) {
                        $contentLanguageNativeTitle = $installationLanguage->_('INSTL_DEFAULTLANGUAGE_NATIVE_LANGUAGE_NAME');
                    }
                }
            }
        }

        // Prepare language data for store.
        $languageData = array(
            'lang_id'      => 0,
            'lang_code'    => $tag,
            'title'        => $contentLanguageTitle,
            'title_native' => $contentLanguageNativeTitle,
            'sef'          => $this->getSefString($tag),
            'image'        => strtolower(str_replace('-', '_', $tag)),
            'published'    => 0,
            'ordering'     => 0,
            'access'       => (int) Factory::getApplication()->get('access', 1),
            'description'  => '',
            'metadesc'     => '',
            'sitename'     => '',
        );

        if (!$tableLanguage->bind($languageData) || !$tableLanguage->check() || !$tableLanguage->store() || !$tableLanguage->reorder()) {
            Log::add(
                Text::sprintf('JLIB_INSTALLER_WARNING_UNABLE_TO_INSTALL_CONTENT_LANGUAGE', $siteLanguageManifest['name'], $tableLanguage->getError()),
                Log::NOTICE,
                'jerror'
            );
        }
    }
}
