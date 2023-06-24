<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;

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

                if (array_key_exists('version', $manifestValues)) {
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
        $options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
        $options['text_file'] = 'joomla_update.php';

        Log::addLogger($options, Log::INFO, ['Update', 'databasequery', 'jerror']);

        try {
            Log::add(Text::_('COM_JOOMLAUPDATE_UPDATE_LOG_DELETE_FILES'), Log::INFO, 'Update');
        } catch (RuntimeException $exception) {
            // Informational log only
        }

        // Uninstall extensions before removing their files and folders
        $this->uninstallExtensions();

        // This needs to stay for 2.5 update compatibility
        $this->deleteUnexistingFiles();
        $this->updateManifestCaches();
        $this->updateDatabase();
        $this->updateAssets($installer);
        $this->clearStatsCache();
        $this->cleanJoomlaCache();
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
                $db->getQuery(true)
                    ->select($db->quoteName('params'))
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote('stats'))
            )->loadResult();
        } catch (Exception $e) {
            echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

            return;
        }

        $params = json_decode($params, true);

        // Reset the last run parameter
        if (isset($params['lastrun'])) {
            $params['lastrun'] = '';
        }

        $params = json_encode($params);

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = ' . $db->quote($params))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('stats'));

        try {
            $db->setQuery($query)->execute();
        } catch (Exception $e) {
            echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

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
            echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

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
                echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

                return;
            }

            break;
        }
    }

    /**
     * Uninstall extensions and optionally migrate their parameters when
     * updating from a version older than 5.0.1.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function uninstallExtensions()
    {
        // Don't uninstall extensions when not updating from a version older than 5.0.1
        if (empty($this->fromVersion) || version_compare($this->fromVersion, '5.0.1', 'ge')) {
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
             */
             ['type' => 'plugin', 'element' => 'demotasks', 'folder' => 'task', 'client_id' => 0, 'pre_function' => null],
        ];

        $db = Factory::getDbo();

        foreach ($extensions as $extension) {
            $row = $db->setQuery(
                $db->getQuery(true)
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
                    $db->getQuery(true)
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
                echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';
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
        $query = $db->getQuery(true)
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
            echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

            return;
        }

        $installer = new Installer();
        $installer->setDatabase($db);

        foreach ($extensions as $extension) {
            if (!$installer->refreshManifestCache($extension->extension_id)) {
                echo Text::sprintf('FILES_JOOMLA_ERROR_MANIFEST', $extension->type, $extension->element, $extension->name, $extension->client_id) . '<br>';
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
            'folders_checked' => [],
            'files_checked'   => [],
        ];

        $files = [
            // From 4.4 to 5.0
            '/administrator/components/com_admin/sql/others/mysql/utf8mb4-conversion.sql',
            '/administrator/components/com_admin/sql/others/mysql/utf8mb4-conversion_optional.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2018-03-05.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2018-05-15.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2018-07-19.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2018-07-29.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2018-08-29.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-03-09.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-03-30.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-04-15.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-04-22.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-05-20.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-06-29.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-07-13.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-09-13.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-09-22.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-10-06.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2019-10-17.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-02-02.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-03-10.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-03-25.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-05-29.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-09-27.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2020-12-20.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-04-22.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-04-27.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-05-30.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-06-04.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-08-13.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.0-2021-08-17.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.3-2021-09-04.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.3-2021-09-05.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.0.6-2021-12-23.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2021-11-20.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2021-11-28.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2021-12-29.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2022-01-08.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2022-01-19.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.0-2022-01-24.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.1-2022-02-20.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.3-2022-04-07.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.1.3-2022-04-08.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.0-2022-05-15.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.0-2022-06-15.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.0-2022-06-19.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.0-2022-06-22.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.0-2022-07-07.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.1-2022-08-23.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.3-2022-09-07.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.7-2022-12-29.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.2.9-2023-03-07.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2022-09-23.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2022-11-06.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-01-30.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-02-15.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-02-25.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-03-07.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-03-09.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-03-10.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-03-28.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.0-2023-03-29.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.2-2023-03-31.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.2-2023-05-03.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.3.2-2023-05-20.sql',
            '/administrator/components/com_admin/sql/updates/mysql/4.4.0-2023-05-08.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2018-03-05.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2018-05-15.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2018-07-19.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2018-07-29.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2018-08-29.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-03-09.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-03-30.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-04-15.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-04-22.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-05-20.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-06-29.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-07-13.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-09-13.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-09-22.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-10-06.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2019-10-17.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-02-02.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-03-10.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-03-25.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-05-29.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-08-01.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-09-27.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2020-12-20.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-04-22.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-04-27.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-05-30.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-06-04.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-08-13.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.0-2021-08-17.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.3-2021-09-04.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.3-2021-09-05.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.0.6-2021-12-23.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2021-11-20.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2021-11-28.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2021-12-29.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2022-01-08.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2022-01-19.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.0-2022-01-24.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.1-2022-02-20.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.3-2022-04-07.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.1.3-2022-04-08.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.2.0-2022-05-15.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.2.0-2022-06-19.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.2.0-2022-06-22.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.2.0-2022-07-07.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.2.1-2022-08-23.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.2.3-2022-09-07.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.2.7-2022-12-29.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.2.9-2023-03-07.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2022-09-23.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2022-11-06.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-01-30.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-02-15.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-02-25.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-03-07.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-03-09.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-03-10.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-03-28.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.0-2023-03-29.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.2-2023-03-31.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.2-2023-05-03.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.3.2-2023-05-20.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.4.0-2023-05-08.sql',
            '/libraries/src/Schema/ChangeItem/SqlsrvChangeItem.php',
            '/libraries/vendor/beberlei/assert/lib/Assert/Assert.php',
            '/libraries/vendor/beberlei/assert/lib/Assert/Assertion.php',
            '/libraries/vendor/beberlei/assert/lib/Assert/AssertionChain.php',
            '/libraries/vendor/beberlei/assert/lib/Assert/AssertionFailedException.php',
            '/libraries/vendor/beberlei/assert/lib/Assert/functions.php',
            '/libraries/vendor/beberlei/assert/lib/Assert/InvalidArgumentException.php',
            '/libraries/vendor/beberlei/assert/lib/Assert/LazyAssertion.php',
            '/libraries/vendor/beberlei/assert/lib/Assert/LazyAssertionException.php',
            '/libraries/vendor/beberlei/assert/LICENSE',
            '/libraries/vendor/google/recaptcha/ARCHITECTURE.md',
            '/libraries/vendor/jfcherng/php-color-output/src/helpers.php',
            '/libraries/vendor/joomla/ldap/LICENSE',
            '/libraries/vendor/joomla/ldap/src/LdapClient.php',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/config/replacements.php',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/COPYRIGHT.md',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/LICENSE.md',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/src/autoload.php',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/src/Autoloader.php',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/src/ConfigPostProcessor.php',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/src/Module.php',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/src/Replacements.php',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/src/RewriteRules.php',
            '/libraries/vendor/lcobucci/jwt/compat/class-aliases.php',
            '/libraries/vendor/lcobucci/jwt/compat/json-exception-polyfill.php',
            '/libraries/vendor/lcobucci/jwt/compat/lcobucci-clock-polyfill.php',
            '/libraries/vendor/lcobucci/jwt/src/Claim.php',
            '/libraries/vendor/lcobucci/jwt/src/Claim/Basic.php',
            '/libraries/vendor/lcobucci/jwt/src/Claim/EqualsTo.php',
            '/libraries/vendor/lcobucci/jwt/src/Claim/Factory.php',
            '/libraries/vendor/lcobucci/jwt/src/Claim/GreaterOrEqualsTo.php',
            '/libraries/vendor/lcobucci/jwt/src/Claim/LesserOrEqualsTo.php',
            '/libraries/vendor/lcobucci/jwt/src/Claim/Validatable.php',
            '/libraries/vendor/lcobucci/jwt/src/Parsing/Decoder.php',
            '/libraries/vendor/lcobucci/jwt/src/Parsing/Encoder.php',
            '/libraries/vendor/lcobucci/jwt/src/Signature.php',
            '/libraries/vendor/lcobucci/jwt/src/Signer/BaseSigner.php',
            '/libraries/vendor/lcobucci/jwt/src/Signer/Keychain.php',
            '/libraries/vendor/lcobucci/jwt/src/ValidationData.php',
            '/libraries/vendor/nyholm/psr7/LICENSE',
            '/libraries/vendor/nyholm/psr7/phpstan-baseline.neon',
            '/libraries/vendor/nyholm/psr7/psalm.baseline.xml',
            '/libraries/vendor/nyholm/psr7/src/Factory/HttplugFactory.php',
            '/libraries/vendor/nyholm/psr7/src/Factory/Psr17Factory.php',
            '/libraries/vendor/nyholm/psr7/src/MessageTrait.php',
            '/libraries/vendor/nyholm/psr7/src/Request.php',
            '/libraries/vendor/nyholm/psr7/src/RequestTrait.php',
            '/libraries/vendor/nyholm/psr7/src/Response.php',
            '/libraries/vendor/nyholm/psr7/src/ServerRequest.php',
            '/libraries/vendor/nyholm/psr7/src/Stream.php',
            '/libraries/vendor/nyholm/psr7/src/StreamTrait.php',
            '/libraries/vendor/nyholm/psr7/src/UploadedFile.php',
            '/libraries/vendor/nyholm/psr7/src/Uri.php',
            '/libraries/vendor/psr/log/Psr/Log/AbstractLogger.php',
            '/libraries/vendor/psr/log/Psr/Log/InvalidArgumentException.php',
            '/libraries/vendor/psr/log/Psr/Log/LoggerAwareInterface.php',
            '/libraries/vendor/psr/log/Psr/Log/LoggerAwareTrait.php',
            '/libraries/vendor/psr/log/Psr/Log/LoggerInterface.php',
            '/libraries/vendor/psr/log/Psr/Log/LoggerTrait.php',
            '/libraries/vendor/psr/log/Psr/Log/LogLevel.php',
            '/libraries/vendor/psr/log/Psr/Log/NullLogger.php',
            '/libraries/vendor/ramsey/uuid/LICENSE',
            '/libraries/vendor/ramsey/uuid/src/BinaryUtils.php',
            '/libraries/vendor/ramsey/uuid/src/Builder/DefaultUuidBuilder.php',
            '/libraries/vendor/ramsey/uuid/src/Builder/DegradedUuidBuilder.php',
            '/libraries/vendor/ramsey/uuid/src/Builder/UuidBuilderInterface.php',
            '/libraries/vendor/ramsey/uuid/src/Codec/CodecInterface.php',
            '/libraries/vendor/ramsey/uuid/src/Codec/GuidStringCodec.php',
            '/libraries/vendor/ramsey/uuid/src/Codec/OrderedTimeCodec.php',
            '/libraries/vendor/ramsey/uuid/src/Codec/StringCodec.php',
            '/libraries/vendor/ramsey/uuid/src/Codec/TimestampFirstCombCodec.php',
            '/libraries/vendor/ramsey/uuid/src/Codec/TimestampLastCombCodec.php',
            '/libraries/vendor/ramsey/uuid/src/Converter/Number/BigNumberConverter.php',
            '/libraries/vendor/ramsey/uuid/src/Converter/Number/DegradedNumberConverter.php',
            '/libraries/vendor/ramsey/uuid/src/Converter/NumberConverterInterface.php',
            '/libraries/vendor/ramsey/uuid/src/Converter/Time/BigNumberTimeConverter.php',
            '/libraries/vendor/ramsey/uuid/src/Converter/Time/DegradedTimeConverter.php',
            '/libraries/vendor/ramsey/uuid/src/Converter/Time/PhpTimeConverter.php',
            '/libraries/vendor/ramsey/uuid/src/Converter/TimeConverterInterface.php',
            '/libraries/vendor/ramsey/uuid/src/DegradedUuid.php',
            '/libraries/vendor/ramsey/uuid/src/Exception/InvalidUuidStringException.php',
            '/libraries/vendor/ramsey/uuid/src/Exception/UnsatisfiedDependencyException.php',
            '/libraries/vendor/ramsey/uuid/src/Exception/UnsupportedOperationException.php',
            '/libraries/vendor/ramsey/uuid/src/FeatureSet.php',
            '/libraries/vendor/ramsey/uuid/src/functions.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/CombGenerator.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/DefaultTimeGenerator.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/MtRandGenerator.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/OpenSslGenerator.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/PeclUuidRandomGenerator.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/PeclUuidTimeGenerator.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/RandomBytesGenerator.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/RandomGeneratorFactory.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/RandomGeneratorInterface.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/RandomLibAdapter.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/SodiumRandomGenerator.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/TimeGeneratorFactory.php',
            '/libraries/vendor/ramsey/uuid/src/Generator/TimeGeneratorInterface.php',
            '/libraries/vendor/ramsey/uuid/src/Provider/Node/FallbackNodeProvider.php',
            '/libraries/vendor/ramsey/uuid/src/Provider/Node/RandomNodeProvider.php',
            '/libraries/vendor/ramsey/uuid/src/Provider/Node/SystemNodeProvider.php',
            '/libraries/vendor/ramsey/uuid/src/Provider/NodeProviderInterface.php',
            '/libraries/vendor/ramsey/uuid/src/Provider/Time/FixedTimeProvider.php',
            '/libraries/vendor/ramsey/uuid/src/Provider/Time/SystemTimeProvider.php',
            '/libraries/vendor/ramsey/uuid/src/Provider/TimeProviderInterface.php',
            '/libraries/vendor/ramsey/uuid/src/Uuid.php',
            '/libraries/vendor/ramsey/uuid/src/UuidFactory.php',
            '/libraries/vendor/ramsey/uuid/src/UuidFactoryInterface.php',
            '/libraries/vendor/ramsey/uuid/src/UuidInterface.php',
            '/libraries/vendor/spomky-labs/base64url/LICENSE',
            '/libraries/vendor/spomky-labs/base64url/src/Base64Url.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/ByteStringWithChunkObject.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/InfiniteListObject.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/InfiniteMapObject.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/SignedIntegerObject.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/Tag/EpochTag.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/Tag/PositiveBigIntegerTag.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/Tag/TagObjectManager.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/TagObject.php',
            '/libraries/vendor/spomky-labs/cbor-php/src/TextStringWithChunkObject.php',
            '/libraries/vendor/symfony/polyfill-php72/bootstrap.php',
            '/libraries/vendor/symfony/polyfill-php72/LICENSE',
            '/libraries/vendor/symfony/polyfill-php72/Php72.php',
            '/libraries/vendor/symfony/polyfill-php73/bootstrap.php',
            '/libraries/vendor/symfony/polyfill-php73/LICENSE',
            '/libraries/vendor/symfony/polyfill-php73/Php73.php',
            '/libraries/vendor/symfony/polyfill-php73/Resources/stubs/JsonException.php',
            '/libraries/vendor/symfony/polyfill-php80/bootstrap.php',
            '/libraries/vendor/symfony/polyfill-php80/LICENSE',
            '/libraries/vendor/symfony/polyfill-php80/Php80.php',
            '/libraries/vendor/symfony/polyfill-php80/PhpToken.php',
            '/libraries/vendor/symfony/polyfill-php80/Resources/stubs/Attribute.php',
            '/libraries/vendor/symfony/polyfill-php80/Resources/stubs/PhpToken.php',
            '/libraries/vendor/symfony/polyfill-php80/Resources/stubs/Stringable.php',
            '/libraries/vendor/symfony/polyfill-php80/Resources/stubs/UnhandledMatchError.php',
            '/libraries/vendor/symfony/polyfill-php80/Resources/stubs/ValueError.php',
            '/libraries/vendor/symfony/polyfill-php81/bootstrap.php',
            '/libraries/vendor/symfony/polyfill-php81/LICENSE',
            '/libraries/vendor/symfony/polyfill-php81/Php81.php',
            '/libraries/vendor/symfony/polyfill-php81/Resources/stubs/ReturnTypeWillChange.php',
            '/libraries/vendor/web-auth/cose-lib/src/Verifier.php',
            '/libraries/vendor/web-auth/metadata-service/src/AuthenticatorStatus.php',
            '/libraries/vendor/web-auth/metadata-service/src/BiometricAccuracyDescriptor.php',
            '/libraries/vendor/web-auth/metadata-service/src/BiometricStatusReport.php',
            '/libraries/vendor/web-auth/metadata-service/src/CodeAccuracyDescriptor.php',
            '/libraries/vendor/web-auth/metadata-service/src/DisplayPNGCharacteristicsDescriptor.php',
            '/libraries/vendor/web-auth/metadata-service/src/DistantSingleMetadata.php',
            '/libraries/vendor/web-auth/metadata-service/src/DistantSingleMetadataFactory.php',
            '/libraries/vendor/web-auth/metadata-service/src/EcdaaTrustAnchor.php',
            '/libraries/vendor/web-auth/metadata-service/src/ExtensionDescriptor.php',
            '/libraries/vendor/web-auth/metadata-service/src/MetadataService.php',
            '/libraries/vendor/web-auth/metadata-service/src/MetadataServiceFactory.php',
            '/libraries/vendor/web-auth/metadata-service/src/MetadataStatement.php',
            '/libraries/vendor/web-auth/metadata-service/src/MetadataStatementFetcher.php',
            '/libraries/vendor/web-auth/metadata-service/src/MetadataTOCPayload.php',
            '/libraries/vendor/web-auth/metadata-service/src/MetadataTOCPayloadEntry.php',
            '/libraries/vendor/web-auth/metadata-service/src/PatternAccuracyDescriptor.php',
            '/libraries/vendor/web-auth/metadata-service/src/RgbPaletteEntry.php',
            '/libraries/vendor/web-auth/metadata-service/src/RogueListEntry.php',
            '/libraries/vendor/web-auth/metadata-service/src/SimpleMetadataStatementRepository.php',
            '/libraries/vendor/web-auth/metadata-service/src/SingleMetadata.php',
            '/libraries/vendor/web-auth/metadata-service/src/StatusReport.php',
            '/libraries/vendor/web-auth/metadata-service/src/VerificationMethodANDCombinations.php',
            '/libraries/vendor/web-auth/metadata-service/src/VerificationMethodDescriptor.php',
            '/libraries/vendor/web-auth/metadata-service/src/Version.php',
            '/libraries/vendor/web-auth/webauthn-lib/src/Server.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-rsa/RSA.php',
            '/media/vendor/fontawesome-free/scss/_larger.scss',
            '/media/vendor/fontawesome-free/webfonts/fa-brands-400.eot',
            '/media/vendor/fontawesome-free/webfonts/fa-brands-400.svg',
            '/media/vendor/fontawesome-free/webfonts/fa-brands-400.woff',
            '/media/vendor/fontawesome-free/webfonts/fa-regular-400.eot',
            '/media/vendor/fontawesome-free/webfonts/fa-regular-400.svg',
            '/media/vendor/fontawesome-free/webfonts/fa-regular-400.woff',
            '/media/vendor/fontawesome-free/webfonts/fa-solid-900.eot',
            '/media/vendor/fontawesome-free/webfonts/fa-solid-900.svg',
            '/media/vendor/fontawesome-free/webfonts/fa-solid-900.woff',
            '/media/vendor/tinymce/plugins/bbcode/index.js',
            '/media/vendor/tinymce/plugins/bbcode/plugin.js',
            '/media/vendor/tinymce/plugins/bbcode/plugin.min.js',
            '/media/vendor/tinymce/plugins/bbcode/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/colorpicker/index.js',
            '/media/vendor/tinymce/plugins/colorpicker/plugin.js',
            '/media/vendor/tinymce/plugins/colorpicker/plugin.min.js',
            '/media/vendor/tinymce/plugins/colorpicker/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/contextmenu/index.js',
            '/media/vendor/tinymce/plugins/contextmenu/plugin.js',
            '/media/vendor/tinymce/plugins/contextmenu/plugin.min.js',
            '/media/vendor/tinymce/plugins/contextmenu/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/fullpage/index.js',
            '/media/vendor/tinymce/plugins/fullpage/plugin.js',
            '/media/vendor/tinymce/plugins/fullpage/plugin.min.js',
            '/media/vendor/tinymce/plugins/fullpage/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/hr/index.js',
            '/media/vendor/tinymce/plugins/hr/plugin.js',
            '/media/vendor/tinymce/plugins/hr/plugin.min.js',
            '/media/vendor/tinymce/plugins/hr/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/imagetools/index.js',
            '/media/vendor/tinymce/plugins/imagetools/plugin.js',
            '/media/vendor/tinymce/plugins/imagetools/plugin.min.js',
            '/media/vendor/tinymce/plugins/imagetools/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/legacyoutput/index.js',
            '/media/vendor/tinymce/plugins/legacyoutput/plugin.js',
            '/media/vendor/tinymce/plugins/legacyoutput/plugin.min.js',
            '/media/vendor/tinymce/plugins/legacyoutput/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/noneditable/index.js',
            '/media/vendor/tinymce/plugins/noneditable/plugin.js',
            '/media/vendor/tinymce/plugins/noneditable/plugin.min.js',
            '/media/vendor/tinymce/plugins/noneditable/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/paste/index.js',
            '/media/vendor/tinymce/plugins/paste/plugin.js',
            '/media/vendor/tinymce/plugins/paste/plugin.min.js',
            '/media/vendor/tinymce/plugins/paste/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/print/index.js',
            '/media/vendor/tinymce/plugins/print/plugin.js',
            '/media/vendor/tinymce/plugins/print/plugin.min.js',
            '/media/vendor/tinymce/plugins/print/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/spellchecker/index.js',
            '/media/vendor/tinymce/plugins/spellchecker/plugin.js',
            '/media/vendor/tinymce/plugins/spellchecker/plugin.min.js',
            '/media/vendor/tinymce/plugins/spellchecker/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/tabfocus/index.js',
            '/media/vendor/tinymce/plugins/tabfocus/plugin.js',
            '/media/vendor/tinymce/plugins/tabfocus/plugin.min.js',
            '/media/vendor/tinymce/plugins/tabfocus/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/template/index.js',
            '/media/vendor/tinymce/plugins/template/plugin.js',
            '/media/vendor/tinymce/plugins/template/plugin.min.js',
            '/media/vendor/tinymce/plugins/template/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/textcolor/index.js',
            '/media/vendor/tinymce/plugins/textcolor/plugin.js',
            '/media/vendor/tinymce/plugins/textcolor/plugin.min.js',
            '/media/vendor/tinymce/plugins/textcolor/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/textpattern/index.js',
            '/media/vendor/tinymce/plugins/textpattern/plugin.js',
            '/media/vendor/tinymce/plugins/textpattern/plugin.min.js',
            '/media/vendor/tinymce/plugins/textpattern/plugin.min.js.gz',
            '/media/vendor/tinymce/plugins/toc/index.js',
            '/media/vendor/tinymce/plugins/toc/plugin.js',
            '/media/vendor/tinymce/plugins/toc/plugin.min.js',
            '/media/vendor/tinymce/plugins/toc/plugin.min.js.gz',
            '/media/vendor/tinymce/skins/ui/oxide-dark/content.mobile.css',
            '/media/vendor/tinymce/skins/ui/oxide-dark/content.mobile.min.css',
            '/media/vendor/tinymce/skins/ui/oxide-dark/content.mobile.min.css.gz',
            '/media/vendor/tinymce/skins/ui/oxide-dark/fonts/tinymce-mobile.woff',
            '/media/vendor/tinymce/skins/ui/oxide-dark/skin.mobile.css',
            '/media/vendor/tinymce/skins/ui/oxide-dark/skin.mobile.min.css',
            '/media/vendor/tinymce/skins/ui/oxide-dark/skin.mobile.min.css.gz',
            '/media/vendor/tinymce/skins/ui/oxide/content.mobile.css',
            '/media/vendor/tinymce/skins/ui/oxide/content.mobile.min.css',
            '/media/vendor/tinymce/skins/ui/oxide/content.mobile.min.css.gz',
            '/media/vendor/tinymce/skins/ui/oxide/fonts/tinymce-mobile.woff',
            '/media/vendor/tinymce/skins/ui/oxide/skin.mobile.css',
            '/media/vendor/tinymce/skins/ui/oxide/skin.mobile.min.css',
            '/media/vendor/tinymce/skins/ui/oxide/skin.mobile.min.css.gz',
            '/media/vendor/tinymce/themes/mobile/index.js',
            '/media/vendor/tinymce/themes/mobile/theme.js',
            '/media/vendor/tinymce/themes/mobile/theme.min.js',
            '/media/vendor/tinymce/themes/mobile/theme.min.js.gz',
            '/plugins/multifactorauth/webauthn/src/Hotfix/AndroidKeyAttestationStatementSupport.php',
            '/plugins/multifactorauth/webauthn/src/Hotfix/FidoU2FAttestationStatementSupport.php',
            '/plugins/multifactorauth/webauthn/src/Hotfix/Server.php',
            '/plugins/system/webauthn/src/Hotfix/AndroidKeyAttestationStatementSupport.php',
            '/plugins/system/webauthn/src/Hotfix/FidoU2FAttestationStatementSupport.php',
            '/plugins/system/webauthn/src/Hotfix/Server.php',
            // From 5.0.0-alpha1 to 5.0.0-alpha2
            '/administrator/language/en-GB/plg_task_demotasks.ini',
            '/administrator/language/en-GB/plg_task_demotasks.sys.ini',
            '/libraries/vendor/jfcherng/php-diff/src/languages/readme.txt',
            '/media/com_actionlogs/js/admin-actionlogs-default-es5.js',
            '/media/com_actionlogs/js/admin-actionlogs-default-es5.min.js',
            '/media/com_actionlogs/js/admin-actionlogs-default-es5.min.js.gz',
            '/media/com_admin/js/admin-help-es5.js',
            '/media/com_admin/js/admin-help-es5.min.js',
            '/media/com_admin/js/admin-help-es5.min.js.gz',
            '/media/com_associations/js/admin-associations-default-es5.js',
            '/media/com_associations/js/admin-associations-default-es5.min.js',
            '/media/com_associations/js/admin-associations-default-es5.min.js.gz',
            '/media/com_associations/js/admin-associations-modal-es5.js',
            '/media/com_associations/js/admin-associations-modal-es5.min.js',
            '/media/com_associations/js/admin-associations-modal-es5.min.js.gz',
            '/media/com_associations/js/associations-edit-es5.js',
            '/media/com_associations/js/associations-edit-es5.min.js',
            '/media/com_associations/js/associations-edit-es5.min.js.gz',
            '/media/com_banners/js/admin-banner-edit-es5.js',
            '/media/com_banners/js/admin-banner-edit-es5.min.js',
            '/media/com_banners/js/admin-banner-edit-es5.min.js.gz',
            '/media/com_cache/js/admin-cache-default-es5.js',
            '/media/com_cache/js/admin-cache-default-es5.min.js',
            '/media/com_cache/js/admin-cache-default-es5.min.js.gz',
            '/media/com_categories/js/shared-categories-accordion-es5.js',
            '/media/com_categories/js/shared-categories-accordion-es5.min.js',
            '/media/com_categories/js/shared-categories-accordion-es5.min.js.gz',
            '/media/com_config/js/config-default-es5.js',
            '/media/com_config/js/config-default-es5.min.js',
            '/media/com_config/js/config-default-es5.min.js.gz',
            '/media/com_config/js/config-filters-es5.js',
            '/media/com_config/js/config-filters-es5.min.js',
            '/media/com_config/js/config-filters-es5.min.js.gz',
            '/media/com_config/js/modules-default-es5.js',
            '/media/com_config/js/modules-default-es5.min.js',
            '/media/com_config/js/modules-default-es5.min.js.gz',
            '/media/com_config/js/templates-default-es5.js',
            '/media/com_config/js/templates-default-es5.min.js',
            '/media/com_config/js/templates-default-es5.min.js.gz',
            '/media/com_contact/js/admin-contacts-modal-es5.js',
            '/media/com_contact/js/admin-contacts-modal-es5.min.js',
            '/media/com_contact/js/admin-contacts-modal-es5.min.js.gz',
            '/media/com_contact/js/contacts-list-es5.js',
            '/media/com_contact/js/contacts-list-es5.min.js',
            '/media/com_contact/js/contacts-list-es5.min.js.gz',
            '/media/com_content/js/admin-article-pagebreak-es5.js',
            '/media/com_content/js/admin-article-pagebreak-es5.min.js',
            '/media/com_content/js/admin-article-pagebreak-es5.min.js.gz',
            '/media/com_content/js/admin-article-readmore-es5.js',
            '/media/com_content/js/admin-article-readmore-es5.min.js',
            '/media/com_content/js/admin-article-readmore-es5.min.js.gz',
            '/media/com_content/js/admin-articles-default-batch-footer-es5.js',
            '/media/com_content/js/admin-articles-default-batch-footer-es5.min.js',
            '/media/com_content/js/admin-articles-default-batch-footer-es5.min.js.gz',
            '/media/com_content/js/admin-articles-default-stage-footer-es5.js',
            '/media/com_content/js/admin-articles-default-stage-footer-es5.min.js',
            '/media/com_content/js/admin-articles-default-stage-footer-es5.min.js.gz',
            '/media/com_content/js/admin-articles-modal-es5.js',
            '/media/com_content/js/admin-articles-modal-es5.min.js',
            '/media/com_content/js/admin-articles-modal-es5.min.js.gz',
            '/media/com_content/js/articles-list-es5.js',
            '/media/com_content/js/articles-list-es5.min.js',
            '/media/com_content/js/articles-list-es5.min.js.gz',
            '/media/com_content/js/articles-status-es5.js',
            '/media/com_content/js/articles-status-es5.min.js',
            '/media/com_content/js/articles-status-es5.min.js.gz',
            '/media/com_content/js/form-edit-es5.js',
            '/media/com_content/js/form-edit-es5.min.js',
            '/media/com_content/js/form-edit-es5.min.js.gz',
            '/media/com_contenthistory/js/admin-compare-compare-es5.js',
            '/media/com_contenthistory/js/admin-compare-compare-es5.min.js',
            '/media/com_contenthistory/js/admin-compare-compare-es5.min.js.gz',
            '/media/com_contenthistory/js/admin-history-modal-es5.js',
            '/media/com_contenthistory/js/admin-history-modal-es5.min.js',
            '/media/com_contenthistory/js/admin-history-modal-es5.min.js.gz',
            '/media/com_contenthistory/js/admin-history-versions-es5.js',
            '/media/com_contenthistory/js/admin-history-versions-es5.min.js',
            '/media/com_contenthistory/js/admin-history-versions-es5.min.js.gz',
            '/media/com_cpanel/js/admin-add_module-es5.js',
            '/media/com_cpanel/js/admin-add_module-es5.min.js',
            '/media/com_cpanel/js/admin-add_module-es5.min.js.gz',
            '/media/com_cpanel/js/admin-cpanel-default-es5.js',
            '/media/com_cpanel/js/admin-cpanel-default-es5.min.js',
            '/media/com_cpanel/js/admin-cpanel-default-es5.min.js.gz',
            '/media/com_cpanel/js/admin-system-loader-es5.js',
            '/media/com_cpanel/js/admin-system-loader-es5.min.js',
            '/media/com_cpanel/js/admin-system-loader-es5.min.js.gz',
            '/media/com_fields/js/admin-field-changecontext-es5.js',
            '/media/com_fields/js/admin-field-changecontext-es5.min.js',
            '/media/com_fields/js/admin-field-changecontext-es5.min.js.gz',
            '/media/com_fields/js/admin-field-edit-es5.js',
            '/media/com_fields/js/admin-field-edit-es5.min.js',
            '/media/com_fields/js/admin-field-edit-es5.min.js.gz',
            '/media/com_fields/js/admin-field-typehaschanged-es5.js',
            '/media/com_fields/js/admin-field-typehaschanged-es5.min.js',
            '/media/com_fields/js/admin-field-typehaschanged-es5.min.js.gz',
            '/media/com_fields/js/admin-fields-default-batch-es5.js',
            '/media/com_fields/js/admin-fields-default-batch-es5.min.js',
            '/media/com_fields/js/admin-fields-default-batch-es5.min.js.gz',
            '/media/com_fields/js/admin-fields-modal-es5.js',
            '/media/com_fields/js/admin-fields-modal-es5.min.js',
            '/media/com_fields/js/admin-fields-modal-es5.min.js.gz',
            '/media/com_finder/js/debug-es5.js',
            '/media/com_finder/js/debug-es5.min.js',
            '/media/com_finder/js/debug-es5.min.js.gz',
            '/media/com_finder/js/filters-es5.js',
            '/media/com_finder/js/filters-es5.min.js',
            '/media/com_finder/js/filters-es5.min.js.gz',
            '/media/com_finder/js/finder-edit-es5.js',
            '/media/com_finder/js/finder-edit-es5.min.js',
            '/media/com_finder/js/finder-edit-es5.min.js.gz',
            '/media/com_finder/js/finder-es5.js',
            '/media/com_finder/js/finder-es5.min.js',
            '/media/com_finder/js/finder-es5.min.js.gz',
            '/media/com_finder/js/indexer-es5.js',
            '/media/com_finder/js/indexer-es5.min.js',
            '/media/com_finder/js/indexer-es5.min.js.gz',
            '/media/com_finder/js/maps-es5.js',
            '/media/com_finder/js/maps-es5.min.js',
            '/media/com_finder/js/maps-es5.min.js.gz',
            '/media/com_installer/js/changelog-es5.js',
            '/media/com_installer/js/changelog-es5.min.js',
            '/media/com_installer/js/changelog-es5.min.js.gz',
            '/media/com_installer/js/installer-es5.js',
            '/media/com_installer/js/installer-es5.min.js',
            '/media/com_installer/js/installer-es5.min.js.gz',
            '/media/com_joomlaupdate/js/admin-update-default-es5.js',
            '/media/com_joomlaupdate/js/admin-update-default-es5.min.js',
            '/media/com_joomlaupdate/js/admin-update-default-es5.min.js.gz',
            '/media/com_joomlaupdate/js/default-es5.js',
            '/media/com_joomlaupdate/js/default-es5.min.js',
            '/media/com_joomlaupdate/js/default-es5.min.js.gz',
            '/media/com_languages/js/admin-language-edit-change-flag-es5.js',
            '/media/com_languages/js/admin-language-edit-change-flag-es5.min.js',
            '/media/com_languages/js/admin-language-edit-change-flag-es5.min.js.gz',
            '/media/com_languages/js/admin-override-edit-refresh-searchstring-es5.js',
            '/media/com_languages/js/admin-override-edit-refresh-searchstring-es5.min.js',
            '/media/com_languages/js/admin-override-edit-refresh-searchstring-es5.min.js.gz',
            '/media/com_languages/js/overrider-es5.js',
            '/media/com_languages/js/overrider-es5.min.js',
            '/media/com_languages/js/overrider-es5.min.js.gz',
            '/media/com_mails/js/admin-email-template-edit-es5.js',
            '/media/com_mails/js/admin-email-template-edit-es5.min.js',
            '/media/com_mails/js/admin-email-template-edit-es5.min.js.gz',
            '/media/com_media/js/edit-images-es5.js',
            '/media/com_media/js/edit-images-es5.min.js',
            '/media/com_media/js/edit-images-es5.min.js.gz',
            '/media/com_media/js/media-manager-es5.js',
            '/media/com_media/js/media-manager-es5.min.js',
            '/media/com_media/js/media-manager-es5.min.js.gz',
            '/media/com_menus/js/admin-item-edit-es5.js',
            '/media/com_menus/js/admin-item-edit-es5.min.js',
            '/media/com_menus/js/admin-item-edit-es5.min.js.gz',
            '/media/com_menus/js/admin-item-edit_container-es5.js',
            '/media/com_menus/js/admin-item-edit_container-es5.min.js',
            '/media/com_menus/js/admin-item-edit_container-es5.min.js.gz',
            '/media/com_menus/js/admin-item-edit_modules-es5.js',
            '/media/com_menus/js/admin-item-edit_modules-es5.min.js',
            '/media/com_menus/js/admin-item-edit_modules-es5.min.js.gz',
            '/media/com_menus/js/admin-item-modal-es5.js',
            '/media/com_menus/js/admin-item-modal-es5.min.js',
            '/media/com_menus/js/admin-item-modal-es5.min.js.gz',
            '/media/com_menus/js/admin-items-modal-es5.js',
            '/media/com_menus/js/admin-items-modal-es5.min.js',
            '/media/com_menus/js/admin-items-modal-es5.min.js.gz',
            '/media/com_menus/js/admin-menus-default-es5.js',
            '/media/com_menus/js/admin-menus-default-es5.min.js',
            '/media/com_menus/js/admin-menus-default-es5.min.js.gz',
            '/media/com_menus/js/default-batch-body-es5.js',
            '/media/com_menus/js/default-batch-body-es5.min.js',
            '/media/com_menus/js/default-batch-body-es5.min.js.gz',
            '/media/com_modules/js/admin-module-edit-es5.js',
            '/media/com_modules/js/admin-module-edit-es5.min.js',
            '/media/com_modules/js/admin-module-edit-es5.min.js.gz',
            '/media/com_modules/js/admin-module-edit_assignment-es5.js',
            '/media/com_modules/js/admin-module-edit_assignment-es5.min.js',
            '/media/com_modules/js/admin-module-edit_assignment-es5.min.js.gz',
            '/media/com_modules/js/admin-module-search-es5.js',
            '/media/com_modules/js/admin-module-search-es5.min.js',
            '/media/com_modules/js/admin-module-search-es5.min.js.gz',
            '/media/com_modules/js/admin-modules-modal-es5.js',
            '/media/com_modules/js/admin-modules-modal-es5.min.js',
            '/media/com_modules/js/admin-modules-modal-es5.min.js.gz',
            '/media/com_modules/js/admin-select-modal-es5.js',
            '/media/com_modules/js/admin-select-modal-es5.min.js',
            '/media/com_modules/js/admin-select-modal-es5.min.js.gz',
            '/media/com_scheduler/js/admin-view-run-test-task-es5.js',
            '/media/com_scheduler/js/admin-view-run-test-task-es5.min.js',
            '/media/com_scheduler/js/admin-view-run-test-task-es5.min.js.gz',
            '/media/com_scheduler/js/admin-view-select-task-search-es5.js',
            '/media/com_scheduler/js/admin-view-select-task-search-es5.min.js',
            '/media/com_scheduler/js/admin-view-select-task-search-es5.min.js.gz',
            '/media/com_scheduler/js/scheduler-config-es5.js',
            '/media/com_scheduler/js/scheduler-config-es5.min.js',
            '/media/com_scheduler/js/scheduler-config-es5.min.js.gz',
            '/media/com_tags/js/tag-default-es5.js',
            '/media/com_tags/js/tag-default-es5.min.js',
            '/media/com_tags/js/tag-default-es5.min.js.gz',
            '/media/com_tags/js/tag-list-es5.js',
            '/media/com_tags/js/tag-list-es5.min.js',
            '/media/com_tags/js/tag-list-es5.min.js.gz',
            '/media/com_tags/js/tags-default-es5.js',
            '/media/com_tags/js/tags-default-es5.min.js',
            '/media/com_tags/js/tags-default-es5.min.js.gz',
            '/media/com_templates/js/admin-template-compare-es5.js',
            '/media/com_templates/js/admin-template-compare-es5.min.js',
            '/media/com_templates/js/admin-template-compare-es5.min.js.gz',
            '/media/com_templates/js/admin-template-toggle-assignment-es5.js',
            '/media/com_templates/js/admin-template-toggle-assignment-es5.min.js',
            '/media/com_templates/js/admin-template-toggle-assignment-es5.min.js.gz',
            '/media/com_templates/js/admin-template-toggle-switch-es5.js',
            '/media/com_templates/js/admin-template-toggle-switch-es5.min.js',
            '/media/com_templates/js/admin-template-toggle-switch-es5.min.js.gz',
            '/media/com_templates/js/admin-templates-default-es5.js',
            '/media/com_templates/js/admin-templates-default-es5.min.js',
            '/media/com_templates/js/admin-templates-default-es5.min.js.gz',
            '/media/com_users/js/admin-users-groups-es5.js',
            '/media/com_users/js/admin-users-groups-es5.min.js',
            '/media/com_users/js/admin-users-groups-es5.min.js.gz',
            '/media/com_users/js/admin-users-mail-es5.js',
            '/media/com_users/js/admin-users-mail-es5.min.js',
            '/media/com_users/js/admin-users-mail-es5.min.js.gz',
            '/media/com_users/js/two-factor-focus-es5.js',
            '/media/com_users/js/two-factor-focus-es5.min.js',
            '/media/com_users/js/two-factor-focus-es5.min.js.gz',
            '/media/com_users/js/two-factor-list-es5.js',
            '/media/com_users/js/two-factor-list-es5.min.js',
            '/media/com_users/js/two-factor-list-es5.min.js.gz',
            '/media/com_workflow/js/admin-items-workflow-buttons-es5.js',
            '/media/com_workflow/js/admin-items-workflow-buttons-es5.min.js',
            '/media/com_workflow/js/admin-items-workflow-buttons-es5.min.js.gz',
            '/media/com_wrapper/js/iframe-height-es5.js',
            '/media/com_wrapper/js/iframe-height-es5.min.js',
            '/media/com_wrapper/js/iframe-height-es5.min.js.gz',
            '/media/layouts/js/joomla/form/field/category-change-es5.js',
            '/media/layouts/js/joomla/form/field/category-change-es5.min.js',
            '/media/layouts/js/joomla/form/field/category-change-es5.min.js.gz',
            '/media/layouts/js/joomla/html/batch/batch-copymove-es5.js',
            '/media/layouts/js/joomla/html/batch/batch-copymove-es5.min.js',
            '/media/layouts/js/joomla/html/batch/batch-copymove-es5.min.js.gz',
            '/media/mod_login/js/admin-login-es5.js',
            '/media/mod_login/js/admin-login-es5.min.js',
            '/media/mod_login/js/admin-login-es5.min.js.gz',
            '/media/mod_menu/js/admin-menu-es5.js',
            '/media/mod_menu/js/admin-menu-es5.min.js',
            '/media/mod_menu/js/admin-menu-es5.min.js.gz',
            '/media/mod_menu/js/menu-es5.js',
            '/media/mod_menu/js/menu-es5.min.js',
            '/media/mod_menu/js/menu-es5.min.js.gz',
            '/media/mod_multilangstatus/js/admin-multilangstatus-es5.js',
            '/media/mod_multilangstatus/js/admin-multilangstatus-es5.min.js',
            '/media/mod_multilangstatus/js/admin-multilangstatus-es5.min.js.gz',
            '/media/mod_quickicon/js/quickicon-es5.js',
            '/media/mod_quickicon/js/quickicon-es5.min.js',
            '/media/mod_quickicon/js/quickicon-es5.min.js.gz',
            '/media/mod_sampledata/js/sampledata-process-es5.js',
            '/media/mod_sampledata/js/sampledata-process-es5.min.js',
            '/media/mod_sampledata/js/sampledata-process-es5.min.js.gz',
            '/media/plg_captcha_recaptcha/js/recaptcha-es5.js',
            '/media/plg_captcha_recaptcha/js/recaptcha-es5.min.js',
            '/media/plg_captcha_recaptcha/js/recaptcha-es5.min.js.gz',
            '/media/plg_captcha_recaptcha_invisible/js/recaptcha-es5.js',
            '/media/plg_captcha_recaptcha_invisible/js/recaptcha-es5.min.js',
            '/media/plg_captcha_recaptcha_invisible/js/recaptcha-es5.min.js.gz',
            '/media/plg_editors_codemirror/js/joomla-editor-codemirror-es5.js',
            '/media/plg_editors_codemirror/js/joomla-editor-codemirror-es5.min.js',
            '/media/plg_editors_codemirror/js/joomla-editor-codemirror-es5.min.js.gz',
            '/media/plg_editors_none/js/joomla-editor-none-es5.js',
            '/media/plg_editors_none/js/joomla-editor-none-es5.min.js',
            '/media/plg_editors_none/js/joomla-editor-none-es5.min.js.gz',
            '/media/plg_editors_tinymce/js/plugins/highlighter/plugin-es5.js',
            '/media/plg_editors_tinymce/js/plugins/highlighter/plugin-es5.min.js',
            '/media/plg_editors_tinymce/js/plugins/highlighter/plugin-es5.min.js.gz',
            '/media/plg_editors_tinymce/js/plugins/highlighter/source-es5.js',
            '/media/plg_editors_tinymce/js/plugins/highlighter/source-es5.min.js',
            '/media/plg_editors_tinymce/js/plugins/highlighter/source-es5.min.js.gz',
            '/media/plg_editors_tinymce/js/tinymce-builder-es5.js',
            '/media/plg_editors_tinymce/js/tinymce-builder-es5.min.js',
            '/media/plg_editors_tinymce/js/tinymce-builder-es5.min.js.gz',
            '/media/plg_editors_tinymce/js/tinymce-es5.js',
            '/media/plg_editors_tinymce/js/tinymce-es5.min.js',
            '/media/plg_editors_tinymce/js/tinymce-es5.min.js.gz',
            '/media/plg_installer_folderinstaller/js/folderinstaller-es5.js',
            '/media/plg_installer_folderinstaller/js/folderinstaller-es5.min.js',
            '/media/plg_installer_folderinstaller/js/folderinstaller-es5.min.js.gz',
            '/media/plg_installer_packageinstaller/js/packageinstaller-es5.js',
            '/media/plg_installer_packageinstaller/js/packageinstaller-es5.min.js',
            '/media/plg_installer_packageinstaller/js/packageinstaller-es5.min.js.gz',
            '/media/plg_installer_urlinstaller/js/urlinstaller-es5.js',
            '/media/plg_installer_urlinstaller/js/urlinstaller-es5.min.js',
            '/media/plg_installer_urlinstaller/js/urlinstaller-es5.min.js.gz',
            '/media/plg_installer_webinstaller/js/client-es5.js',
            '/media/plg_installer_webinstaller/js/client-es5.min.js',
            '/media/plg_installer_webinstaller/js/client-es5.min.js.gz',
            '/media/plg_media-action_crop/js/crop-es5.js',
            '/media/plg_media-action_crop/js/crop-es5.min.js',
            '/media/plg_media-action_crop/js/crop-es5.min.js.gz',
            '/media/plg_media-action_resize/js/resize-es5.js',
            '/media/plg_media-action_resize/js/resize-es5.min.js',
            '/media/plg_media-action_resize/js/resize-es5.min.js.gz',
            '/media/plg_media-action_rotate/js/rotate-es5.js',
            '/media/plg_media-action_rotate/js/rotate-es5.min.js',
            '/media/plg_media-action_rotate/js/rotate-es5.min.js.gz',
            '/media/plg_multifactorauth_totp/js/setup-es5.js',
            '/media/plg_multifactorauth_totp/js/setup-es5.min.js',
            '/media/plg_multifactorauth_totp/js/setup-es5.min.js.gz',
            '/media/plg_multifactorauth_webauthn/js/webauthn-es5.js',
            '/media/plg_multifactorauth_webauthn/js/webauthn-es5.min.js',
            '/media/plg_multifactorauth_webauthn/js/webauthn-es5.min.js.gz',
            '/media/plg_quickicon_eos/js/snooze-es5.js',
            '/media/plg_quickicon_eos/js/snooze-es5.min.js',
            '/media/plg_quickicon_eos/js/snooze-es5.min.js.gz',
            '/media/plg_quickicon_extensionupdate/js/extensionupdatecheck-es5.js',
            '/media/plg_quickicon_extensionupdate/js/extensionupdatecheck-es5.min.js',
            '/media/plg_quickicon_extensionupdate/js/extensionupdatecheck-es5.min.js.gz',
            '/media/plg_quickicon_joomlaupdate/js/jupdatecheck-es5.js',
            '/media/plg_quickicon_joomlaupdate/js/jupdatecheck-es5.min.js',
            '/media/plg_quickicon_joomlaupdate/js/jupdatecheck-es5.min.js.gz',
            '/media/plg_quickicon_overridecheck/js/overridecheck-es5.js',
            '/media/plg_quickicon_overridecheck/js/overridecheck-es5.min.js',
            '/media/plg_quickicon_overridecheck/js/overridecheck-es5.min.js.gz',
            '/media/plg_quickicon_privacycheck/js/privacycheck-es5.js',
            '/media/plg_quickicon_privacycheck/js/privacycheck-es5.min.js',
            '/media/plg_quickicon_privacycheck/js/privacycheck-es5.min.js.gz',
            '/media/plg_system_debug/js/debug-es5.js',
            '/media/plg_system_debug/js/debug-es5.min.js',
            '/media/plg_system_debug/js/debug-es5.min.js.gz',
            '/media/plg_system_guidedtours/js/guidedtours-es5.js',
            '/media/plg_system_guidedtours/js/guidedtours-es5.min.js',
            '/media/plg_system_guidedtours/js/guidedtours-es5.min.js.gz',
            '/media/plg_system_jooa11y/js/jooa11y-es5.js',
            '/media/plg_system_jooa11y/js/jooa11y-es5.min.js',
            '/media/plg_system_jooa11y/js/jooa11y-es5.min.js.gz',
            '/media/plg_system_schedulerunner/js/run-schedule-es5.js',
            '/media/plg_system_schedulerunner/js/run-schedule-es5.min.js',
            '/media/plg_system_schedulerunner/js/run-schedule-es5.min.js.gz',
            '/media/plg_system_shortcut/js/shortcut-es5.js',
            '/media/plg_system_shortcut/js/shortcut-es5.min.js',
            '/media/plg_system_shortcut/js/shortcut-es5.min.js.gz',
            '/media/plg_system_stats/js/stats-es5.js',
            '/media/plg_system_stats/js/stats-es5.min.js',
            '/media/plg_system_stats/js/stats-es5.min.js.gz',
            '/media/plg_system_stats/js/stats-message-es5.js',
            '/media/plg_system_stats/js/stats-message-es5.min.js',
            '/media/plg_system_stats/js/stats-message-es5.min.js.gz',
            '/media/plg_system_webauthn/js/login-es5.js',
            '/media/plg_system_webauthn/js/login-es5.min.js',
            '/media/plg_system_webauthn/js/login-es5.min.js.gz',
            '/media/plg_system_webauthn/js/management-es5.js',
            '/media/plg_system_webauthn/js/management-es5.min.js',
            '/media/plg_system_webauthn/js/management-es5.min.js.gz',
            '/media/plg_user_token/js/token-es5.js',
            '/media/plg_user_token/js/token-es5.min.js',
            '/media/plg_user_token/js/token-es5.min.js.gz',
            '/media/system/js/core-es5.js',
            '/media/system/js/core-es5.min.js',
            '/media/system/js/core-es5.min.js.gz',
            '/media/system/js/draggable-es5.js',
            '/media/system/js/draggable-es5.min.js',
            '/media/system/js/draggable-es5.min.js.gz',
            '/media/system/js/fields/joomla-field-color-slider-es5.js',
            '/media/system/js/fields/joomla-field-color-slider-es5.min.js',
            '/media/system/js/fields/joomla-field-color-slider-es5.min.js.gz',
            '/media/system/js/fields/joomla-field-fancy-select-es5.js',
            '/media/system/js/fields/joomla-field-fancy-select-es5.min.js',
            '/media/system/js/fields/joomla-field-fancy-select-es5.min.js.gz',
            '/media/system/js/fields/joomla-field-media-es5.js',
            '/media/system/js/fields/joomla-field-media-es5.min.js',
            '/media/system/js/fields/joomla-field-media-es5.min.js.gz',
            '/media/system/js/fields/joomla-field-module-order-es5.js',
            '/media/system/js/fields/joomla-field-module-order-es5.min.js',
            '/media/system/js/fields/joomla-field-module-order-es5.min.js.gz',
            '/media/system/js/fields/joomla-field-permissions-es5.js',
            '/media/system/js/fields/joomla-field-permissions-es5.min.js',
            '/media/system/js/fields/joomla-field-permissions-es5.min.js.gz',
            '/media/system/js/fields/joomla-field-send-test-mail-es5.js',
            '/media/system/js/fields/joomla-field-send-test-mail-es5.min.js',
            '/media/system/js/fields/joomla-field-send-test-mail-es5.min.js.gz',
            '/media/system/js/fields/joomla-field-simple-color-es5.js',
            '/media/system/js/fields/joomla-field-simple-color-es5.min.js',
            '/media/system/js/fields/joomla-field-simple-color-es5.min.js.gz',
            '/media/system/js/fields/joomla-field-subform-es5.js',
            '/media/system/js/fields/joomla-field-subform-es5.min.js',
            '/media/system/js/fields/joomla-field-subform-es5.min.js.gz',
            '/media/system/js/fields/joomla-field-user-es5.js',
            '/media/system/js/fields/joomla-field-user-es5.min.js',
            '/media/system/js/fields/joomla-field-user-es5.min.js.gz',
            '/media/system/js/fields/joomla-media-select-es5.js',
            '/media/system/js/fields/joomla-media-select-es5.min.js',
            '/media/system/js/fields/joomla-media-select-es5.min.js.gz',
            '/media/system/js/fields/passwordstrength-es5.js',
            '/media/system/js/fields/passwordstrength-es5.min.js',
            '/media/system/js/fields/passwordstrength-es5.min.js.gz',
            '/media/system/js/fields/passwordview-es5.js',
            '/media/system/js/fields/passwordview-es5.min.js',
            '/media/system/js/fields/passwordview-es5.min.js.gz',
            '/media/system/js/fields/select-colour-es5.js',
            '/media/system/js/fields/select-colour-es5.min.js',
            '/media/system/js/fields/select-colour-es5.min.js.gz',
            '/media/system/js/fields/validate-es5.js',
            '/media/system/js/fields/validate-es5.min.js',
            '/media/system/js/fields/validate-es5.min.js.gz',
            '/media/system/js/highlight-es5.js',
            '/media/system/js/highlight-es5.min.js',
            '/media/system/js/highlight-es5.min.js.gz',
            '/media/system/js/inlinehelp-es5.js',
            '/media/system/js/inlinehelp-es5.min.js',
            '/media/system/js/inlinehelp-es5.min.js.gz',
            '/media/system/js/joomla-core-loader-es5.js',
            '/media/system/js/joomla-core-loader-es5.min.js',
            '/media/system/js/joomla-core-loader-es5.min.js.gz',
            '/media/system/js/joomla-hidden-mail-es5.js',
            '/media/system/js/joomla-hidden-mail-es5.min.js',
            '/media/system/js/joomla-hidden-mail-es5.min.js.gz',
            '/media/system/js/joomla-toolbar-button-es5.js',
            '/media/system/js/joomla-toolbar-button-es5.min.js',
            '/media/system/js/joomla-toolbar-button-es5.min.js.gz',
            '/media/system/js/keepalive-es5.js',
            '/media/system/js/keepalive-es5.min.js',
            '/media/system/js/keepalive-es5.min.js.gz',
            '/media/system/js/list-view-es5.js',
            '/media/system/js/list-view-es5.min.js',
            '/media/system/js/list-view-es5.min.js.gz',
            '/media/system/js/messages-es5.js',
            '/media/system/js/messages-es5.min.js',
            '/media/system/js/messages-es5.min.js.gz',
            '/media/system/js/multiselect-es5.js',
            '/media/system/js/multiselect-es5.min.js',
            '/media/system/js/multiselect-es5.min.js.gz',
            '/media/system/js/searchtools-es5.js',
            '/media/system/js/searchtools-es5.min.js',
            '/media/system/js/searchtools-es5.min.js.gz',
            '/media/system/js/showon-es5.js',
            '/media/system/js/showon-es5.min.js',
            '/media/system/js/showon-es5.min.js.gz',
            '/media/system/js/table-columns-es5.js',
            '/media/system/js/table-columns-es5.min.js',
            '/media/system/js/table-columns-es5.min.js.gz',
            '/media/templates/administrator/atum/js/template-es5.js',
            '/media/templates/administrator/atum/js/template-es5.min.js',
            '/media/templates/administrator/atum/js/template-es5.min.js.gz',
            '/media/templates/site/cassiopeia/js/mod_menu/menu-metismenu-es5.js',
            '/media/templates/site/cassiopeia/js/mod_menu/menu-metismenu-es5.min.js',
            '/media/templates/site/cassiopeia/js/mod_menu/menu-metismenu-es5.min.js.gz',
            '/media/vendor/bootstrap/js/bootstrap-es5.js',
            '/media/vendor/bootstrap/js/bootstrap-es5.min.js',
            '/media/vendor/bootstrap/js/bootstrap-es5.min.js.gz',
            '/media/vendor/joomla-custom-elements/js/joomla-alert-es5.js',
            '/media/vendor/joomla-custom-elements/js/joomla-alert-es5.min.js',
            '/media/vendor/joomla-custom-elements/js/joomla-alert-es5.min.js.gz',
            '/media/vendor/joomla-custom-elements/js/joomla-tab-es5.js',
            '/media/vendor/joomla-custom-elements/js/joomla-tab-es5.min.js',
            '/media/vendor/joomla-custom-elements/js/joomla-tab-es5.min.js.gz',
            '/media/vendor/mediaelement/js/mediaelement-flash-audio-ogg.swf',
            '/media/vendor/mediaelement/js/mediaelement-flash-audio.swf',
            '/media/vendor/mediaelement/js/mediaelement-flash-video-hls.swf',
            '/media/vendor/mediaelement/js/mediaelement-flash-video-mdash.swf',
            '/media/vendor/mediaelement/js/mediaelement-flash-video.swf',
            '/plugins/editors-xtd/pagebreak/pagebreak.php',
            '/plugins/task/demotasks/demotasks.xml',
            '/plugins/task/demotasks/forms/testTaskForm.xml',
            '/plugins/task/demotasks/services/provider.php',
            '/plugins/task/demotasks/src/Extension/DemoTasks.php',
        ];

        $folders = [
            // From 4.4 to 5.0
            '/plugins/system/webauthn/src/Hotfix',
            '/plugins/multifactorauth/webauthn/src/Hotfix',
            '/media/vendor/tinymce/themes/mobile',
            '/media/vendor/tinymce/skins/ui/oxide/fonts',
            '/media/vendor/tinymce/skins/ui/oxide-dark/fonts',
            '/media/vendor/tinymce/plugins/toc',
            '/media/vendor/tinymce/plugins/textpattern',
            '/media/vendor/tinymce/plugins/textcolor',
            '/media/vendor/tinymce/plugins/template',
            '/media/vendor/tinymce/plugins/tabfocus',
            '/media/vendor/tinymce/plugins/spellchecker',
            '/media/vendor/tinymce/plugins/print',
            '/media/vendor/tinymce/plugins/paste',
            '/media/vendor/tinymce/plugins/noneditable',
            '/media/vendor/tinymce/plugins/legacyoutput',
            '/media/vendor/tinymce/plugins/imagetools',
            '/media/vendor/tinymce/plugins/hr',
            '/media/vendor/tinymce/plugins/fullpage',
            '/media/vendor/tinymce/plugins/contextmenu',
            '/media/vendor/tinymce/plugins/colorpicker',
            '/media/vendor/tinymce/plugins/bbcode',
            '/libraries/vendor/symfony/polyfill-php81/Resources/stubs',
            '/libraries/vendor/symfony/polyfill-php81/Resources',
            '/libraries/vendor/symfony/polyfill-php81',
            '/libraries/vendor/symfony/polyfill-php80/Resources/stubs',
            '/libraries/vendor/symfony/polyfill-php80/Resources',
            '/libraries/vendor/symfony/polyfill-php80',
            '/libraries/vendor/symfony/polyfill-php73/Resources/stubs',
            '/libraries/vendor/symfony/polyfill-php73/Resources',
            '/libraries/vendor/symfony/polyfill-php73',
            '/libraries/vendor/symfony/polyfill-php72',
            '/libraries/vendor/spomky-labs/base64url/src',
            '/libraries/vendor/spomky-labs/base64url',
            '/libraries/vendor/ramsey/uuid/src/Provider/Time',
            '/libraries/vendor/ramsey/uuid/src/Provider/Node',
            '/libraries/vendor/ramsey/uuid/src/Provider',
            '/libraries/vendor/ramsey/uuid/src/Generator',
            '/libraries/vendor/ramsey/uuid/src/Exception',
            '/libraries/vendor/ramsey/uuid/src/Converter/Time',
            '/libraries/vendor/ramsey/uuid/src/Converter/Number',
            '/libraries/vendor/ramsey/uuid/src/Converter',
            '/libraries/vendor/ramsey/uuid/src/Codec',
            '/libraries/vendor/ramsey/uuid/src/Builder',
            '/libraries/vendor/ramsey/uuid/src',
            '/libraries/vendor/ramsey/uuid',
            '/libraries/vendor/ramsey',
            '/libraries/vendor/psr/log/Psr/Log',
            '/libraries/vendor/psr/log/Psr',
            '/libraries/vendor/nyholm/psr7/src/Factory',
            '/libraries/vendor/nyholm/psr7/src',
            '/libraries/vendor/nyholm/psr7',
            '/libraries/vendor/nyholm',
            '/libraries/vendor/lcobucci/jwt/src/Parsing',
            '/libraries/vendor/lcobucci/jwt/src/Claim',
            '/libraries/vendor/lcobucci/jwt/compat',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/src',
            '/libraries/vendor/laminas/laminas-zendframework-bridge/config',
            '/libraries/vendor/laminas/laminas-zendframework-bridge',
            '/libraries/vendor/joomla/ldap/src',
            '/libraries/vendor/joomla/ldap',
            '/libraries/vendor/beberlei/assert/lib/Assert',
            '/libraries/vendor/beberlei/assert/lib',
            '/libraries/vendor/beberlei/assert',
            '/libraries/vendor/beberlei',
            '/administrator/components/com_admin/sql/others/mysql',
            '/administrator/components/com_admin/sql/others',
            // From 5.0.0-alpha1 to 5.0.0-alpha2
            '/plugins/task/demotasks/src/Extension',
            '/plugins/task/demotasks/src',
            '/plugins/task/demotasks/services',
            '/plugins/task/demotasks/forms',
            '/plugins/task/demotasks',
        ];

        $status['files_checked']   = $files;
        $status['folders_checked'] = $folders;

        foreach ($files as $file) {
            if ($fileExists = is_file(JPATH_ROOT . $file)) {
                $status['files_exist'][] = $file;

                if ($dryRun === false) {
                    if (File::delete(JPATH_ROOT . $file)) {
                        $status['files_deleted'][] = $file;
                    } else {
                        $status['files_errors'][] = Text::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file);
                    }
                }
            }
        }

        foreach ($folders as $folder) {
            if ($folderExists = Folder::exists(JPATH_ROOT . $folder)) {
                $status['folders_exist'][] = $folder;

                if ($dryRun === false) {
                    if (Folder::delete(JPATH_ROOT . $folder)) {
                        $status['folders_deleted'][] = $folder;
                    } else {
                        $status['folders_errors'][] = Text::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder);
                    }
                }
            }
        }

        $this->fixFilenameCasing();

        if ($suppressOutput === false && count($status['folders_errors'])) {
            echo implode('<br>', $status['folders_errors']);
        }

        if ($suppressOutput === false && count($status['files_errors'])) {
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
        // List all components added since 4.0
        $newComponents = [
            // Components to be added here
        ];

        foreach ($newComponents as $component) {
            /** @var \Joomla\CMS\Table\Asset $asset */
            $asset = Table::getInstance('Asset');

            if ($asset->loadByName($component)) {
                continue;
            }

            $asset->name      = $component;
            $asset->parent_id = 1;
            $asset->rules     = '{}';
            $asset->title     = $component;
            $asset->setLocation(1, 'last-child');

            if (!$asset->store()) {
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

        if (empty($this->fromVersion) || version_compare($this->fromVersion, '5.0.0', 'ge')) {
            return true;
        }

        // Add here code which shall be executed only when updating from an older version than 5.0.0
        if (!$this->migrateTinymceConfiguration()) {
            return false;
        }

        return true;
    }

    /**
     * Migrate TinyMCE editor plugin configuration
     *
     * @return  boolean  True on success
     *
     * @since   5.0.0
     */
    private function migrateTinymceConfiguration(): bool
    {
        $db = Factory::getDbo();

        try {
            // Get the TinyMCE editor plugin's parameters
            $params = $db->setQuery(
                $db->getQuery(true)
                    ->select($db->quoteName('params'))
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote('editors'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote('tinymce'))
            )->loadResult();
        } catch (Exception $e) {
            echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

            return false;
        }

        $params = json_decode($params, true);

        // If there are no toolbars there is nothing to migrate
        if (!isset($params['configuration']['toolbars'])) {
            return true;
        }

        // Each set has its own toolbar configuration
        foreach ($params['configuration']['toolbars'] as $setIdx => $toolbarConfig) {
            // Migrate menu items if there is a menu
            if (isset($toolbarConfig['menu'])) {
                /**
                 * Replace array values with menu item names ("old name" -> "new name"):
                 * "blockformats" -> "blocks"
                 * "fontformats"  -> "fontfamily"
                 * "fontsizes"    -> "fontsize"
                 * "formats"      -> "styles"
                 * "template"     -> "jtemplate"
                 */
                $params['configuration']['toolbars'][$setIdx]['menu'] = str_replace(
                    ['blockformats', 'fontformats', 'fontsizes', 'formats', 'template'],
                    ['blocks', 'fontfamily', 'fontsize', 'styles', 'jtemplate'],
                    $toolbarConfig['menu']
                );
            }

            // There could be no toolbar at all, or only toolbar1, or both toolbar1 and toolbar2
            foreach (['toolbar1', 'toolbar2'] as $toolbarIdx) {
                // Migrate toolbar buttons if that toolbar exists
                if (isset($toolbarConfig[$toolbarIdx])) {
                    /**
                     * Replace array values with button names ("old name" -> "new name"):
                     * "fontselect"     -> "fontfamily"
                     * "fontsizeselect" -> "fontsize"
                     * "formatselect"   -> "blocks"
                     * "styleselect"    -> "styles"
                     * "template"       -> "jtemplate"
                     */
                    $params['configuration']['toolbars'][$setIdx][$toolbarIdx] = str_replace(
                        ['fontselect', 'fontsizeselect', 'formatselect', 'styleselect', 'template'],
                        ['fontfamily', 'fontsize', 'blocks', 'styles', 'jtemplate'],
                        $toolbarConfig[$toolbarIdx]
                    );
                }
            }
        }

        $params = json_encode($params);

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = ' . $db->quote($params))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('editors'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('tinymce'));

        try {
            $db->setQuery($query)->execute();
        } catch (Exception $e) {
            echo Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

            return false;
        }

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
            // From 4.4 to 5.0
            '/libraries/vendor/web-auth/cose-lib/src/Algorithm/Signature/EdDSA/ED256.php' => '/libraries/vendor/web-auth/cose-lib/src/Algorithm/Signature/EdDSA/Ed256.php',
            '/libraries/vendor/web-auth/cose-lib/src/Algorithm/Signature/EdDSA/ED512.php' => '/libraries/vendor/web-auth/cose-lib/src/Algorithm/Signature/EdDSA/Ed512.php',
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
                    if (!in_array($expectedBasename, scandir(dirname($newRealpath)))) {
                        // Rename the file.
                        File::move(JPATH_ROOT . $old, JPATH_ROOT . $old . '.tmp');
                        File::move(JPATH_ROOT . $old . '.tmp', JPATH_ROOT . $expected);
                    }
                } else {
                    // On Unix with both files: Delete the incorrectly cased file.
                    File::delete(JPATH_ROOT . $old);
                }
            }
        }
    }
}
