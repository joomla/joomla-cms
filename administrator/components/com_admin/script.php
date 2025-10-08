<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Asset;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Script file of Joomla CMS
 *
 * @since  1.6.4
 */
class JoomlaInstallerScript
{
    /**
     * The Joomla Version we are updating from
     *
     * @var    string
     * @since  3.7
     */
    protected $fromVersion = null;

    /**
     * Callback for collecting errors. Like function(string $context, \Throwable $error){};
     *
     * @var callable
     *
     * @since  4.4.0
     */
    protected $errorCollector;

    /**
     * Set the callback for collecting errors.
     *
     * @param   callable  $callback  The callback Like function(string $context, \Throwable $error){};
     *
     * @return  void
     *
     * @since  4.4.0
     */
    public function setErrorCollector(callable $callback)
    {
        $this->errorCollector = $callback;
    }

    /**
     * Collect errors.
     *
     * @param  string      $context  A context/place where error happened
     * @param  \Throwable  $error    The error that occurred
     *
     * @return  void
     *
     * @since  4.4.0
     */
    protected function collectError(string $context, \Throwable $error)
    {
        // The errorCollector are required
        // However when someone already running the script manually the code may fail.
        if ($this->errorCollector) {
            \call_user_func($this->errorCollector, $context, $error);
        } else {
            Log::add($error->getMessage(), Log::ERROR, 'Update');
        }
    }

    /**
     * Function to act prior to installation process begins
     *
     * @param   string     $action     Which action is happening (install|uninstall|discover_install|update)
     * @param   Installer  $installer  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   3.7.0
     */
    public function preflight($action, $installer)
    {
        if ($action === 'update') {
            // Get the version we are updating from
            if (!empty($installer->extension->manifest_cache)) {
                $manifestValues = json_decode($installer->extension->manifest_cache, true);

                if (\array_key_exists('version', $manifestValues)) {
                    $this->fromVersion = $manifestValues['version'];

                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Method to update Joomla!
     *
     * @param   Installer  $installer  The class calling this method
     *
     * @return  void
     */
    public function update($installer)
    {
        // Uninstall extensions before removing their files and folders
        try {
            Log::add(Text::_('COM_JOOMLAUPDATE_UPDATE_LOG_UNINSTALL_EXTENSIONS'), Log::INFO, 'Update');
            $this->uninstallExtensions();
        } catch (\Throwable $e) {
            $this->collectError('uninstallExtensions', $e);
        }

        // Remove old files
        try {
            Log::add(Text::_('COM_JOOMLAUPDATE_UPDATE_LOG_DELETE_FILES'), Log::INFO, 'Update');
            $this->deleteUnexistingFiles();
        } catch (\Throwable $e) {
            $this->collectError('deleteUnexistingFiles', $e);
        }

        // Further update
        try {
            $this->updateManifestCaches();
            $this->updateDatabase();
            $this->updateAssets($installer);
            $this->clearStatsCache();
        } catch (\Throwable $e) {
            $this->collectError('Further update', $e);
        }

        // Clean cache
        try {
            $this->cleanJoomlaCache();
        } catch (\Throwable $e) {
            $this->collectError('cleanJoomlaCache', $e);
        }
    }

    /**
     * Method to clear our stats plugin cache to ensure we get fresh data on Joomla Update
     *
     * @return  void
     *
     * @since   3.5
     */
    protected function clearStatsCache()
    {
        $db = Factory::getDbo();

        try {
            // Get the params for the stats plugin
            $params = $db->setQuery(
                $db->createQuery()
                    ->select($db->quoteName('params'))
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote('stats'))
            )->loadResult();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return;
        }

        $params = json_decode($params, true);

        // Reset the last run parameter
        if (isset($params['lastrun'])) {
            $params['lastrun'] = '';
        }

        $params = json_encode($params);

        $query = $db->createQuery()
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = ' . $db->quote($params))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('stats'));

        try {
            $db->setQuery($query)->execute();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return;
        }
    }

    /**
     * Method to update Database
     *
     * @return  void
     */
    protected function updateDatabase()
    {
        if (Factory::getDbo()->getServerType() === 'mysql') {
            $this->updateDatabaseMysql();
        }
    }

    /**
     * Method to update MySQL Database
     *
     * @return  void
     */
    protected function updateDatabaseMysql()
    {
        $db = Factory::getDbo();

        $db->setQuery('SHOW ENGINES');

        try {
            $results = $db->loadObjectList();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return;
        }

        foreach ($results as $result) {
            if ($result->Support != 'DEFAULT') {
                continue;
            }

            $db->setQuery('ALTER TABLE #__update_sites_extensions ENGINE = ' . $result->Engine);

            try {
                $db->execute();
            } catch (Exception $e) {
                $this->collectError(__METHOD__, $e);

                return;
            }

            break;
        }
    }

    /**
     * Uninstall extensions and optionally migrate their parameters when
     * updating from a version older than 6.0.0.
     *
     * @return  void
     *
     * @since   5.0.0
     */
    protected function uninstallExtensions()
    {
        // Don't uninstall extensions when not updating from a version older than 6.0.0
        if (empty($this->fromVersion) || version_compare($this->fromVersion, '6.0.0', 'ge')) {
            return true;
        }

        $extensions = [
            /**
             * Define here the extensions to be uninstalled and optionally migrated on update.
             * For each extension, specify an associative array with following elements (key => value):
             * 'type'         => Field `type` in the `#__extensions` table
             * 'element'      => Field `element` in the `#__extensions` table
             * 'folder'       => Field `folder` in the `#__extensions` table
             * 'client_id'    => Field `client_id` in the `#__extensions` table
             * 'pre_function' => Name of an optional migration function to be called before
             *                   uninstalling, `null` if not used.
             * Examples:
             * ['type' => 'plugin', 'element' => 'demotasks', 'folder' => 'task', 'client_id' => 0, 'pre_function' => null],
             * ['type' => 'plugin', 'element' => 'compat', 'folder' => 'system', 'client_id' => 0, 'pre_function' => 'migrateCompatPlugin'],
             */
            // Uninstall the old joomla 5 compatibility plugin
            ['type' => 'plugin', 'element' => 'compat', 'folder' => 'behaviour', 'client_id' => 0, 'pre_function' => null],
        ];

        $db = Factory::getDbo();

        foreach ($extensions as $extension) {
            $row = $db->setQuery(
                $db->createQuery()
                    ->select('*')
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote($extension['type']))
                    ->where($db->quoteName('element') . ' = ' . $db->quote($extension['element']))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote($extension['folder']))
                    ->where($db->quoteName('client_id') . ' = ' . $db->quote($extension['client_id']))
            )->loadObject();

            // Skip migrating and uninstalling if the extension doesn't exist
            if (!$row) {
                continue;
            }

            // If there is a function for migration to be called before uninstalling, call it
            if ($extension['pre_function'] && method_exists($this, $extension['pre_function'])) {
                $this->{$extension['pre_function']}($row);
            }

            try {
                $db->transactionStart();

                // Unlock and unprotect the plugin so we can uninstall it
                $db->setQuery(
                    $db->createQuery()
                        ->update($db->quoteName('#__extensions'))
                        ->set($db->quoteName('locked') . ' = 0')
                        ->set($db->quoteName('protected') . ' = 0')
                        ->where($db->quoteName('extension_id') . ' = :extension_id')
                        ->bind(':extension_id', $row->extension_id, ParameterType::INTEGER)
                )->execute();

                // Uninstall the plugin
                $installer = new Installer();
                $installer->setDatabase($db);
                $installer->uninstall($extension['type'], $row->extension_id);

                $db->transactionCommit();
            } catch (\Exception $e) {
                $db->transactionRollback();
                throw $e;
            }
        }
    }

    /**
     * Update the manifest caches
     *
     * @return  void
     */
    protected function updateManifestCaches()
    {
        $extensions = ExtensionHelper::getCoreExtensions();

        // Attempt to refresh manifest caches
        $db    = Factory::getDbo();
        $query = $db->createQuery()
            ->select('*')
            ->from('#__extensions');

        foreach ($extensions as $extension) {
            $query->where(
                'type=' . $db->quote($extension[0])
                . ' AND element=' . $db->quote($extension[1])
                . ' AND folder=' . $db->quote($extension[2])
                . ' AND client_id=' . $extension[3],
                'OR'
            );
        }

        $db->setQuery($query);

        try {
            $extensions = $db->loadObjectList();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return;
        }

        $installer = new Installer();
        $installer->setDatabase($db);

        foreach ($extensions as $extension) {
            if (!$installer->refreshManifestCache($extension->extension_id)) {
                $this->collectError(
                    __METHOD__,
                    new \Exception(\sprintf(
                        'Error on updating manifest cache: (type, element, folder, client) = (%s, %s, %s, %s)',
                        $extension->type,
                        $extension->element,
                        $extension->name,
                        $extension->client_id
                    ))
                );
            }
        }
    }

    /**
     * Delete files that should not exist
     *
     * @param bool  $dryRun          If set to true, will not actually delete files, but just report their status for use in CLI
     * @param bool  $suppressOutput   Set to true to suppress echoing any errors, and just return the $status array
     *
     * @return  array
     */
    public function deleteUnexistingFiles($dryRun = false, $suppressOutput = false)
    {
        $status = [
            'files_exist'     => [],
            'folders_exist'   => [],
            'files_deleted'   => [],
            'folders_deleted' => [],
            'files_errors'    => [],
            'folders_errors'  => [],
        ];

        $files = [
            // From 5.x to 6.0
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-03-11.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-03-17.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-07-12.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-07-25.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-07-29.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-08-21.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-08-26.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-08-28.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-08-29.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-08-30.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-09-02.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-09-06.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-09-09.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.0.0-2023-09-11.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.1.0-2023-11-28.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.1.0-2024-01-04.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.1.0-2024-02-10.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.1.0-2024-02-24.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.1.0-2024-02-25.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.1.0-2024-03-08.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.1.0-2024-03-28.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.1.1-2024-04-18.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.2.0-2024-07-02.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.2.0-2024-07-19.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.2.0-2024-08-22.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.2.0-2024-09-17.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.2.2-2024-09-24.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.2.3-2025-01-09.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.3.0-2024-10-13.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.3.0-2024-10-26.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.3.0-2024-12-09.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.3.0-2024-12-19.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.3.0-2025-02-09.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.3.0-2025-02-22.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.3.0-2025-03-14.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.3.4-2025-09-19.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.4.0-2025-04-23.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.4.0-2025-05-10.sql',
            '/administrator/components/com_admin/sql/updates/mysql/5.4.0-2025-08-02.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-03-11.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-03-17.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-07-12.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-07-25.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-07-29.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-08-21.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-08-26.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-08-28.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-08-29.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-08-30.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-09-02.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-09-06.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-09-09.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.0.0-2023-09-11.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.1.0-2023-11-28.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.1.0-2024-02-10.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.1.0-2024-02-24.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.1.0-2024-02-25.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.1.0-2024-03-08.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.1.0-2024-03-28.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.1.1-2024-04-18.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.2.0-2024-07-02.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.2.0-2024-07-19.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.2.0-2024-08-22.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.2.0-2024-09-17.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.2.2-2024-09-24.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.2.3-2025-01-09.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.3.0-2024-10-26.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.3.0-2024-12-09.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.3.0-2024-12-19.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.3.0-2025-02-09.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.3.0-2025-02-22.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.3.0-2025-03-14.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.3.1-2025-04-27.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.3.4-2025-09-19.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.4.0-2025-04-23.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.4.0-2025-05-10.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/5.4.0-2025-08-02.sql',
            '/administrator/components/com_content/forms/filter_featured.xml',
            '/administrator/components/com_content/tmpl/featured/default.php',
            '/administrator/components/com_content/tmpl/featured/default.xml',
            '/administrator/components/com_content/tmpl/featured/default_stage_body.php',
            '/administrator/components/com_content/tmpl/featured/default_stage_footer.php',
            '/administrator/components/com_content/tmpl/featured/emptystate.php',
            '/administrator/components/com_finder/helpers/indexer/adapter.php',
            '/administrator/components/com_finder/helpers/indexer/helper.php',
            '/administrator/components/com_finder/helpers/indexer/parser.php',
            '/administrator/components/com_finder/helpers/indexer/query.php',
            '/administrator/components/com_finder/helpers/indexer/result.php',
            '/administrator/components/com_finder/helpers/indexer/taxonomy.php',
            '/administrator/components/com_finder/helpers/indexer/token.php',
            '/libraries/src/Application/BaseApplication.php',
            '/libraries/src/Application/CLI/CliInput.php',
            '/libraries/src/Application/CLI/CliOutput.php',
            '/libraries/src/Application/CLI/ColorStyle.php',
            '/libraries/src/Application/CLI/Output/Processor/ColorProcessor.php',
            '/libraries/src/Application/CLI/Output/Processor/ProcessorInterface.php',
            '/libraries/src/Application/CLI/Output/Stdout.php',
            '/libraries/src/Application/CLI/Output/Xml.php',
            '/libraries/src/Application/CliApplication.php',
            '/libraries/src/Filesystem/File.php',
            '/libraries/src/Filesystem/FilesystemHelper.php',
            '/libraries/src/Filesystem/Folder.php',
            '/libraries/src/Filesystem/Meta/language/en-GB/en-GB.lib_joomla_filesystem_patcher.ini',
            '/libraries/src/Filesystem/Patcher.php',
            '/libraries/src/Filesystem/Path.php',
            '/libraries/src/Filesystem/Stream.php',
            '/libraries/src/Filesystem/Streams/StreamString.php',
            '/libraries/src/Filesystem/Support/StringController.php',
            '/libraries/src/Input/Cookie.php',
            '/libraries/src/Input/Files.php',
            '/libraries/src/Input/Input.php',
            '/libraries/src/Input/Json.php',
            '/libraries/vendor/symfony/polyfill-iconv/bootstrap.php',
            '/libraries/vendor/symfony/polyfill-iconv/bootstrap80.php',
            '/libraries/vendor/symfony/polyfill-iconv/Iconv.php',
            '/libraries/vendor/symfony/polyfill-iconv/LICENSE',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.big5.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp037.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp1006.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp1026.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp424.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp437.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp500.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp737.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp775.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp850.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp852.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp855.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp856.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp857.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp860.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp861.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp862.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp863.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp864.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp865.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp866.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp869.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp874.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp875.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp932.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp936.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp949.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.cp950.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.iso-8859-1.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.iso-8859-10.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.iso-8859-11.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.iso-8859-13.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.iso-8859-14.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.iso-8859-15.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.iso-8859-16.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.iso-8859-2.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.iso-8859-3.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.iso-8859-4.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.iso-8859-5.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.iso-8859-6.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.iso-8859-7.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.iso-8859-8.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.iso-8859-9.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.koi8-r.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.koi8-u.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.us-ascii.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.windows-1250.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.windows-1251.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.windows-1252.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.windows-1253.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.windows-1254.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.windows-1255.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.windows-1256.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.windows-1257.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/from.windows-1258.php',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset/translit.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/LICENSE',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Assertable.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Behavior.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Collectable.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Exception.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Helper.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Interceptor/ConjunctionInterceptor.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Interceptor/PharExtensionInterceptor.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Interceptor/PharMetaDataInterceptor.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Manager.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Phar/Container.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Phar/DeserializationException.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Phar/Manifest.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Phar/Reader.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Phar/ReaderException.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Phar/Stub.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/PharStreamWrapper.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Resolvable.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Resolver/PharInvocation.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Resolver/PharInvocationCollection.php',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Resolver/PharInvocationResolver.php',
            '/libraries/vendor/voku/portable-ascii/.deepsource.toml',
            '/libraries/vendor/voku/portable-ascii/LICENSE.txt',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/ASCII.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/ascii_by_languages.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/ascii_extras_by_languages.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/ascii_language_max_key.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/ascii_ord.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x000.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x001.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x002.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x003.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x004.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x005.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x006.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x007.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x009.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x00a.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x00b.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x00c.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x00d.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x00e.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x00f.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x010.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x011.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x012.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x013.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x014.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x015.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x016.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x017.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x018.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x01d.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x01e.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x01f.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x020.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x021.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x022.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x023.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x024.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x025.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x026.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x027.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x028.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x029.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x02a.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x02c.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x02e.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x02f.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x030.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x031.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x032.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x033.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x04d.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x04e.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x04f.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x050.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x051.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x052.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x053.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x054.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x055.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x056.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x057.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x058.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x059.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x05a.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x05b.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x05c.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x05d.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x05e.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x05f.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x060.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x061.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x062.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x063.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x064.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x065.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x066.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x067.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x068.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x069.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x06a.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x06b.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x06c.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x06d.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x06e.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x06f.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x070.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x071.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x072.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x073.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x074.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x075.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x076.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x077.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x078.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x079.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x07a.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x07b.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x07c.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x07d.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x07e.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x07f.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x080.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x081.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x082.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x083.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x084.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x085.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x086.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x087.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x088.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x089.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x08a.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x08b.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x08c.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x08d.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x08e.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x08f.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x090.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x091.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x092.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x093.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x094.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x095.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x096.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x097.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x098.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x099.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x09a.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x09b.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x09c.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x09d.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x09e.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x09f.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0a0.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0a1.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0a2.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0a3.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0a4.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0ac.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0ad.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0ae.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0af.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0b0.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0b1.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0b2.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0b3.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0b4.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0b5.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0b6.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0b7.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0b8.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0b9.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0ba.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0bb.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0bc.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0bd.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0be.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0bf.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0c0.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0c1.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0c2.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0c3.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0c4.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0c5.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0c6.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0c7.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0c8.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0c9.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0ca.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0cb.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0cc.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0cd.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0ce.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0cf.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0d0.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0d1.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0d2.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0d3.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0d4.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0d5.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0d6.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0d7.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0f9.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0fa.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0fb.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0fc.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0fd.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0fe.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x0ff.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x1d4.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x1d5.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x1d6.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x1d7.php',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data/x1f1.php',
            '/libraries/vendor/voku/portable-utf8/.deepsource.toml',
            '/libraries/vendor/voku/portable-utf8/bootstrap.php',
            '/libraries/vendor/voku/portable-utf8/LICENSE-APACHE',
            '/libraries/vendor/voku/portable-utf8/LICENSE-GPL',
            '/libraries/vendor/voku/portable-utf8/src/voku/helper/Bootup.php',
            '/libraries/vendor/voku/portable-utf8/src/voku/helper/data/caseFolding_full.php',
            '/libraries/vendor/voku/portable-utf8/src/voku/helper/data/chr.php',
            '/libraries/vendor/voku/portable-utf8/src/voku/helper/data/emoji.php',
            '/libraries/vendor/voku/portable-utf8/src/voku/helper/data/encodings.php',
            '/libraries/vendor/voku/portable-utf8/src/voku/helper/data/ord.php',
            '/libraries/vendor/voku/portable-utf8/src/voku/helper/data/transliterator_list.php',
            '/libraries/vendor/voku/portable-utf8/src/voku/helper/data/utf8_fix.php',
            '/libraries/vendor/voku/portable-utf8/src/voku/helper/data/win1252_to_utf8.php',
            '/libraries/vendor/voku/portable-utf8/src/voku/helper/UTF8.php',
            '/media/legacy/joomla.asset.json',
            '/media/legacy/js/jquery-noconflict.js',
            '/media/legacy/js/jquery-noconflict.min.js',
            '/media/legacy/js/jquery-noconflict.min.js.gz',
            '/media/legacy/js/tabs-state.js',
            '/media/legacy/js/tabs-state.min.js',
            '/media/legacy/js/tabs-state.min.js.gz',
            // From 6.0.0-alpha2 to 6.0.0-alpha3
            '/administrator/modules/mod_menu/mod_menu.php',
            '/modules/mod_random_image/mod_random_image.php',
            // From 6.0.0-alpha3 to 6.0.0-beta1
            '/administrator/modules/mod_latest/mod_latest.php',
            '/administrator/modules/mod_logged/mod_logged.php',
            '/administrator/modules/mod_popular/mod_popular.php',
            '/administrator/modules/mod_privacy_dashboard/mod_privacy_dashboard.php',
            '/administrator/modules/mod_submenu/mod_submenu.php',
            '/libraries/src/Adapter/Adapter.php',
            '/libraries/src/Adapter/AdapterInstance.php',
            '/libraries/src/MVC/Model/DatabaseAwareTrait.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Common/Inflector/Inflector.php',
            '/libraries/vendor/joomla/database/src/Query/LimitableInterface.php',
            '/libraries/vendor/joomla/database/src/Query/PreparableInterface.php',
            '/libraries/vendor/joomla/string/src/phputf8/LICENSE',
            '/libraries/vendor/joomla/string/src/phputf8/mbstring/core.php',
            '/libraries/vendor/joomla/string/src/phputf8/native/core.php',
            '/libraries/vendor/joomla/string/src/phputf8/ord.php',
            '/libraries/vendor/joomla/string/src/phputf8/README',
            '/libraries/vendor/joomla/string/src/phputf8/str_ireplace.php',
            '/libraries/vendor/joomla/string/src/phputf8/str_pad.php',
            '/libraries/vendor/joomla/string/src/phputf8/str_split.php',
            '/libraries/vendor/joomla/string/src/phputf8/strcasecmp.php',
            '/libraries/vendor/joomla/string/src/phputf8/strcspn.php',
            '/libraries/vendor/joomla/string/src/phputf8/stristr.php',
            '/libraries/vendor/joomla/string/src/phputf8/strrev.php',
            '/libraries/vendor/joomla/string/src/phputf8/strspn.php',
            '/libraries/vendor/joomla/string/src/phputf8/substr_replace.php',
            '/libraries/vendor/joomla/string/src/phputf8/trim.php',
            '/libraries/vendor/joomla/string/src/phputf8/ucfirst.php',
            '/libraries/vendor/joomla/string/src/phputf8/ucwords.php',
            '/libraries/vendor/joomla/string/src/phputf8/utf8.php',
            '/libraries/vendor/joomla/string/src/phputf8/utils/ascii.php',
            '/libraries/vendor/joomla/string/src/phputf8/utils/bad.php',
            '/libraries/vendor/joomla/string/src/phputf8/utils/patterns.php',
            '/libraries/vendor/joomla/string/src/phputf8/utils/position.php',
            '/libraries/vendor/joomla/string/src/phputf8/utils/specials.php',
            '/libraries/vendor/joomla/string/src/phputf8/utils/unicode.php',
            '/libraries/vendor/joomla/string/src/phputf8/utils/validation.php',
            '/libraries/vendor/laminas/laminas-diactoros/src/functions/create_uploaded_file.legacy.php',
            '/libraries/vendor/laminas/laminas-diactoros/src/functions/marshal_headers_from_sapi.legacy.php',
            '/libraries/vendor/laminas/laminas-diactoros/src/functions/marshal_method_from_sapi.legacy.php',
            '/libraries/vendor/laminas/laminas-diactoros/src/functions/marshal_protocol_version_from_sapi.legacy.php',
            '/libraries/vendor/laminas/laminas-diactoros/src/functions/marshal_uri_from_sapi.legacy.php',
            '/libraries/vendor/laminas/laminas-diactoros/src/functions/marshal_uri_from_sapi.php',
            '/libraries/vendor/laminas/laminas-diactoros/src/functions/normalize_server.legacy.php',
            '/libraries/vendor/laminas/laminas-diactoros/src/functions/normalize_uploaded_files.legacy.php',
            '/libraries/vendor/laminas/laminas-diactoros/src/functions/parse_cookie_header.legacy.php',
            '/libraries/vendor/laminas/laminas-diactoros/src/PhpInputStream.php',
            '/libraries/vendor/lcobucci/clock/renovate.json',
            '/libraries/vendor/symfony/http-client/Internal/AmpBody.php',
            '/libraries/vendor/symfony/http-client/Internal/AmpClientState.php',
            '/libraries/vendor/symfony/http-client/Internal/AmpListener.php',
            '/libraries/vendor/symfony/http-client/Internal/AmpResolver.php',
            '/libraries/vendor/symfony/http-client/Internal/LegacyHttplugInterface.php',
            '/libraries/vendor/symfony/http-client/Response/AmpResponse.php',
            '/libraries/vendor/symfony/polyfill-php83/bootstrap.php',
            '/libraries/vendor/symfony/polyfill-php83/bootstrap81.php',
            '/libraries/vendor/symfony/polyfill-php83/LICENSE',
            '/libraries/vendor/symfony/polyfill-php83/Php83.php',
            '/libraries/vendor/symfony/polyfill-php83/Resources/stubs/DateError.php',
            '/libraries/vendor/symfony/polyfill-php83/Resources/stubs/DateException.php',
            '/libraries/vendor/symfony/polyfill-php83/Resources/stubs/DateInvalidOperationException.php',
            '/libraries/vendor/symfony/polyfill-php83/Resources/stubs/DateInvalidTimeZoneException.php',
            '/libraries/vendor/symfony/polyfill-php83/Resources/stubs/DateMalformedIntervalStringException.php',
            '/libraries/vendor/symfony/polyfill-php83/Resources/stubs/DateMalformedPeriodStringException.php',
            '/libraries/vendor/symfony/polyfill-php83/Resources/stubs/DateMalformedStringException.php',
            '/libraries/vendor/symfony/polyfill-php83/Resources/stubs/DateObjectError.php',
            '/libraries/vendor/symfony/polyfill-php83/Resources/stubs/DateRangeError.php',
            '/libraries/vendor/symfony/polyfill-php83/Resources/stubs/Override.php',
            '/libraries/vendor/symfony/polyfill-php83/Resources/stubs/SQLite3Exception.php',
            '/media/legacy/js/ajax-chosen.js',
            '/media/legacy/js/ajax-chosen.min.js',
            '/media/legacy/js/ajax-chosen.min.js.gz',
            '/media/legacy/js/joomla-chosen.js',
            '/media/legacy/js/joomla-chosen.min.js',
            '/media/legacy/js/joomla-chosen.min.js.gz',
            '/media/system/js/fields/tag.js',
            '/media/system/js/fields/tag.min.js',
            '/media/system/js/fields/tag.min.js.gz',
            '/media/templates/site/cassiopeia/scss/vendor/_chosen.scss',
            '/media/vendor/chosen/css/chosen-sprite.png',
            '/media/vendor/chosen/css/chosen-sprite@2x.png',
            '/media/vendor/chosen/css/chosen.css',
            '/media/vendor/chosen/js/chosen.jquery.js',
            '/media/vendor/chosen/js/chosen.jquery.min.js',
            '/media/vendor/chosen/js/chosen.jquery.min.js.gz',
            '/media/vendor/tinymce/langs/af.js',
            '/media/vendor/tinymce/langs/af.min.js',
            '/media/vendor/tinymce/langs/af.min.js.gz',
            '/media/vendor/tinymce/langs/bg.js',
            '/media/vendor/tinymce/langs/bg.min.js',
            '/media/vendor/tinymce/langs/bg.min.js.gz',
            '/media/vendor/tinymce/langs/bs.js',
            '/media/vendor/tinymce/langs/bs.min.js',
            '/media/vendor/tinymce/langs/bs.min.js.gz',
            '/media/vendor/tinymce/langs/fo.js',
            '/media/vendor/tinymce/langs/fo.min.js',
            '/media/vendor/tinymce/langs/fo.min.js.gz',
            '/media/vendor/tinymce/langs/fr.js',
            '/media/vendor/tinymce/langs/fr.min.js',
            '/media/vendor/tinymce/langs/fr.min.js.gz',
            '/media/vendor/tinymce/langs/he.js',
            '/media/vendor/tinymce/langs/he.min.js',
            '/media/vendor/tinymce/langs/he.min.js.gz',
            '/media/vendor/tinymce/langs/hu.js',
            '/media/vendor/tinymce/langs/hu.min.js',
            '/media/vendor/tinymce/langs/hu.min.js.gz',
            '/media/vendor/tinymce/langs/ka.js',
            '/media/vendor/tinymce/langs/ka.min.js',
            '/media/vendor/tinymce/langs/ka.min.js.gz',
            '/media/vendor/tinymce/langs/km.js',
            '/media/vendor/tinymce/langs/km.min.js',
            '/media/vendor/tinymce/langs/km.min.js.gz',
            '/media/vendor/tinymce/langs/ko.js',
            '/media/vendor/tinymce/langs/ko.min.js',
            '/media/vendor/tinymce/langs/ko.min.js.gz',
            '/media/vendor/tinymce/langs/lb.js',
            '/media/vendor/tinymce/langs/lb.min.js',
            '/media/vendor/tinymce/langs/lb.min.js.gz',
            '/media/vendor/tinymce/langs/mk.js',
            '/media/vendor/tinymce/langs/mk.min.js',
            '/media/vendor/tinymce/langs/mk.min.js.gz',
            '/media/vendor/tinymce/langs/ms.js',
            '/media/vendor/tinymce/langs/ms.min.js',
            '/media/vendor/tinymce/langs/ms.min.js.gz',
            '/media/vendor/tinymce/langs/nb.js',
            '/media/vendor/tinymce/langs/nb.min.js',
            '/media/vendor/tinymce/langs/nb.min.js.gz',
            '/media/vendor/tinymce/langs/readme.md',
            '/media/vendor/tinymce/langs/si-LK.js',
            '/media/vendor/tinymce/langs/si-LK.min.js',
            '/media/vendor/tinymce/langs/si-LK.min.js.gz',
            '/media/vendor/tinymce/langs/sl.js',
            '/media/vendor/tinymce/langs/sl.min.js',
            '/media/vendor/tinymce/langs/sl.min.js.gz',
            '/media/vendor/tinymce/langs/sv.js',
            '/media/vendor/tinymce/langs/sv.min.js',
            '/media/vendor/tinymce/langs/sv.min.js.gz',
            '/media/vendor/tinymce/langs/sw.js',
            '/media/vendor/tinymce/langs/sw.min.js',
            '/media/vendor/tinymce/langs/sw.min.js.gz',
            '/media/vendor/tinymce/langs/sy.js',
            '/media/vendor/tinymce/langs/sy.min.js',
            '/media/vendor/tinymce/langs/sy.min.js.gz',
            '/media/vendor/tinymce/langs/th.js',
            '/media/vendor/tinymce/langs/th.min.js',
            '/media/vendor/tinymce/langs/th.min.js.gz',
            '/media/vendor/tinymce/license.txt',
            '/media/vendor/webcomponentsjs/js/webcomponents-bundle.js',
            '/media/vendor/webcomponentsjs/js/webcomponents-bundle.min.js',
            '/media/vendor/webcomponentsjs/js/webcomponents-bundle.min.js.gz',
            '/media/vendor/webcomponentsjs/LICENSE.md',
            '/modules/mod_finder/mod_finder.php',
            '/modules/mod_login/mod_login.php',
            '/modules/mod_menu/mod_menu.php',
            '/modules/mod_whosonline/mod_whosonline.php',
            // From 6.0.0-beta2 to 6.0.0-beta3
            '/media/templates/site/cassiopeia/css/global/colors_custom.css',
            '/media/templates/site/cassiopeia/css/global/colors_custom.min.css',
            '/media/templates/site/cassiopeia/css/global/colors_custom.min.css.gz',
            '/media/templates/site/cassiopeia/css/global/font_advanced.css',
            '/media/templates/site/cassiopeia/css/global/font_advanced.min.css',
            '/media/templates/site/cassiopeia/css/global/font_advanced.min.css.gz',
            '/media/templates/site/cassiopeia/scss/global/colors_custom.scss',
            '/media/templates/site/cassiopeia/scss/global/font_advanced.scss',
            // From 6.0.0-beta3 to 6.0.0-rc1
            '/administrator/language/en-GB/guidedtours.joomla_whatsnew_5_2.ini',
            '/administrator/language/en-GB/guidedtours.joomla_whatsnew_5_2_steps.ini',
            '/administrator/language/en-GB/guidedtours.joomla_whatsnew_5_3.ini',
            '/administrator/language/en-GB/guidedtours.joomla_whatsnew_5_3_steps.ini',
            '/administrator/language/en-GB/guidedtours.joomla_whatsnew_5_4.ini',
            '/administrator/language/en-GB/guidedtours.joomla_whatsnew_5_4_steps.ini',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/CachedWordInflector.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/GenericLanguageInflectorFactory.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Inflector.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/InflectorFactory.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Language.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/LanguageInflectorFactory.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/NoopWordInflector.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/English/Inflectible.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/English/InflectorFactory.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/English/Rules.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/English/Uninflected.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/French/Inflectible.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/French/InflectorFactory.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/French/Rules.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/French/Uninflected.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/NorwegianBokmal/Inflectible.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/NorwegianBokmal/InflectorFactory.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/NorwegianBokmal/Rules.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/NorwegianBokmal/Uninflected.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Pattern.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Patterns.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Portuguese/Inflectible.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Portuguese/InflectorFactory.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Portuguese/Rules.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Portuguese/Uninflected.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Ruleset.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Spanish/Inflectible.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Spanish/InflectorFactory.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Spanish/Rules.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Spanish/Uninflected.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Substitution.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Substitutions.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Transformation.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Transformations.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Turkish/Inflectible.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Turkish/InflectorFactory.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Turkish/Rules.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Turkish/Uninflected.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Word.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/RulesetInflector.php',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/WordInflector.php',
            '/media/com_guidedtours/images/5_2/enhancedtourheader.png',
            '/media/com_guidedtours/images/5_2/mailtemplate.png',
            '/media/com_guidedtours/images/5_3/filesdirectory.jpg',
            '/media/com_guidedtours/images/5_4/automated-updates.jpg',
        ];

        $folders = [
            // From 5.x to 6.0
            '/libraries/vendor/voku/portable-utf8/src/voku/helper/data',
            '/libraries/vendor/voku/portable-utf8/src/voku/helper',
            '/libraries/vendor/voku/portable-utf8/src/voku',
            '/libraries/vendor/voku/portable-utf8/src',
            '/libraries/vendor/voku/portable-utf8',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper/data',
            '/libraries/vendor/voku/portable-ascii/src/voku/helper',
            '/libraries/vendor/voku/portable-ascii/src/voku',
            '/libraries/vendor/voku/portable-ascii/src',
            '/libraries/vendor/voku/portable-ascii',
            '/libraries/vendor/voku',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Resolver',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Phar',
            '/libraries/vendor/typo3/phar-stream-wrapper/src/Interceptor',
            '/libraries/vendor/typo3/phar-stream-wrapper/src',
            '/libraries/vendor/typo3/phar-stream-wrapper',
            '/libraries/vendor/typo3',
            '/libraries/vendor/symfony/polyfill-iconv/Resources/charset',
            '/libraries/vendor/symfony/polyfill-iconv/Resources',
            '/libraries/vendor/symfony/polyfill-iconv',
            '/libraries/src/Filesystem/Support',
            '/libraries/src/Filesystem/Streams',
            '/libraries/src/Filesystem/Meta/language/en-GB',
            '/libraries/src/Filesystem/Meta/language',
            '/libraries/src/Filesystem/Meta',
            '/libraries/src/Filesystem',
            '/libraries/src/Application/CLI/Output/Processor',
            '/libraries/src/Application/CLI/Output',
            '/libraries/src/Application/CLI',
            '/administrator/components/com_finder/helpers/indexer',
            '/administrator/components/com_content/tmpl/featured',
            // From 6.0.0-alpha3 to 6.0.0-beta1
            '/media/vendor/webcomponentsjs/js',
            '/media/vendor/webcomponentsjs',
            '/media/vendor/chosen/js',
            '/media/vendor/chosen/css',
            '/media/vendor/chosen',
            '/libraries/vendor/symfony/polyfill-php83/Resources/stubs',
            '/libraries/vendor/symfony/polyfill-php83/Resources',
            '/libraries/vendor/symfony/polyfill-php83',
            '/libraries/vendor/joomla/string/src/phputf8/utils',
            '/libraries/vendor/joomla/string/src/phputf8/native',
            '/libraries/vendor/joomla/string/src/phputf8/mbstring',
            '/libraries/vendor/joomla/string/src/phputf8',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Common/Inflector',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Common',
            '/libraries/src/Adapter',
            // From 6.0.0-beta3 to 6.0.0-rc1
            '/media/com_guidedtours/images/5_4',
            '/media/com_guidedtours/images/5_3',
            '/media/com_guidedtours/images/5_2',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Turkish',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Spanish',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/Portuguese',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/NorwegianBokmal',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/French',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules/English',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector/Rules',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine/Inflector',
            '/libraries/vendor/doctrine/inflector/lib/Doctrine',
            '/libraries/vendor/doctrine/inflector/lib',
        ];

        $status['files_checked']   = $files;
        $status['folders_checked'] = $folders;

        foreach ($files as $file) {
            if (is_file(JPATH_ROOT . $file)) {
                $status['files_exist'][] = $file;

                if ($dryRun === false) {
                    if (File::delete(JPATH_ROOT . $file)) {
                        $status['files_deleted'][] = $file;
                    } else {
                        $status['files_errors'][] = \sprintf('Error on deleting file or folder %s', $file);
                    }
                }
            }
        }

        foreach ($folders as $folder) {
            if (is_dir(JPATH_ROOT . $folder)) {
                $status['folders_exist'][] = $folder;

                if ($dryRun === false) {
                    if (Folder::delete(JPATH_ROOT . $folder)) {
                        $status['folders_deleted'][] = $folder;
                    } else {
                        $status['folders_errors'][] = \sprintf('Error on deleting file or folder %s', $folder);
                    }
                }
            }
        }

        $this->fixFilenameCasing();

        if ($suppressOutput === false && \count($status['folders_errors'])) {
            echo implode('<br>', $status['folders_errors']);
        }

        if ($suppressOutput === false && \count($status['files_errors'])) {
            echo implode('<br>', $status['files_errors']);
        }

        return $status;
    }

    /**
     * Method to create assets for newly installed components
     *
     * @param   Installer  $installer  The class calling this method
     *
     * @return  boolean
     *
     * @since   3.2
     */
    public function updateAssets($installer)
    {
        // List all components added since 6.0
        $newComponents = [
            // Components to be added here
        ];

        foreach ($newComponents as $component) {
            $asset = new Asset(Factory::getDbo());

            if ($asset->loadByName($component)) {
                continue;
            }

            $asset->name      = $component;
            $asset->parent_id = 1;
            $asset->rules     = '{}';
            $asset->title     = $component;
            $asset->setLocation(1, 'last-child');

            if (!$asset->store()) {
                $this->collectError(__METHOD__, new \Exception($asset->getError(true)));

                // Install failed, roll back changes
                $installer->abort(Text::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_ROLLBACK', $asset->getError(true)));

                return false;
            }
        }

        return true;
    }

    /**
     * This method clean the Joomla Cache using the method `clean` from the com_cache model
     *
     * @return  void
     *
     * @since   3.5.1
     */
    private function cleanJoomlaCache()
    {
        /** @var \Joomla\Component\Cache\Administrator\Model\CacheModel $model */
        $model = Factory::getApplication()->bootComponent('com_cache')->getMVCFactory()
            ->createModel('Cache', 'Administrator', ['ignore_request' => true]);

        // Clean frontend cache
        $model->clean();

        // Clean admin cache
        $model->setState('client_id', 1);
        $model->clean();
    }

    /**
     * Called after any type of action
     *
     * @param   string     $action     Which action is happening (install|uninstall|discover_install|update)
     * @param   Installer  $installer  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   4.0.0
     */
    public function postflight($action, $installer)
    {
        if ($action !== 'update') {
            return true;
        }

        if (empty($this->fromVersion) || version_compare($this->fromVersion, '6.0.0', 'ge')) {
            return true;
        }

        // Add here code which shall be executed only when updating from an older version than 6.0.0

        // Refresh versionable assets cache.
        Factory::getApplication()->flushAssets();

        return true;
    }

    /**
     * Renames or removes incorrectly cased files.
     *
     * @return  void
     *
     * @since   3.9.25
     */
    protected function fixFilenameCasing()
    {
        $files = [
            // From 5.x to 6.0
        ];

        foreach ($files as $old => $expected) {
            $oldRealpath = realpath(JPATH_ROOT . $old);

            // On Unix without incorrectly cased file.
            if ($oldRealpath === false) {
                continue;
            }

            $oldBasename      = basename($oldRealpath);
            $newRealpath      = realpath(JPATH_ROOT . $expected);
            $newBasename      = basename($newRealpath);
            $expectedBasename = basename($expected);

            // On Windows or Unix with only the incorrectly cased file.
            if ($newBasename !== $expectedBasename) {
                // Rename the file.
                File::move(JPATH_ROOT . $old, JPATH_ROOT . $old . '.tmp');
                File::move(JPATH_ROOT . $old . '.tmp', JPATH_ROOT . $expected);

                continue;
            }

            // There might still be an incorrectly cased file on other OS than Windows.
            if ($oldBasename === basename($old)) {
                // Check if case-insensitive file system, eg on OSX.
                if (fileinode($oldRealpath) === fileinode($newRealpath)) {
                    // Check deeper because even realpath or glob might not return the actual case.
                    if (!\in_array($expectedBasename, scandir(\dirname($newRealpath)))) {
                        // Rename the file.
                        File::move(JPATH_ROOT . $old, JPATH_ROOT . $old . '.tmp');
                        File::move(JPATH_ROOT . $old . '.tmp', JPATH_ROOT . $expected);
                    }
                } else {
                    // On Unix with both files: Delete the incorrectly cased file.
                    if (is_file(JPATH_ROOT . $old)) {
                        File::delete(JPATH_ROOT . $old);
                    }
                }
            }
        }
    }
}
