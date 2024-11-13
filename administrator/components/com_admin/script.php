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

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;

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
                $db->getQuery(true)
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

        $query = $db->getQuery(true)
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
     * updating from a version older than 5.0.1.
     *
     * @return  void
     *
     * @since   5.0.0
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
            ['type' => 'plugin', 'element' => 'compat', 'folder' => 'system', 'client_id' => 0, 'pre_function' => 'migrateCompatPlugin'],
            ['type' => 'plugin', 'element' => 'logrotation', 'folder' => 'system', 'client_id' => 0, 'pre_function' => 'migrateLogRotationPlugin'],
            ['type' => 'plugin', 'element' => 'recaptcha', 'folder' => 'captcha', 'client_id' => 0, 'pre_function' => null],
            ['type' => 'plugin', 'element' => 'sessiongc', 'folder' => 'system', 'client_id' => 0, 'pre_function' => 'migrateSessionGCPlugin'],
            ['type' => 'plugin', 'element' => 'updatenotification', 'folder' => 'system', 'client_id' => 0, 'pre_function' => 'migrateUpdatenotificationPlugin'],
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
                throw $e;
            }
        }
    }

    /**
     * Migrate plugin parameters of obsolete compat system plugin to compat behaviour plugin
     *
     * @param   \stdClass  $rowOld  Object with the obsolete plugin's record in the `#__extensions` table
     *
     * @return  void
     *
     * @since   5.0.0
     */
    private function migrateCompatPlugin($rowOld)
    {
        $db = Factory::getDbo();

        $db->setQuery(
            $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = :enabled')
                ->set($db->quoteName('params') . ' = :params')
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('compat'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('behaviour'))
                ->where($db->quoteName('client_id') . ' = 0')
                ->bind(':enabled', $rowOld->enabled, ParameterType::INTEGER)
                ->bind(':params', $rowOld->params)
        )->execute();
    }

    /**
     * This method is for migration for old logrotation system plugin migration to task.
     *
     * @param   \stdClass  $data  Object with the extension's record in the `#__extensions` table
     *
     * @return  void
     *
     * @since   5.0.0
     */
    private function migrateLogRotationPlugin($data)
    {
        if (!$data->enabled) {
            return;
        }

        /** @var \Joomla\Component\Scheduler\Administrator\Extension\SchedulerComponent $component */
        $component = Factory::getApplication()->bootComponent('com_scheduler');

        /** @var \Joomla\Component\Scheduler\Administrator\Model\TaskModel $model */
        $model = $component->getMVCFactory()->createModel('Task', 'Administrator', ['ignore_request' => true]);

        // Get the timeout, as configured in plg_system_logrotation
        $params       = new Registry($data->params);
        $cachetimeout = (int) $params->get('cachetimeout', 30);
        $lastrun      = (int) $params->get('lastrun', time());

        $task = [
            'title'           => 'Rotate Logs',
            'type'            => 'rotation.logs',
            'execution_rules' => [
                'rule-type'     => 'interval-days',
                'interval-days' => $cachetimeout,
                'exec-time'     => gmdate('H:i', $lastrun),
                'exec-day'      => gmdate('d'),
            ],
            'state'  => 1,
            'params' => [
                'logstokeep' => $params->get('logstokeep', 1),
            ],
        ];
        $model->save($task);
    }

    /**
     * This method is for migration for old updatenotification system plugin migration to task.
     *
     * @param   \stdClass  $data  Object with the extension's record in the `#__extensions` table
     *
     * @return  void
     *
     * @since   5.0.0
     */
    private function migrateSessionGCPlugin($data)
    {
        if (!$data->enabled) {
            return;
        }

        // Get the plugin parameters
        $params = new Registry($data->params);

        /** @var \Joomla\Component\Scheduler\Administrator\Extension\SchedulerComponent $component */
        $component = Factory::getApplication()->bootComponent('com_scheduler');

        /** @var \Joomla\Component\Scheduler\Administrator\Model\TaskModel $model */
        $model = $component->getMVCFactory()->createModel('Task', 'Administrator', ['ignore_request' => true]);
        $task  = [
            'title'           => 'Session GC',
            'type'            => 'session.gc',
            'execution_rules' => [
                'rule-type'      => 'interval-hours',
                'interval-hours' => 24,
                'exec-time'      => gmdate('H:i'),
                'exec-day'       => gmdate('d'),
            ],
            'state'  => 1,
            'params' => [
                'enable_session_gc'          => $params->get('enable_session_gc', 1),
                'enable_session_metadata_gc' => $params->get('enable_session_metadata_gc', 1),
            ],
        ];
        $model->save($task);
    }

    /**
     * This method is for migration for old updatenotification system plugin migration to task.
     *
     * @param   \stdClass  $data  Object with the extension's record in the `#__extensions` table
     *
     * @return  void
     *
     * @since   5.0.0
     */
    private function migrateUpdatenotificationPlugin($data)
    {
        if (!$data->enabled) {
            return;
        }

        // Get the timeout for Joomla! updates, as configured in com_installer's component parameters
        $component    = ComponentHelper::getComponent('com_installer');
        $paramsc      = $component->getParams();
        $cachetimeout = (int) $paramsc->get('cachetimeout', 6);
        $params       = new Registry($data->params);
        $lastrun      = (int) $params->get('lastrun', time());

        /** @var \Joomla\Component\Scheduler\Administrator\Extension\SchedulerComponent $component */
        $component = Factory::getApplication()->bootComponent('com_scheduler');

        /** @var \Joomla\Component\Scheduler\Administrator\Model\TaskModel $model */
        $model = $component->getMVCFactory()->createModel('Task', 'Administrator', ['ignore_request' => true]);
        $task  = [
            'title'           => 'Update Notification',
            'type'            => 'update.notification',
            'execution_rules' => [
                'rule-type'      => 'interval-hours',
                'interval-hours' => $cachetimeout,
                'exec-time'      => gmdate('H:i', $lastrun),
                'exec-day'       => gmdate('d'),
            ],
            'state'  => 1,
            'params' => [
                'email'             => $params->get('email', ''),
                'language_override' => $params->get('language_override', ''),
            ],
        ];
        $model->save($task);
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
            '/administrator/components/com_admin/sql/updates/mysql/4.4.0-2023-09-13.sql',
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
            '/administrator/components/com_admin/sql/updates/postgresql/4.4.0-2023-09-13.sql',
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
            '/libraries/vendor/symfony/polyfill-php81/Resources/stubs/CURLStringFile.php',
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
            '/media/com_templates/js/admin-template-compare-es5.js',
            '/media/com_templates/js/admin-template-compare-es5.min.js',
            '/media/com_templates/js/admin-template-compare-es5.min.js.gz',
            '/media/com_templates/js/admin-template-compare.js',
            '/media/com_templates/js/admin-template-compare.min.js',
            '/media/com_templates/js/admin-template-compare.min.js.gz',
            '/media/com_users/js/admin-users-mail-es5.js',
            '/media/com_users/js/admin-users-mail-es5.min.js',
            '/media/com_users/js/admin-users-mail-es5.min.js.gz',
            '/media/com_users/js/admin-users-mail.js',
            '/media/com_users/js/admin-users-mail.min.js',
            '/media/com_users/js/admin-users-mail.min.js.gz',
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
            // From 5.0.0-alpha2 to 5.0.0-alpha3
            '/libraries/classmap.php',
            '/libraries/extensions.classmap.php',
            '/media/vendor/codemirror/addon/comment/comment.js',
            '/media/vendor/codemirror/addon/comment/comment.min.js',
            '/media/vendor/codemirror/addon/comment/comment.min.js.gz',
            '/media/vendor/codemirror/addon/comment/continuecomment.js',
            '/media/vendor/codemirror/addon/comment/continuecomment.min.js',
            '/media/vendor/codemirror/addon/comment/continuecomment.min.js.gz',
            '/media/vendor/codemirror/addon/dialog/dialog.css',
            '/media/vendor/codemirror/addon/dialog/dialog.js',
            '/media/vendor/codemirror/addon/dialog/dialog.min.js',
            '/media/vendor/codemirror/addon/dialog/dialog.min.js.gz',
            '/media/vendor/codemirror/addon/display/autorefresh.js',
            '/media/vendor/codemirror/addon/display/autorefresh.min.js',
            '/media/vendor/codemirror/addon/display/autorefresh.min.js.gz',
            '/media/vendor/codemirror/addon/display/fullscreen.css',
            '/media/vendor/codemirror/addon/display/fullscreen.js',
            '/media/vendor/codemirror/addon/display/fullscreen.min.js',
            '/media/vendor/codemirror/addon/display/fullscreen.min.js.gz',
            '/media/vendor/codemirror/addon/display/panel.js',
            '/media/vendor/codemirror/addon/display/panel.min.js',
            '/media/vendor/codemirror/addon/display/panel.min.js.gz',
            '/media/vendor/codemirror/addon/display/placeholder.js',
            '/media/vendor/codemirror/addon/display/placeholder.min.js',
            '/media/vendor/codemirror/addon/display/placeholder.min.js.gz',
            '/media/vendor/codemirror/addon/display/rulers.js',
            '/media/vendor/codemirror/addon/display/rulers.min.js',
            '/media/vendor/codemirror/addon/display/rulers.min.js.gz',
            '/media/vendor/codemirror/addon/edit/closebrackets.js',
            '/media/vendor/codemirror/addon/edit/closebrackets.min.js',
            '/media/vendor/codemirror/addon/edit/closebrackets.min.js.gz',
            '/media/vendor/codemirror/addon/edit/closetag.js',
            '/media/vendor/codemirror/addon/edit/closetag.min.js',
            '/media/vendor/codemirror/addon/edit/closetag.min.js.gz',
            '/media/vendor/codemirror/addon/edit/continuelist.js',
            '/media/vendor/codemirror/addon/edit/continuelist.min.js',
            '/media/vendor/codemirror/addon/edit/continuelist.min.js.gz',
            '/media/vendor/codemirror/addon/edit/matchbrackets.js',
            '/media/vendor/codemirror/addon/edit/matchbrackets.min.js',
            '/media/vendor/codemirror/addon/edit/matchbrackets.min.js.gz',
            '/media/vendor/codemirror/addon/edit/matchtags.js',
            '/media/vendor/codemirror/addon/edit/matchtags.min.js',
            '/media/vendor/codemirror/addon/edit/matchtags.min.js.gz',
            '/media/vendor/codemirror/addon/edit/trailingspace.js',
            '/media/vendor/codemirror/addon/edit/trailingspace.min.js',
            '/media/vendor/codemirror/addon/edit/trailingspace.min.js.gz',
            '/media/vendor/codemirror/addon/fold/brace-fold.js',
            '/media/vendor/codemirror/addon/fold/brace-fold.min.js',
            '/media/vendor/codemirror/addon/fold/brace-fold.min.js.gz',
            '/media/vendor/codemirror/addon/fold/comment-fold.js',
            '/media/vendor/codemirror/addon/fold/comment-fold.min.js',
            '/media/vendor/codemirror/addon/fold/comment-fold.min.js.gz',
            '/media/vendor/codemirror/addon/fold/foldcode.js',
            '/media/vendor/codemirror/addon/fold/foldcode.min.js',
            '/media/vendor/codemirror/addon/fold/foldcode.min.js.gz',
            '/media/vendor/codemirror/addon/fold/foldgutter.css',
            '/media/vendor/codemirror/addon/fold/foldgutter.js',
            '/media/vendor/codemirror/addon/fold/foldgutter.min.js',
            '/media/vendor/codemirror/addon/fold/foldgutter.min.js.gz',
            '/media/vendor/codemirror/addon/fold/indent-fold.js',
            '/media/vendor/codemirror/addon/fold/indent-fold.min.js',
            '/media/vendor/codemirror/addon/fold/indent-fold.min.js.gz',
            '/media/vendor/codemirror/addon/fold/markdown-fold.js',
            '/media/vendor/codemirror/addon/fold/markdown-fold.min.js',
            '/media/vendor/codemirror/addon/fold/markdown-fold.min.js.gz',
            '/media/vendor/codemirror/addon/fold/xml-fold.js',
            '/media/vendor/codemirror/addon/fold/xml-fold.min.js',
            '/media/vendor/codemirror/addon/fold/xml-fold.min.js.gz',
            '/media/vendor/codemirror/addon/hint/anyword-hint.js',
            '/media/vendor/codemirror/addon/hint/anyword-hint.min.js',
            '/media/vendor/codemirror/addon/hint/anyword-hint.min.js.gz',
            '/media/vendor/codemirror/addon/hint/css-hint.js',
            '/media/vendor/codemirror/addon/hint/css-hint.min.js',
            '/media/vendor/codemirror/addon/hint/css-hint.min.js.gz',
            '/media/vendor/codemirror/addon/hint/html-hint.js',
            '/media/vendor/codemirror/addon/hint/html-hint.min.js',
            '/media/vendor/codemirror/addon/hint/html-hint.min.js.gz',
            '/media/vendor/codemirror/addon/hint/javascript-hint.js',
            '/media/vendor/codemirror/addon/hint/javascript-hint.min.js',
            '/media/vendor/codemirror/addon/hint/javascript-hint.min.js.gz',
            '/media/vendor/codemirror/addon/hint/show-hint.css',
            '/media/vendor/codemirror/addon/hint/show-hint.js',
            '/media/vendor/codemirror/addon/hint/show-hint.min.js',
            '/media/vendor/codemirror/addon/hint/show-hint.min.js.gz',
            '/media/vendor/codemirror/addon/hint/sql-hint.js',
            '/media/vendor/codemirror/addon/hint/sql-hint.min.js',
            '/media/vendor/codemirror/addon/hint/sql-hint.min.js.gz',
            '/media/vendor/codemirror/addon/hint/xml-hint.js',
            '/media/vendor/codemirror/addon/hint/xml-hint.min.js',
            '/media/vendor/codemirror/addon/hint/xml-hint.min.js.gz',
            '/media/vendor/codemirror/addon/lint/coffeescript-lint.js',
            '/media/vendor/codemirror/addon/lint/coffeescript-lint.min.js',
            '/media/vendor/codemirror/addon/lint/coffeescript-lint.min.js.gz',
            '/media/vendor/codemirror/addon/lint/css-lint.js',
            '/media/vendor/codemirror/addon/lint/css-lint.min.js',
            '/media/vendor/codemirror/addon/lint/css-lint.min.js.gz',
            '/media/vendor/codemirror/addon/lint/html-lint.js',
            '/media/vendor/codemirror/addon/lint/html-lint.min.js',
            '/media/vendor/codemirror/addon/lint/html-lint.min.js.gz',
            '/media/vendor/codemirror/addon/lint/javascript-lint.js',
            '/media/vendor/codemirror/addon/lint/javascript-lint.min.js',
            '/media/vendor/codemirror/addon/lint/javascript-lint.min.js.gz',
            '/media/vendor/codemirror/addon/lint/json-lint.js',
            '/media/vendor/codemirror/addon/lint/json-lint.min.js',
            '/media/vendor/codemirror/addon/lint/json-lint.min.js.gz',
            '/media/vendor/codemirror/addon/lint/lint.css',
            '/media/vendor/codemirror/addon/lint/lint.js',
            '/media/vendor/codemirror/addon/lint/lint.min.js',
            '/media/vendor/codemirror/addon/lint/lint.min.js.gz',
            '/media/vendor/codemirror/addon/lint/yaml-lint.js',
            '/media/vendor/codemirror/addon/lint/yaml-lint.min.js',
            '/media/vendor/codemirror/addon/lint/yaml-lint.min.js.gz',
            '/media/vendor/codemirror/addon/merge/merge.css',
            '/media/vendor/codemirror/addon/merge/merge.js',
            '/media/vendor/codemirror/addon/merge/merge.min.js',
            '/media/vendor/codemirror/addon/merge/merge.min.js.gz',
            '/media/vendor/codemirror/addon/mode/loadmode.js',
            '/media/vendor/codemirror/addon/mode/loadmode.min.js',
            '/media/vendor/codemirror/addon/mode/loadmode.min.js.gz',
            '/media/vendor/codemirror/addon/mode/multiplex.js',
            '/media/vendor/codemirror/addon/mode/multiplex.min.js',
            '/media/vendor/codemirror/addon/mode/multiplex.min.js.gz',
            '/media/vendor/codemirror/addon/mode/multiplex_test.js',
            '/media/vendor/codemirror/addon/mode/multiplex_test.min.js',
            '/media/vendor/codemirror/addon/mode/multiplex_test.min.js.gz',
            '/media/vendor/codemirror/addon/mode/overlay.js',
            '/media/vendor/codemirror/addon/mode/overlay.min.js',
            '/media/vendor/codemirror/addon/mode/overlay.min.js.gz',
            '/media/vendor/codemirror/addon/mode/simple.js',
            '/media/vendor/codemirror/addon/mode/simple.min.js',
            '/media/vendor/codemirror/addon/mode/simple.min.js.gz',
            '/media/vendor/codemirror/addon/runmode/colorize.js',
            '/media/vendor/codemirror/addon/runmode/colorize.min.js',
            '/media/vendor/codemirror/addon/runmode/colorize.min.js.gz',
            '/media/vendor/codemirror/addon/runmode/runmode-standalone.js',
            '/media/vendor/codemirror/addon/runmode/runmode-standalone.min.js',
            '/media/vendor/codemirror/addon/runmode/runmode-standalone.min.js.gz',
            '/media/vendor/codemirror/addon/runmode/runmode.js',
            '/media/vendor/codemirror/addon/runmode/runmode.min.js',
            '/media/vendor/codemirror/addon/runmode/runmode.min.js.gz',
            '/media/vendor/codemirror/addon/runmode/runmode.node.js',
            '/media/vendor/codemirror/addon/runmode/runmode.node.min.js',
            '/media/vendor/codemirror/addon/runmode/runmode.node.min.js.gz',
            '/media/vendor/codemirror/addon/scroll/annotatescrollbar.js',
            '/media/vendor/codemirror/addon/scroll/annotatescrollbar.min.js',
            '/media/vendor/codemirror/addon/scroll/annotatescrollbar.min.js.gz',
            '/media/vendor/codemirror/addon/scroll/scrollpastend.js',
            '/media/vendor/codemirror/addon/scroll/scrollpastend.min.js',
            '/media/vendor/codemirror/addon/scroll/scrollpastend.min.js.gz',
            '/media/vendor/codemirror/addon/scroll/simplescrollbars.css',
            '/media/vendor/codemirror/addon/scroll/simplescrollbars.js',
            '/media/vendor/codemirror/addon/scroll/simplescrollbars.min.js',
            '/media/vendor/codemirror/addon/scroll/simplescrollbars.min.js.gz',
            '/media/vendor/codemirror/addon/search/jump-to-line.js',
            '/media/vendor/codemirror/addon/search/jump-to-line.min.js',
            '/media/vendor/codemirror/addon/search/jump-to-line.min.js.gz',
            '/media/vendor/codemirror/addon/search/match-highlighter.js',
            '/media/vendor/codemirror/addon/search/match-highlighter.min.js',
            '/media/vendor/codemirror/addon/search/match-highlighter.min.js.gz',
            '/media/vendor/codemirror/addon/search/matchesonscrollbar.css',
            '/media/vendor/codemirror/addon/search/matchesonscrollbar.js',
            '/media/vendor/codemirror/addon/search/matchesonscrollbar.min.js',
            '/media/vendor/codemirror/addon/search/matchesonscrollbar.min.js.gz',
            '/media/vendor/codemirror/addon/search/search.js',
            '/media/vendor/codemirror/addon/search/search.min.js',
            '/media/vendor/codemirror/addon/search/search.min.js.gz',
            '/media/vendor/codemirror/addon/search/searchcursor.js',
            '/media/vendor/codemirror/addon/search/searchcursor.min.js',
            '/media/vendor/codemirror/addon/search/searchcursor.min.js.gz',
            '/media/vendor/codemirror/addon/selection/active-line.js',
            '/media/vendor/codemirror/addon/selection/active-line.min.js',
            '/media/vendor/codemirror/addon/selection/active-line.min.js.gz',
            '/media/vendor/codemirror/addon/selection/mark-selection.js',
            '/media/vendor/codemirror/addon/selection/mark-selection.min.js',
            '/media/vendor/codemirror/addon/selection/mark-selection.min.js.gz',
            '/media/vendor/codemirror/addon/selection/selection-pointer.js',
            '/media/vendor/codemirror/addon/selection/selection-pointer.min.js',
            '/media/vendor/codemirror/addon/selection/selection-pointer.min.js.gz',
            '/media/vendor/codemirror/addon/tern/tern.css',
            '/media/vendor/codemirror/addon/tern/tern.js',
            '/media/vendor/codemirror/addon/tern/tern.min.js',
            '/media/vendor/codemirror/addon/tern/tern.min.js.gz',
            '/media/vendor/codemirror/addon/tern/worker.js',
            '/media/vendor/codemirror/addon/tern/worker.min.js',
            '/media/vendor/codemirror/addon/tern/worker.min.js.gz',
            '/media/vendor/codemirror/addon/wrap/hardwrap.js',
            '/media/vendor/codemirror/addon/wrap/hardwrap.min.js',
            '/media/vendor/codemirror/addon/wrap/hardwrap.min.js.gz',
            '/media/vendor/codemirror/keymap/emacs.js',
            '/media/vendor/codemirror/keymap/emacs.min.js',
            '/media/vendor/codemirror/keymap/emacs.min.js.gz',
            '/media/vendor/codemirror/keymap/sublime.js',
            '/media/vendor/codemirror/keymap/sublime.min.js',
            '/media/vendor/codemirror/keymap/sublime.min.js.gz',
            '/media/vendor/codemirror/keymap/vim.js',
            '/media/vendor/codemirror/keymap/vim.min.js',
            '/media/vendor/codemirror/keymap/vim.min.js.gz',
            '/media/vendor/codemirror/lib/addons.css',
            '/media/vendor/codemirror/lib/addons.js',
            '/media/vendor/codemirror/lib/addons.min.js',
            '/media/vendor/codemirror/lib/addons.min.js.gz',
            '/media/vendor/codemirror/lib/codemirror.css',
            '/media/vendor/codemirror/lib/codemirror.js',
            '/media/vendor/codemirror/lib/codemirror.min.js',
            '/media/vendor/codemirror/lib/codemirror.min.js.gz',
            '/media/vendor/codemirror/mode/apl/apl.js',
            '/media/vendor/codemirror/mode/apl/apl.min.js',
            '/media/vendor/codemirror/mode/apl/apl.min.js.gz',
            '/media/vendor/codemirror/mode/asciiarmor/asciiarmor.js',
            '/media/vendor/codemirror/mode/asciiarmor/asciiarmor.min.js',
            '/media/vendor/codemirror/mode/asciiarmor/asciiarmor.min.js.gz',
            '/media/vendor/codemirror/mode/asn.1/asn.1.js',
            '/media/vendor/codemirror/mode/asn.1/asn.1.min.js',
            '/media/vendor/codemirror/mode/asn.1/asn.1.min.js.gz',
            '/media/vendor/codemirror/mode/asterisk/asterisk.js',
            '/media/vendor/codemirror/mode/asterisk/asterisk.min.js',
            '/media/vendor/codemirror/mode/asterisk/asterisk.min.js.gz',
            '/media/vendor/codemirror/mode/brainfuck/brainfuck.js',
            '/media/vendor/codemirror/mode/brainfuck/brainfuck.min.js',
            '/media/vendor/codemirror/mode/brainfuck/brainfuck.min.js.gz',
            '/media/vendor/codemirror/mode/clike/clike.js',
            '/media/vendor/codemirror/mode/clike/clike.min.js',
            '/media/vendor/codemirror/mode/clike/clike.min.js.gz',
            '/media/vendor/codemirror/mode/clojure/clojure.js',
            '/media/vendor/codemirror/mode/clojure/clojure.min.js',
            '/media/vendor/codemirror/mode/clojure/clojure.min.js.gz',
            '/media/vendor/codemirror/mode/cmake/cmake.js',
            '/media/vendor/codemirror/mode/cmake/cmake.min.js',
            '/media/vendor/codemirror/mode/cmake/cmake.min.js.gz',
            '/media/vendor/codemirror/mode/cobol/cobol.js',
            '/media/vendor/codemirror/mode/cobol/cobol.min.js',
            '/media/vendor/codemirror/mode/cobol/cobol.min.js.gz',
            '/media/vendor/codemirror/mode/coffeescript/coffeescript.js',
            '/media/vendor/codemirror/mode/coffeescript/coffeescript.min.js',
            '/media/vendor/codemirror/mode/coffeescript/coffeescript.min.js.gz',
            '/media/vendor/codemirror/mode/commonlisp/commonlisp.js',
            '/media/vendor/codemirror/mode/commonlisp/commonlisp.min.js',
            '/media/vendor/codemirror/mode/commonlisp/commonlisp.min.js.gz',
            '/media/vendor/codemirror/mode/crystal/crystal.js',
            '/media/vendor/codemirror/mode/crystal/crystal.min.js',
            '/media/vendor/codemirror/mode/crystal/crystal.min.js.gz',
            '/media/vendor/codemirror/mode/css/css.js',
            '/media/vendor/codemirror/mode/css/css.min.js',
            '/media/vendor/codemirror/mode/css/css.min.js.gz',
            '/media/vendor/codemirror/mode/cypher/cypher.js',
            '/media/vendor/codemirror/mode/cypher/cypher.min.js',
            '/media/vendor/codemirror/mode/cypher/cypher.min.js.gz',
            '/media/vendor/codemirror/mode/d/d.js',
            '/media/vendor/codemirror/mode/d/d.min.js',
            '/media/vendor/codemirror/mode/d/d.min.js.gz',
            '/media/vendor/codemirror/mode/dart/dart.js',
            '/media/vendor/codemirror/mode/dart/dart.min.js',
            '/media/vendor/codemirror/mode/dart/dart.min.js.gz',
            '/media/vendor/codemirror/mode/diff/diff.js',
            '/media/vendor/codemirror/mode/diff/diff.min.js',
            '/media/vendor/codemirror/mode/diff/diff.min.js.gz',
            '/media/vendor/codemirror/mode/django/django.js',
            '/media/vendor/codemirror/mode/django/django.min.js',
            '/media/vendor/codemirror/mode/django/django.min.js.gz',
            '/media/vendor/codemirror/mode/dockerfile/dockerfile.js',
            '/media/vendor/codemirror/mode/dockerfile/dockerfile.min.js',
            '/media/vendor/codemirror/mode/dockerfile/dockerfile.min.js.gz',
            '/media/vendor/codemirror/mode/dtd/dtd.js',
            '/media/vendor/codemirror/mode/dtd/dtd.min.js',
            '/media/vendor/codemirror/mode/dtd/dtd.min.js.gz',
            '/media/vendor/codemirror/mode/dylan/dylan.js',
            '/media/vendor/codemirror/mode/dylan/dylan.min.js',
            '/media/vendor/codemirror/mode/dylan/dylan.min.js.gz',
            '/media/vendor/codemirror/mode/ebnf/ebnf.js',
            '/media/vendor/codemirror/mode/ebnf/ebnf.min.js',
            '/media/vendor/codemirror/mode/ebnf/ebnf.min.js.gz',
            '/media/vendor/codemirror/mode/ecl/ecl.js',
            '/media/vendor/codemirror/mode/ecl/ecl.min.js',
            '/media/vendor/codemirror/mode/ecl/ecl.min.js.gz',
            '/media/vendor/codemirror/mode/eiffel/eiffel.js',
            '/media/vendor/codemirror/mode/eiffel/eiffel.min.js',
            '/media/vendor/codemirror/mode/eiffel/eiffel.min.js.gz',
            '/media/vendor/codemirror/mode/elm/elm.js',
            '/media/vendor/codemirror/mode/elm/elm.min.js',
            '/media/vendor/codemirror/mode/elm/elm.min.js.gz',
            '/media/vendor/codemirror/mode/erlang/erlang.js',
            '/media/vendor/codemirror/mode/erlang/erlang.min.js',
            '/media/vendor/codemirror/mode/erlang/erlang.min.js.gz',
            '/media/vendor/codemirror/mode/factor/factor.js',
            '/media/vendor/codemirror/mode/factor/factor.min.js',
            '/media/vendor/codemirror/mode/factor/factor.min.js.gz',
            '/media/vendor/codemirror/mode/fcl/fcl.js',
            '/media/vendor/codemirror/mode/fcl/fcl.min.js',
            '/media/vendor/codemirror/mode/fcl/fcl.min.js.gz',
            '/media/vendor/codemirror/mode/forth/forth.js',
            '/media/vendor/codemirror/mode/forth/forth.min.js',
            '/media/vendor/codemirror/mode/forth/forth.min.js.gz',
            '/media/vendor/codemirror/mode/fortran/fortran.js',
            '/media/vendor/codemirror/mode/fortran/fortran.min.js',
            '/media/vendor/codemirror/mode/fortran/fortran.min.js.gz',
            '/media/vendor/codemirror/mode/gas/gas.js',
            '/media/vendor/codemirror/mode/gas/gas.min.js',
            '/media/vendor/codemirror/mode/gas/gas.min.js.gz',
            '/media/vendor/codemirror/mode/gfm/gfm.js',
            '/media/vendor/codemirror/mode/gfm/gfm.min.js',
            '/media/vendor/codemirror/mode/gfm/gfm.min.js.gz',
            '/media/vendor/codemirror/mode/gherkin/gherkin.js',
            '/media/vendor/codemirror/mode/gherkin/gherkin.min.js',
            '/media/vendor/codemirror/mode/gherkin/gherkin.min.js.gz',
            '/media/vendor/codemirror/mode/go/go.js',
            '/media/vendor/codemirror/mode/go/go.min.js',
            '/media/vendor/codemirror/mode/go/go.min.js.gz',
            '/media/vendor/codemirror/mode/groovy/groovy.js',
            '/media/vendor/codemirror/mode/groovy/groovy.min.js',
            '/media/vendor/codemirror/mode/groovy/groovy.min.js.gz',
            '/media/vendor/codemirror/mode/haml/haml.js',
            '/media/vendor/codemirror/mode/haml/haml.min.js',
            '/media/vendor/codemirror/mode/haml/haml.min.js.gz',
            '/media/vendor/codemirror/mode/handlebars/handlebars.js',
            '/media/vendor/codemirror/mode/handlebars/handlebars.min.js',
            '/media/vendor/codemirror/mode/handlebars/handlebars.min.js.gz',
            '/media/vendor/codemirror/mode/haskell-literate/haskell-literate.js',
            '/media/vendor/codemirror/mode/haskell-literate/haskell-literate.min.js',
            '/media/vendor/codemirror/mode/haskell-literate/haskell-literate.min.js.gz',
            '/media/vendor/codemirror/mode/haskell/haskell.js',
            '/media/vendor/codemirror/mode/haskell/haskell.min.js',
            '/media/vendor/codemirror/mode/haskell/haskell.min.js.gz',
            '/media/vendor/codemirror/mode/haxe/haxe.js',
            '/media/vendor/codemirror/mode/haxe/haxe.min.js',
            '/media/vendor/codemirror/mode/haxe/haxe.min.js.gz',
            '/media/vendor/codemirror/mode/htmlembedded/htmlembedded.js',
            '/media/vendor/codemirror/mode/htmlembedded/htmlembedded.min.js',
            '/media/vendor/codemirror/mode/htmlembedded/htmlembedded.min.js.gz',
            '/media/vendor/codemirror/mode/htmlmixed/htmlmixed.js',
            '/media/vendor/codemirror/mode/htmlmixed/htmlmixed.min.js',
            '/media/vendor/codemirror/mode/htmlmixed/htmlmixed.min.js.gz',
            '/media/vendor/codemirror/mode/http/http.js',
            '/media/vendor/codemirror/mode/http/http.min.js',
            '/media/vendor/codemirror/mode/http/http.min.js.gz',
            '/media/vendor/codemirror/mode/idl/idl.js',
            '/media/vendor/codemirror/mode/idl/idl.min.js',
            '/media/vendor/codemirror/mode/idl/idl.min.js.gz',
            '/media/vendor/codemirror/mode/javascript/javascript.js',
            '/media/vendor/codemirror/mode/javascript/javascript.min.js',
            '/media/vendor/codemirror/mode/javascript/javascript.min.js.gz',
            '/media/vendor/codemirror/mode/jinja2/jinja2.js',
            '/media/vendor/codemirror/mode/jinja2/jinja2.min.js',
            '/media/vendor/codemirror/mode/jinja2/jinja2.min.js.gz',
            '/media/vendor/codemirror/mode/jsx/jsx.js',
            '/media/vendor/codemirror/mode/jsx/jsx.min.js',
            '/media/vendor/codemirror/mode/jsx/jsx.min.js.gz',
            '/media/vendor/codemirror/mode/julia/julia.js',
            '/media/vendor/codemirror/mode/julia/julia.min.js',
            '/media/vendor/codemirror/mode/julia/julia.min.js.gz',
            '/media/vendor/codemirror/mode/livescript/livescript.js',
            '/media/vendor/codemirror/mode/livescript/livescript.min.js',
            '/media/vendor/codemirror/mode/livescript/livescript.min.js.gz',
            '/media/vendor/codemirror/mode/lua/lua.js',
            '/media/vendor/codemirror/mode/lua/lua.min.js',
            '/media/vendor/codemirror/mode/lua/lua.min.js.gz',
            '/media/vendor/codemirror/mode/markdown/markdown.js',
            '/media/vendor/codemirror/mode/markdown/markdown.min.js',
            '/media/vendor/codemirror/mode/markdown/markdown.min.js.gz',
            '/media/vendor/codemirror/mode/mathematica/mathematica.js',
            '/media/vendor/codemirror/mode/mathematica/mathematica.min.js',
            '/media/vendor/codemirror/mode/mathematica/mathematica.min.js.gz',
            '/media/vendor/codemirror/mode/mbox/mbox.js',
            '/media/vendor/codemirror/mode/mbox/mbox.min.js',
            '/media/vendor/codemirror/mode/mbox/mbox.min.js.gz',
            '/media/vendor/codemirror/mode/meta.js',
            '/media/vendor/codemirror/mode/meta.min.js',
            '/media/vendor/codemirror/mode/meta.min.js.gz',
            '/media/vendor/codemirror/mode/mirc/mirc.js',
            '/media/vendor/codemirror/mode/mirc/mirc.min.js',
            '/media/vendor/codemirror/mode/mirc/mirc.min.js.gz',
            '/media/vendor/codemirror/mode/mllike/mllike.js',
            '/media/vendor/codemirror/mode/mllike/mllike.min.js',
            '/media/vendor/codemirror/mode/mllike/mllike.min.js.gz',
            '/media/vendor/codemirror/mode/modelica/modelica.js',
            '/media/vendor/codemirror/mode/modelica/modelica.min.js',
            '/media/vendor/codemirror/mode/modelica/modelica.min.js.gz',
            '/media/vendor/codemirror/mode/mscgen/mscgen.js',
            '/media/vendor/codemirror/mode/mscgen/mscgen.min.js',
            '/media/vendor/codemirror/mode/mscgen/mscgen.min.js.gz',
            '/media/vendor/codemirror/mode/mumps/mumps.js',
            '/media/vendor/codemirror/mode/mumps/mumps.min.js',
            '/media/vendor/codemirror/mode/mumps/mumps.min.js.gz',
            '/media/vendor/codemirror/mode/nginx/nginx.js',
            '/media/vendor/codemirror/mode/nginx/nginx.min.js',
            '/media/vendor/codemirror/mode/nginx/nginx.min.js.gz',
            '/media/vendor/codemirror/mode/nsis/nsis.js',
            '/media/vendor/codemirror/mode/nsis/nsis.min.js',
            '/media/vendor/codemirror/mode/nsis/nsis.min.js.gz',
            '/media/vendor/codemirror/mode/ntriples/ntriples.js',
            '/media/vendor/codemirror/mode/ntriples/ntriples.min.js',
            '/media/vendor/codemirror/mode/ntriples/ntriples.min.js.gz',
            '/media/vendor/codemirror/mode/octave/octave.js',
            '/media/vendor/codemirror/mode/octave/octave.min.js',
            '/media/vendor/codemirror/mode/octave/octave.min.js.gz',
            '/media/vendor/codemirror/mode/oz/oz.js',
            '/media/vendor/codemirror/mode/oz/oz.min.js',
            '/media/vendor/codemirror/mode/oz/oz.min.js.gz',
            '/media/vendor/codemirror/mode/pascal/pascal.js',
            '/media/vendor/codemirror/mode/pascal/pascal.min.js',
            '/media/vendor/codemirror/mode/pascal/pascal.min.js.gz',
            '/media/vendor/codemirror/mode/pegjs/pegjs.js',
            '/media/vendor/codemirror/mode/pegjs/pegjs.min.js',
            '/media/vendor/codemirror/mode/pegjs/pegjs.min.js.gz',
            '/media/vendor/codemirror/mode/perl/perl.js',
            '/media/vendor/codemirror/mode/perl/perl.min.js',
            '/media/vendor/codemirror/mode/perl/perl.min.js.gz',
            '/media/vendor/codemirror/mode/php/php.js',
            '/media/vendor/codemirror/mode/php/php.min.js',
            '/media/vendor/codemirror/mode/php/php.min.js.gz',
            '/media/vendor/codemirror/mode/pig/pig.js',
            '/media/vendor/codemirror/mode/pig/pig.min.js',
            '/media/vendor/codemirror/mode/pig/pig.min.js.gz',
            '/media/vendor/codemirror/mode/powershell/powershell.js',
            '/media/vendor/codemirror/mode/powershell/powershell.min.js',
            '/media/vendor/codemirror/mode/powershell/powershell.min.js.gz',
            '/media/vendor/codemirror/mode/properties/properties.js',
            '/media/vendor/codemirror/mode/properties/properties.min.js',
            '/media/vendor/codemirror/mode/properties/properties.min.js.gz',
            '/media/vendor/codemirror/mode/protobuf/protobuf.js',
            '/media/vendor/codemirror/mode/protobuf/protobuf.min.js',
            '/media/vendor/codemirror/mode/protobuf/protobuf.min.js.gz',
            '/media/vendor/codemirror/mode/pug/pug.js',
            '/media/vendor/codemirror/mode/pug/pug.min.js',
            '/media/vendor/codemirror/mode/pug/pug.min.js.gz',
            '/media/vendor/codemirror/mode/puppet/puppet.js',
            '/media/vendor/codemirror/mode/puppet/puppet.min.js',
            '/media/vendor/codemirror/mode/puppet/puppet.min.js.gz',
            '/media/vendor/codemirror/mode/python/python.js',
            '/media/vendor/codemirror/mode/python/python.min.js',
            '/media/vendor/codemirror/mode/python/python.min.js.gz',
            '/media/vendor/codemirror/mode/q/q.js',
            '/media/vendor/codemirror/mode/q/q.min.js',
            '/media/vendor/codemirror/mode/q/q.min.js.gz',
            '/media/vendor/codemirror/mode/r/r.js',
            '/media/vendor/codemirror/mode/r/r.min.js',
            '/media/vendor/codemirror/mode/r/r.min.js.gz',
            '/media/vendor/codemirror/mode/rpm/changes/index.html',
            '/media/vendor/codemirror/mode/rpm/rpm.js',
            '/media/vendor/codemirror/mode/rpm/rpm.min.js',
            '/media/vendor/codemirror/mode/rpm/rpm.min.js.gz',
            '/media/vendor/codemirror/mode/rst/rst.js',
            '/media/vendor/codemirror/mode/rst/rst.min.js',
            '/media/vendor/codemirror/mode/rst/rst.min.js.gz',
            '/media/vendor/codemirror/mode/ruby/ruby.js',
            '/media/vendor/codemirror/mode/ruby/ruby.min.js',
            '/media/vendor/codemirror/mode/ruby/ruby.min.js.gz',
            '/media/vendor/codemirror/mode/rust/rust.js',
            '/media/vendor/codemirror/mode/rust/rust.min.js',
            '/media/vendor/codemirror/mode/rust/rust.min.js.gz',
            '/media/vendor/codemirror/mode/sas/sas.js',
            '/media/vendor/codemirror/mode/sas/sas.min.js',
            '/media/vendor/codemirror/mode/sas/sas.min.js.gz',
            '/media/vendor/codemirror/mode/sass/sass.js',
            '/media/vendor/codemirror/mode/sass/sass.min.js',
            '/media/vendor/codemirror/mode/sass/sass.min.js.gz',
            '/media/vendor/codemirror/mode/scheme/scheme.js',
            '/media/vendor/codemirror/mode/scheme/scheme.min.js',
            '/media/vendor/codemirror/mode/scheme/scheme.min.js.gz',
            '/media/vendor/codemirror/mode/shell/shell.js',
            '/media/vendor/codemirror/mode/shell/shell.min.js',
            '/media/vendor/codemirror/mode/shell/shell.min.js.gz',
            '/media/vendor/codemirror/mode/sieve/sieve.js',
            '/media/vendor/codemirror/mode/sieve/sieve.min.js',
            '/media/vendor/codemirror/mode/sieve/sieve.min.js.gz',
            '/media/vendor/codemirror/mode/slim/slim.js',
            '/media/vendor/codemirror/mode/slim/slim.min.js',
            '/media/vendor/codemirror/mode/slim/slim.min.js.gz',
            '/media/vendor/codemirror/mode/smalltalk/smalltalk.js',
            '/media/vendor/codemirror/mode/smalltalk/smalltalk.min.js',
            '/media/vendor/codemirror/mode/smalltalk/smalltalk.min.js.gz',
            '/media/vendor/codemirror/mode/smarty/smarty.js',
            '/media/vendor/codemirror/mode/smarty/smarty.min.js',
            '/media/vendor/codemirror/mode/smarty/smarty.min.js.gz',
            '/media/vendor/codemirror/mode/solr/solr.js',
            '/media/vendor/codemirror/mode/solr/solr.min.js',
            '/media/vendor/codemirror/mode/solr/solr.min.js.gz',
            '/media/vendor/codemirror/mode/soy/soy.js',
            '/media/vendor/codemirror/mode/soy/soy.min.js',
            '/media/vendor/codemirror/mode/soy/soy.min.js.gz',
            '/media/vendor/codemirror/mode/sparql/sparql.js',
            '/media/vendor/codemirror/mode/sparql/sparql.min.js',
            '/media/vendor/codemirror/mode/sparql/sparql.min.js.gz',
            '/media/vendor/codemirror/mode/spreadsheet/spreadsheet.js',
            '/media/vendor/codemirror/mode/spreadsheet/spreadsheet.min.js',
            '/media/vendor/codemirror/mode/spreadsheet/spreadsheet.min.js.gz',
            '/media/vendor/codemirror/mode/sql/sql.js',
            '/media/vendor/codemirror/mode/sql/sql.min.js',
            '/media/vendor/codemirror/mode/sql/sql.min.js.gz',
            '/media/vendor/codemirror/mode/stex/stex.js',
            '/media/vendor/codemirror/mode/stex/stex.min.js',
            '/media/vendor/codemirror/mode/stex/stex.min.js.gz',
            '/media/vendor/codemirror/mode/stylus/stylus.js',
            '/media/vendor/codemirror/mode/stylus/stylus.min.js',
            '/media/vendor/codemirror/mode/stylus/stylus.min.js.gz',
            '/media/vendor/codemirror/mode/swift/swift.js',
            '/media/vendor/codemirror/mode/swift/swift.min.js',
            '/media/vendor/codemirror/mode/swift/swift.min.js.gz',
            '/media/vendor/codemirror/mode/tcl/tcl.js',
            '/media/vendor/codemirror/mode/tcl/tcl.min.js',
            '/media/vendor/codemirror/mode/tcl/tcl.min.js.gz',
            '/media/vendor/codemirror/mode/textile/textile.js',
            '/media/vendor/codemirror/mode/textile/textile.min.js',
            '/media/vendor/codemirror/mode/textile/textile.min.js.gz',
            '/media/vendor/codemirror/mode/tiddlywiki/tiddlywiki.css',
            '/media/vendor/codemirror/mode/tiddlywiki/tiddlywiki.js',
            '/media/vendor/codemirror/mode/tiddlywiki/tiddlywiki.min.js',
            '/media/vendor/codemirror/mode/tiddlywiki/tiddlywiki.min.js.gz',
            '/media/vendor/codemirror/mode/tiki/tiki.css',
            '/media/vendor/codemirror/mode/tiki/tiki.js',
            '/media/vendor/codemirror/mode/tiki/tiki.min.js',
            '/media/vendor/codemirror/mode/tiki/tiki.min.js.gz',
            '/media/vendor/codemirror/mode/toml/toml.js',
            '/media/vendor/codemirror/mode/toml/toml.min.js',
            '/media/vendor/codemirror/mode/toml/toml.min.js.gz',
            '/media/vendor/codemirror/mode/tornado/tornado.js',
            '/media/vendor/codemirror/mode/tornado/tornado.min.js',
            '/media/vendor/codemirror/mode/tornado/tornado.min.js.gz',
            '/media/vendor/codemirror/mode/troff/troff.js',
            '/media/vendor/codemirror/mode/troff/troff.min.js',
            '/media/vendor/codemirror/mode/troff/troff.min.js.gz',
            '/media/vendor/codemirror/mode/ttcn-cfg/ttcn-cfg.js',
            '/media/vendor/codemirror/mode/ttcn-cfg/ttcn-cfg.min.js',
            '/media/vendor/codemirror/mode/ttcn-cfg/ttcn-cfg.min.js.gz',
            '/media/vendor/codemirror/mode/ttcn/ttcn.js',
            '/media/vendor/codemirror/mode/ttcn/ttcn.min.js',
            '/media/vendor/codemirror/mode/ttcn/ttcn.min.js.gz',
            '/media/vendor/codemirror/mode/turtle/turtle.js',
            '/media/vendor/codemirror/mode/turtle/turtle.min.js',
            '/media/vendor/codemirror/mode/turtle/turtle.min.js.gz',
            '/media/vendor/codemirror/mode/twig/twig.js',
            '/media/vendor/codemirror/mode/twig/twig.min.js',
            '/media/vendor/codemirror/mode/twig/twig.min.js.gz',
            '/media/vendor/codemirror/mode/vb/vb.js',
            '/media/vendor/codemirror/mode/vb/vb.min.js',
            '/media/vendor/codemirror/mode/vb/vb.min.js.gz',
            '/media/vendor/codemirror/mode/vbscript/vbscript.js',
            '/media/vendor/codemirror/mode/vbscript/vbscript.min.js',
            '/media/vendor/codemirror/mode/vbscript/vbscript.min.js.gz',
            '/media/vendor/codemirror/mode/velocity/velocity.js',
            '/media/vendor/codemirror/mode/velocity/velocity.min.js',
            '/media/vendor/codemirror/mode/velocity/velocity.min.js.gz',
            '/media/vendor/codemirror/mode/verilog/verilog.js',
            '/media/vendor/codemirror/mode/verilog/verilog.min.js',
            '/media/vendor/codemirror/mode/verilog/verilog.min.js.gz',
            '/media/vendor/codemirror/mode/vhdl/vhdl.js',
            '/media/vendor/codemirror/mode/vhdl/vhdl.min.js',
            '/media/vendor/codemirror/mode/vhdl/vhdl.min.js.gz',
            '/media/vendor/codemirror/mode/vue/vue.js',
            '/media/vendor/codemirror/mode/vue/vue.min.js',
            '/media/vendor/codemirror/mode/vue/vue.min.js.gz',
            '/media/vendor/codemirror/mode/wast/wast.js',
            '/media/vendor/codemirror/mode/wast/wast.min.js',
            '/media/vendor/codemirror/mode/wast/wast.min.js.gz',
            '/media/vendor/codemirror/mode/webidl/webidl.js',
            '/media/vendor/codemirror/mode/webidl/webidl.min.js',
            '/media/vendor/codemirror/mode/webidl/webidl.min.js.gz',
            '/media/vendor/codemirror/mode/xml/xml.js',
            '/media/vendor/codemirror/mode/xml/xml.min.js',
            '/media/vendor/codemirror/mode/xml/xml.min.js.gz',
            '/media/vendor/codemirror/mode/xquery/xquery.js',
            '/media/vendor/codemirror/mode/xquery/xquery.min.js',
            '/media/vendor/codemirror/mode/xquery/xquery.min.js.gz',
            '/media/vendor/codemirror/mode/yacas/yacas.js',
            '/media/vendor/codemirror/mode/yacas/yacas.min.js',
            '/media/vendor/codemirror/mode/yacas/yacas.min.js.gz',
            '/media/vendor/codemirror/mode/yaml-frontmatter/yaml-frontmatter.js',
            '/media/vendor/codemirror/mode/yaml-frontmatter/yaml-frontmatter.min.js',
            '/media/vendor/codemirror/mode/yaml-frontmatter/yaml-frontmatter.min.js.gz',
            '/media/vendor/codemirror/mode/yaml/yaml.js',
            '/media/vendor/codemirror/mode/yaml/yaml.min.js',
            '/media/vendor/codemirror/mode/yaml/yaml.min.js.gz',
            '/media/vendor/codemirror/mode/z80/z80.js',
            '/media/vendor/codemirror/mode/z80/z80.min.js',
            '/media/vendor/codemirror/mode/z80/z80.min.js.gz',
            '/media/vendor/codemirror/theme/3024-day.css',
            '/media/vendor/codemirror/theme/3024-night.css',
            '/media/vendor/codemirror/theme/abbott.css',
            '/media/vendor/codemirror/theme/abcdef.css',
            '/media/vendor/codemirror/theme/ambiance-mobile.css',
            '/media/vendor/codemirror/theme/ambiance.css',
            '/media/vendor/codemirror/theme/ayu-dark.css',
            '/media/vendor/codemirror/theme/ayu-mirage.css',
            '/media/vendor/codemirror/theme/base16-dark.css',
            '/media/vendor/codemirror/theme/base16-light.css',
            '/media/vendor/codemirror/theme/bespin.css',
            '/media/vendor/codemirror/theme/blackboard.css',
            '/media/vendor/codemirror/theme/cobalt.css',
            '/media/vendor/codemirror/theme/colorforth.css',
            '/media/vendor/codemirror/theme/darcula.css',
            '/media/vendor/codemirror/theme/dracula.css',
            '/media/vendor/codemirror/theme/duotone-dark.css',
            '/media/vendor/codemirror/theme/duotone-light.css',
            '/media/vendor/codemirror/theme/eclipse.css',
            '/media/vendor/codemirror/theme/elegant.css',
            '/media/vendor/codemirror/theme/erlang-dark.css',
            '/media/vendor/codemirror/theme/gruvbox-dark.css',
            '/media/vendor/codemirror/theme/hopscotch.css',
            '/media/vendor/codemirror/theme/icecoder.css',
            '/media/vendor/codemirror/theme/idea.css',
            '/media/vendor/codemirror/theme/isotope.css',
            '/media/vendor/codemirror/theme/juejin.css',
            '/media/vendor/codemirror/theme/lesser-dark.css',
            '/media/vendor/codemirror/theme/liquibyte.css',
            '/media/vendor/codemirror/theme/lucario.css',
            '/media/vendor/codemirror/theme/material-darker.css',
            '/media/vendor/codemirror/theme/material-ocean.css',
            '/media/vendor/codemirror/theme/material-palenight.css',
            '/media/vendor/codemirror/theme/material.css',
            '/media/vendor/codemirror/theme/mbo.css',
            '/media/vendor/codemirror/theme/mdn-like.css',
            '/media/vendor/codemirror/theme/midnight.css',
            '/media/vendor/codemirror/theme/monokai.css',
            '/media/vendor/codemirror/theme/moxer.css',
            '/media/vendor/codemirror/theme/neat.css',
            '/media/vendor/codemirror/theme/neo.css',
            '/media/vendor/codemirror/theme/night.css',
            '/media/vendor/codemirror/theme/nord.css',
            '/media/vendor/codemirror/theme/oceanic-next.css',
            '/media/vendor/codemirror/theme/panda-syntax.css',
            '/media/vendor/codemirror/theme/paraiso-dark.css',
            '/media/vendor/codemirror/theme/paraiso-light.css',
            '/media/vendor/codemirror/theme/pastel-on-dark.css',
            '/media/vendor/codemirror/theme/railscasts.css',
            '/media/vendor/codemirror/theme/rubyblue.css',
            '/media/vendor/codemirror/theme/seti.css',
            '/media/vendor/codemirror/theme/shadowfox.css',
            '/media/vendor/codemirror/theme/solarized.css',
            '/media/vendor/codemirror/theme/ssms.css',
            '/media/vendor/codemirror/theme/the-matrix.css',
            '/media/vendor/codemirror/theme/tomorrow-night-bright.css',
            '/media/vendor/codemirror/theme/tomorrow-night-eighties.css',
            '/media/vendor/codemirror/theme/ttcn.css',
            '/media/vendor/codemirror/theme/twilight.css',
            '/media/vendor/codemirror/theme/vibrant-ink.css',
            '/media/vendor/codemirror/theme/xq-dark.css',
            '/media/vendor/codemirror/theme/xq-light.css',
            '/media/vendor/codemirror/theme/yeti.css',
            '/media/vendor/codemirror/theme/yonce.css',
            '/media/vendor/codemirror/theme/zenburn.css',
            '/plugins/editors/codemirror/fonts.json',
            '/plugins/editors/codemirror/layouts/editors/codemirror/element.php',
            '/plugins/editors/codemirror/layouts/editors/codemirror/styles.php',
            '/plugins/editors/codemirror/src/Field/FontsField.php',
            // From 5.0.0-alpha3 to 5.0.0-alpha4
            '/libraries/src/Event/Application/DeamonForkEvent.php',
            '/libraries/src/Event/Application/DeamonReceiveSignalEvent.php',
            '/media/plg_editors_tinymce/js/plugins/highlighter/plugin.js',
            '/media/plg_editors_tinymce/js/plugins/highlighter/plugin.min.js',
            '/media/plg_editors_tinymce/js/plugins/highlighter/plugin.min.js.gz',
            '/media/plg_editors_tinymce/js/plugins/highlighter/source.css',
            '/media/plg_editors_tinymce/js/plugins/highlighter/source.html',
            '/media/plg_editors_tinymce/js/plugins/highlighter/source.js',
            '/media/plg_editors_tinymce/js/plugins/highlighter/source.min.css',
            '/media/plg_editors_tinymce/js/plugins/highlighter/source.min.css.gz',
            '/media/plg_editors_tinymce/js/plugins/highlighter/source.min.js',
            '/media/plg_editors_tinymce/js/plugins/highlighter/source.min.js.gz',
            '/media/plg_system_compat/es5.asset.json',
            // From 5.0.0-alpha4 to 5.0.0-beta1
            '/administrator/components/com_categories/tmpl/categories/default_batch_footer.php',
            '/administrator/components/com_content/tmpl/articles/default_batch_footer.php',
            '/administrator/language/en-GB/plg_twofactorauth_totp.ini',
            '/administrator/language/en-GB/plg_twofactorauth_totp.sys.ini',
            '/administrator/language/en-GB/plg_twofactorauth_yubikey.ini',
            '/administrator/language/en-GB/plg_twofactorauth_yubikey.sys.ini',
            '/media/com_contenthistory/js/admin-history-versions.js',
            '/media/com_contenthistory/js/admin-history-versions.min.js',
            '/media/com_contenthistory/js/admin-history-versions.min.js.gz',
            // From 5.0.0-beta1 to 5.0.0-beta2
            '/language/en-GB/lib_simplepie.sys.ini',
            '/libraries/src/Cache/Storage/WincacheStorage.php',
            // From 5.0.0-beta2 to 5.0.0-beta3
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/AbstractString.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/AbstractTime.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/ASNObject.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Base128.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Composite/AttributeTypeAndValue.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Composite/RDNString.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Composite/RelativeDistinguishedName.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Construct.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Exception/NotImplementedException.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Exception/ParserException.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/ExplicitlyTaggedObject.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Identifier.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/OID.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Parsable.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/TemplateParser.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/BitString.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/BMPString.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/Boolean.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/CharacterString.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/Enumerated.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/GeneralizedTime.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/GeneralString.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/GraphicString.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/IA5String.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/Integer.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/NullObject.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/NumericString.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/ObjectDescriptor.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/ObjectIdentifier.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/OctetString.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/PrintableString.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/RelativeObjectIdentifier.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/Sequence.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/Set.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/T61String.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/UniversalString.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/UTCTime.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/UTF8String.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal/VisibleString.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/UnknownConstructedObject.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/UnknownObject.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/Utility/BigInteger.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/Utility/BigIntegerBcmath.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/Utility/BigIntegerGmp.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/X509/AlgorithmIdentifier.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/X509/CertificateExtensions.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/X509/CertificateSubject.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/X509/CSR/Attributes.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/X509/CSR/CSR.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/X509/PrivateKey.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/X509/PublicKey.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/X509/SAN/DNSName.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/X509/SAN/IPAddress.php',
            '/libraries/vendor/fgrosse/phpasn1/lib/X509/SAN/SubjectAlternativeNames.php',
            '/libraries/vendor/fgrosse/phpasn1/LICENSE',
            '/libraries/vendor/stella-maris/clock/LICENSE.md',
            '/libraries/vendor/stella-maris/clock/src/ClockInterface.php',
            '/media/com_scheduler/css/admin-view-select-task.css',
            '/media/com_scheduler/css/admin-view-select-task.min.css',
            '/media/com_scheduler/css/admin-view-select-task.min.css.gz',
            '/media/system/css/calendar-jos.css',
            '/media/system/css/calendar-jos.min.css',
            '/media/system/css/calendar-jos.min.css.gz',
            '/media/system/css/debug.css',
            '/media/system/css/debug.min.css',
            '/media/system/css/debug.min.css.gz',
            '/media/system/css/frontediting.css',
            '/media/system/css/frontediting.min.css',
            '/media/system/css/frontediting.min.css.gz',
            '/media/system/css/mootree.css',
            '/media/system/css/mootree.min.css',
            '/media/system/css/mootree.min.css.gz',
            '/media/system/css/mootree_rtl.css',
            '/media/system/css/mootree_rtl.min.css',
            '/media/system/css/mootree_rtl.min.css.gz',
            '/media/system/css/sortablelist.css',
            '/media/system/css/sortablelist.min.css',
            '/media/system/css/sortablelist.min.css.gz',
            // From 5.0.0 to 5.1.0-alpha1
            '/administrator/components/com_banners/tmpl/banners/default_batch_footer.php',
            '/administrator/components/com_contact/tmpl/contacts/default_batch_footer.php',
            '/administrator/components/com_fields/tmpl/fields/default_batch_footer.php',
            '/administrator/components/com_fields/tmpl/groups/default_batch_footer.php',
            '/administrator/components/com_menus/tmpl/items/default_batch_footer.php',
            '/administrator/components/com_modules/tmpl/modules/default_batch_footer.php',
            '/administrator/components/com_newsfeeds/tmpl/newsfeeds/default_batch_footer.php',
            '/administrator/components/com_tags/tmpl/tags/default_batch_footer.php',
            '/administrator/components/com_users/tmpl/users/default_batch_footer.php',
            // From 5.1.0-alpha3 to 5.1.0-alpha4
            '/administrator/components/com_redirect/tmpl/links/default_batch_footer.php',
            '/modules/mod_banners/mod_banners.php',
            // From 5.1.0-alpha4 to 5.1.0-beta1
            '/administrator/modules/mod_custom/mod_custom.php',
            '/administrator/modules/mod_frontend/mod_frontend.php',
            '/administrator/modules/mod_latestactions/mod_latestactions.php',
            '/administrator/modules/mod_loginsupport/mod_loginsupport.php',
            '/administrator/modules/mod_messages/mod_messages.php',
            '/administrator/modules/mod_multilangstatus/mod_multilangstatus.php',
            '/administrator/modules/mod_sampledata/mod_sampledata.php',
            '/administrator/modules/mod_stats_admin/mod_stats_admin.php',
            '/administrator/modules/mod_title/mod_title.php',
            '/administrator/modules/mod_toolbar/mod_toolbar.php',
            '/administrator/modules/mod_user/mod_user.php',
            '/administrator/modules/mod_version/mod_version.php',
            '/libraries/vendor/web-token/jwt-core/Algorithm.php',
            '/libraries/vendor/web-token/jwt-core/AlgorithmManager.php',
            '/libraries/vendor/web-token/jwt-core/AlgorithmManagerFactory.php',
            '/libraries/vendor/web-token/jwt-core/JWK.php',
            '/libraries/vendor/web-token/jwt-core/JWKSet.php',
            '/libraries/vendor/web-token/jwt-core/JWT.php',
            '/libraries/vendor/web-token/jwt-core/LICENSE',
            '/libraries/vendor/web-token/jwt-core/Util/BigInteger.php',
            '/libraries/vendor/web-token/jwt-core/Util/ECKey.php',
            '/libraries/vendor/web-token/jwt-core/Util/ECSignature.php',
            '/libraries/vendor/web-token/jwt-core/Util/Hash.php',
            '/libraries/vendor/web-token/jwt-core/Util/JsonConverter.php',
            '/libraries/vendor/web-token/jwt-core/Util/KeyChecker.php',
            '/libraries/vendor/web-token/jwt-core/Util/RSAKey.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-ecdsa/ECDSA.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-ecdsa/ES256.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-ecdsa/ES384.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-ecdsa/ES512.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-eddsa/EdDSA.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-experimental/Blake2b.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-experimental/ES256K.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-experimental/HS1.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-experimental/HS256_64.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-experimental/RS1.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-hmac/HMAC.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-hmac/HS256.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-hmac/HS384.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-hmac/HS512.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-none/None.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-rsa/PS256.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-rsa/PS384.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-rsa/PS512.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-rsa/RS256.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-rsa/RS384.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-rsa/RS512.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-rsa/RSAPKCS1.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-rsa/RSAPSS.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-rsa/Util/RSA.php',
            '/libraries/vendor/web-token/jwt-signature/Algorithm/MacAlgorithm.php',
            '/libraries/vendor/web-token/jwt-signature/Algorithm/SignatureAlgorithm.php',
            '/libraries/vendor/web-token/jwt-signature/JWS.php',
            '/libraries/vendor/web-token/jwt-signature/JWSBuilder.php',
            '/libraries/vendor/web-token/jwt-signature/JWSBuilderFactory.php',
            '/libraries/vendor/web-token/jwt-signature/JWSLoader.php',
            '/libraries/vendor/web-token/jwt-signature/JWSLoaderFactory.php',
            '/libraries/vendor/web-token/jwt-signature/JWSTokenSupport.php',
            '/libraries/vendor/web-token/jwt-signature/JWSVerifier.php',
            '/libraries/vendor/web-token/jwt-signature/JWSVerifierFactory.php',
            '/libraries/vendor/web-token/jwt-signature/LICENSE',
            '/libraries/vendor/web-token/jwt-signature/Serializer/CompactSerializer.php',
            '/libraries/vendor/web-token/jwt-signature/Serializer/JSONFlattenedSerializer.php',
            '/libraries/vendor/web-token/jwt-signature/Serializer/JSONGeneralSerializer.php',
            '/libraries/vendor/web-token/jwt-signature/Serializer/JWSSerializer.php',
            '/libraries/vendor/web-token/jwt-signature/Serializer/JWSSerializerManager.php',
            '/libraries/vendor/web-token/jwt-signature/Serializer/JWSSerializerManagerFactory.php',
            '/libraries/vendor/web-token/jwt-signature/Serializer/Serializer.php',
            '/libraries/vendor/web-token/jwt-signature/Signature.php',
            '/media/plg_system_jooa11y/css/jooa11y.css',
            '/media/plg_system_jooa11y/css/jooa11y.min.css',
            '/media/plg_system_jooa11y/css/jooa11y.min.css.gz',
            '/media/plg_system_jooa11y/scss/jooa11y.scss',
            '/media/vendor/joomla-a11y-checker/LICENSE.md',
            '/modules/mod_feed/mod_feed.php',
            '/modules/mod_languages/mod_languages.php',
            '/modules/mod_stats/mod_stats.php',
            '/modules/mod_syndicate/mod_syndicate.php',
            '/modules/mod_tags_popular/mod_tags_popular.php',
            '/modules/mod_tags_similar/mod_tags_similar.php',
            '/modules/mod_wrapper/mod_wrapper.php',
            // From 5.1.0-beta1 to 5.1.0-beta2
            '/administrator/modules/mod_login/mod_login.php',
            '/libraries/src/Event/Router/AfterInitialiseRouterEvent.php',
            '/libraries/src/Event/Router/RouterEvent.php',
            '/libraries/src/Http/HttpFactoryInterface.php',
            '/libraries/src/Service/Provider/Http.php',
            '/libraries/vendor/web-token/jwt-experimental/ContentEncryption/A128CCM_16_128.php',
            '/libraries/vendor/web-token/jwt-experimental/ContentEncryption/A128CCM_16_64.php',
            '/libraries/vendor/web-token/jwt-experimental/ContentEncryption/A128CCM_64_128.php',
            '/libraries/vendor/web-token/jwt-experimental/ContentEncryption/A128CCM_64_64.php',
            '/libraries/vendor/web-token/jwt-experimental/ContentEncryption/A256CCM_16_128.php',
            '/libraries/vendor/web-token/jwt-experimental/ContentEncryption/A256CCM_16_64.php',
            '/libraries/vendor/web-token/jwt-experimental/ContentEncryption/A256CCM_64_128.php',
            '/libraries/vendor/web-token/jwt-experimental/ContentEncryption/A256CCM_64_64.php',
            '/libraries/vendor/web-token/jwt-experimental/ContentEncryption/AESCCM.php',
            '/libraries/vendor/web-token/jwt-experimental/KeyEncryption/A128CTR.php',
            '/libraries/vendor/web-token/jwt-experimental/KeyEncryption/A192CTR.php',
            '/libraries/vendor/web-token/jwt-experimental/KeyEncryption/A256CTR.php',
            '/libraries/vendor/web-token/jwt-experimental/KeyEncryption/AESCTR.php',
            '/libraries/vendor/web-token/jwt-experimental/KeyEncryption/Chacha20Poly1305.php',
            '/libraries/vendor/web-token/jwt-experimental/KeyEncryption/RSAOAEP384.php',
            '/libraries/vendor/web-token/jwt-experimental/KeyEncryption/RSAOAEP512.php',
            '/libraries/vendor/web-token/jwt-experimental/LICENSE',
            '/libraries/vendor/web-token/jwt-experimental/Signature/Blake2b.php',
            '/libraries/vendor/web-token/jwt-experimental/Signature/ES256K.php',
            '/libraries/vendor/web-token/jwt-experimental/Signature/HS1.php',
            '/libraries/vendor/web-token/jwt-experimental/Signature/HS256_64.php',
            '/libraries/vendor/web-token/jwt-experimental/Signature/RS1.php',
            '/libraries/vendor/web-token/jwt-signature-algorithm-ecdsa/LICENSE',
            '/libraries/vendor/web-token/jwt-signature-algorithm-eddsa/LICENSE',
            '/libraries/vendor/web-token/jwt-signature-algorithm-experimental/LICENSE',
            '/libraries/vendor/web-token/jwt-signature-algorithm-hmac/LICENSE',
            '/libraries/vendor/web-token/jwt-signature-algorithm-none/LICENSE',
            '/libraries/vendor/web-token/jwt-signature-algorithm-rsa/LICENSE',
            // From 5.1.0-beta2 to 5.1.0-rc1
            '/administrator/components/com_admin/sql/updates/mysql/4.4.4-2024-03-28.sql',
            '/administrator/components/com_admin/sql/updates/postgresql/4.4.4-2024-03-28.sql',
            '/administrator/modules/mod_post_installation_messages/mod_post_installation_messages.php',
            '/media/vendor/punycode/LICENSE-MIT.txt',
            // From 5.1.0 to 5.1.1
            '/libraries/vendor/cweagans/composer-patches/LICENSE.md',
            '/libraries/vendor/cweagans/composer-patches/src/PatchEvent.php',
            '/libraries/vendor/cweagans/composer-patches/src/PatchEvents.php',
            '/libraries/vendor/cweagans/composer-patches/src/Patches.php',
            '/libraries/vendor/cweagans/composer-patches/tests/PatchEventTest.php',
            '/libraries/vendor/laminas/laminas-diactoros/PATCHES.txt',
            // From 5.1.2 to 5.1.3
            '/libraries/vendor/joomla/application/rector.php',
            '/libraries/vendor/joomla/console/.drone.jsonnet',
            '/libraries/vendor/joomla/console/.drone.yml',
            '/libraries/vendor/joomla/database/.drone.jsonnet',
            '/libraries/vendor/joomla/database/.drone.yml',
            '/libraries/vendor/joomla/database/phpunit.appveyor_sql2012sp1.xml.dist',
            '/libraries/vendor/joomla/database/phpunit.appveyor_sql2014.xml.dist',
            '/libraries/vendor/joomla/database/phpunit.appveyor_sql2017.xml.dist',
            '/libraries/vendor/joomla/database/phpunit.mariadb.xml.dist',
            '/libraries/vendor/joomla/database/phpunit.mysql.xml.dist',
            '/libraries/vendor/joomla/database/phpunit.mysqli.xml.dist',
            '/libraries/vendor/joomla/database/phpunit.pgsql.xml.dist',
            '/libraries/vendor/joomla/database/phpunit.sqlite.xml.dist',
            '/libraries/vendor/joomla/database/phpunit.sqlsrv.xml.dist',
            '/libraries/vendor/joomla/session/.drone.jsonnet',
            '/libraries/vendor/joomla/session/.drone.yml',
            // From 5.2.0-alpha2 to 5.2.0-alpha3
            '/libraries/vendor/maximebf/debugbar/src/DebugBar/Resources/vendor/font-awesome/fonts/FontAwesome.otf',
            '/libraries/vendor/maximebf/debugbar/src/DebugBar/Resources/vendor/font-awesome/fonts/fontawesome-webfont.eot',
            '/libraries/vendor/maximebf/debugbar/src/DebugBar/Resources/vendor/font-awesome/fonts/fontawesome-webfont.svg',
            '/libraries/vendor/maximebf/debugbar/src/DebugBar/Resources/vendor/font-awesome/fonts/fontawesome-webfont.ttf',
            '/libraries/vendor/maximebf/debugbar/src/DebugBar/Resources/vendor/font-awesome/fonts/fontawesome-webfont.woff',
            '/libraries/vendor/maximebf/debugbar/src/DebugBar/Resources/vendor/font-awesome/fonts/fontawesome-webfont.woff2',
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
            // From 5.0.0-alpha2 to 5.0.0-alpha3
            '/plugins/editors/codemirror/src/Field',
            '/media/vendor/codemirror/theme',
            '/media/vendor/codemirror/mode/z80',
            '/media/vendor/codemirror/mode/yaml-frontmatter',
            '/media/vendor/codemirror/mode/yaml',
            '/media/vendor/codemirror/mode/yacas',
            '/media/vendor/codemirror/mode/xquery',
            '/media/vendor/codemirror/mode/xml',
            '/media/vendor/codemirror/mode/webidl',
            '/media/vendor/codemirror/mode/wast',
            '/media/vendor/codemirror/mode/vue',
            '/media/vendor/codemirror/mode/vhdl',
            '/media/vendor/codemirror/mode/verilog',
            '/media/vendor/codemirror/mode/velocity',
            '/media/vendor/codemirror/mode/vbscript',
            '/media/vendor/codemirror/mode/vb',
            '/media/vendor/codemirror/mode/twig',
            '/media/vendor/codemirror/mode/turtle',
            '/media/vendor/codemirror/mode/ttcn-cfg',
            '/media/vendor/codemirror/mode/ttcn',
            '/media/vendor/codemirror/mode/troff',
            '/media/vendor/codemirror/mode/tornado',
            '/media/vendor/codemirror/mode/toml',
            '/media/vendor/codemirror/mode/tiki',
            '/media/vendor/codemirror/mode/tiddlywiki',
            '/media/vendor/codemirror/mode/textile',
            '/media/vendor/codemirror/mode/tcl',
            '/media/vendor/codemirror/mode/swift',
            '/media/vendor/codemirror/mode/stylus',
            '/media/vendor/codemirror/mode/stex',
            '/media/vendor/codemirror/mode/sql',
            '/media/vendor/codemirror/mode/spreadsheet',
            '/media/vendor/codemirror/mode/sparql',
            '/media/vendor/codemirror/mode/soy',
            '/media/vendor/codemirror/mode/solr',
            '/media/vendor/codemirror/mode/smarty',
            '/media/vendor/codemirror/mode/smalltalk',
            '/media/vendor/codemirror/mode/slim',
            '/media/vendor/codemirror/mode/sieve',
            '/media/vendor/codemirror/mode/shell',
            '/media/vendor/codemirror/mode/scheme',
            '/media/vendor/codemirror/mode/sass',
            '/media/vendor/codemirror/mode/sas',
            '/media/vendor/codemirror/mode/rust',
            '/media/vendor/codemirror/mode/ruby',
            '/media/vendor/codemirror/mode/rst',
            '/media/vendor/codemirror/mode/rpm/changes',
            '/media/vendor/codemirror/mode/rpm',
            '/media/vendor/codemirror/mode/r',
            '/media/vendor/codemirror/mode/q',
            '/media/vendor/codemirror/mode/python',
            '/media/vendor/codemirror/mode/puppet',
            '/media/vendor/codemirror/mode/pug',
            '/media/vendor/codemirror/mode/protobuf',
            '/media/vendor/codemirror/mode/properties',
            '/media/vendor/codemirror/mode/powershell',
            '/media/vendor/codemirror/mode/pig',
            '/media/vendor/codemirror/mode/php',
            '/media/vendor/codemirror/mode/perl',
            '/media/vendor/codemirror/mode/pegjs',
            '/media/vendor/codemirror/mode/pascal',
            '/media/vendor/codemirror/mode/oz',
            '/media/vendor/codemirror/mode/octave',
            '/media/vendor/codemirror/mode/ntriples',
            '/media/vendor/codemirror/mode/nsis',
            '/media/vendor/codemirror/mode/nginx',
            '/media/vendor/codemirror/mode/mumps',
            '/media/vendor/codemirror/mode/mscgen',
            '/media/vendor/codemirror/mode/modelica',
            '/media/vendor/codemirror/mode/mllike',
            '/media/vendor/codemirror/mode/mirc',
            '/media/vendor/codemirror/mode/mbox',
            '/media/vendor/codemirror/mode/mathematica',
            '/media/vendor/codemirror/mode/markdown',
            '/media/vendor/codemirror/mode/lua',
            '/media/vendor/codemirror/mode/livescript',
            '/media/vendor/codemirror/mode/julia',
            '/media/vendor/codemirror/mode/jsx',
            '/media/vendor/codemirror/mode/jinja2',
            '/media/vendor/codemirror/mode/javascript',
            '/media/vendor/codemirror/mode/idl',
            '/media/vendor/codemirror/mode/http',
            '/media/vendor/codemirror/mode/htmlmixed',
            '/media/vendor/codemirror/mode/htmlembedded',
            '/media/vendor/codemirror/mode/haxe',
            '/media/vendor/codemirror/mode/haskell-literate',
            '/media/vendor/codemirror/mode/haskell',
            '/media/vendor/codemirror/mode/handlebars',
            '/media/vendor/codemirror/mode/haml',
            '/media/vendor/codemirror/mode/groovy',
            '/media/vendor/codemirror/mode/go',
            '/media/vendor/codemirror/mode/gherkin',
            '/media/vendor/codemirror/mode/gfm',
            '/media/vendor/codemirror/mode/gas',
            '/media/vendor/codemirror/mode/fortran',
            '/media/vendor/codemirror/mode/forth',
            '/media/vendor/codemirror/mode/fcl',
            '/media/vendor/codemirror/mode/factor',
            '/media/vendor/codemirror/mode/erlang',
            '/media/vendor/codemirror/mode/elm',
            '/media/vendor/codemirror/mode/eiffel',
            '/media/vendor/codemirror/mode/ecl',
            '/media/vendor/codemirror/mode/ebnf',
            '/media/vendor/codemirror/mode/dylan',
            '/media/vendor/codemirror/mode/dtd',
            '/media/vendor/codemirror/mode/dockerfile',
            '/media/vendor/codemirror/mode/django',
            '/media/vendor/codemirror/mode/diff',
            '/media/vendor/codemirror/mode/dart',
            '/media/vendor/codemirror/mode/d',
            '/media/vendor/codemirror/mode/cypher',
            '/media/vendor/codemirror/mode/css',
            '/media/vendor/codemirror/mode/crystal',
            '/media/vendor/codemirror/mode/commonlisp',
            '/media/vendor/codemirror/mode/coffeescript',
            '/media/vendor/codemirror/mode/cobol',
            '/media/vendor/codemirror/mode/cmake',
            '/media/vendor/codemirror/mode/clojure',
            '/media/vendor/codemirror/mode/clike',
            '/media/vendor/codemirror/mode/brainfuck',
            '/media/vendor/codemirror/mode/asterisk',
            '/media/vendor/codemirror/mode/asn.1',
            '/media/vendor/codemirror/mode/asciiarmor',
            '/media/vendor/codemirror/mode/apl',
            '/media/vendor/codemirror/mode',
            '/media/vendor/codemirror/lib',
            '/media/vendor/codemirror/keymap',
            '/media/vendor/codemirror/addon/wrap',
            '/media/vendor/codemirror/addon/tern',
            '/media/vendor/codemirror/addon/selection',
            '/media/vendor/codemirror/addon/search',
            '/media/vendor/codemirror/addon/scroll',
            '/media/vendor/codemirror/addon/runmode',
            '/media/vendor/codemirror/addon/mode',
            '/media/vendor/codemirror/addon/merge',
            '/media/vendor/codemirror/addon/lint',
            '/media/vendor/codemirror/addon/hint',
            '/media/vendor/codemirror/addon/fold',
            '/media/vendor/codemirror/addon/edit',
            '/media/vendor/codemirror/addon/display',
            '/media/vendor/codemirror/addon/dialog',
            '/media/vendor/codemirror/addon/comment',
            '/media/vendor/codemirror/addon',
            // From 5.0.0-alpha3 to 5.0.0-alpha4
            '/templates/system/incompatible.html,/includes',
            '/templates/system/incompatible.html,',
            '/media/plg_system_compat',
            '/media/plg_editors_tinymce/js/plugins/highlighter',
            // From 5.0.0-beta2 to 5.0.0-beta3
            '/libraries/vendor/stella-maris/clock/src',
            '/libraries/vendor/stella-maris/clock',
            '/libraries/vendor/stella-maris',
            '/libraries/vendor/fgrosse/phpasn1/lib/X509/SAN',
            '/libraries/vendor/fgrosse/phpasn1/lib/X509/CSR',
            '/libraries/vendor/fgrosse/phpasn1/lib/X509',
            '/libraries/vendor/fgrosse/phpasn1/lib/Utility',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Universal',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Exception',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1/Composite',
            '/libraries/vendor/fgrosse/phpasn1/lib/ASN1',
            '/libraries/vendor/fgrosse/phpasn1/lib',
            '/libraries/vendor/fgrosse/phpasn1',
            '/libraries/vendor/fgrosse',
            // From 5.1.0-alpha4 to 5.1.0-beta1
            '/media/vendor/joomla-a11y-checker',
            '/media/plg_system_jooa11y/scss',
            '/media/plg_system_jooa11y/css',
            '/libraries/vendor/web-token/jwt-signature/Serializer',
            '/libraries/vendor/web-token/jwt-signature/Algorithm',
            '/libraries/vendor/web-token/jwt-signature-algorithm-rsa/Util',
            '/libraries/vendor/web-token/jwt-signature',
            '/libraries/vendor/web-token/jwt-core/Util',
            '/libraries/vendor/web-token/jwt-core',
            // From 5.1.0-beta1 to 5.1.0-beta2
            '/libraries/vendor/web-token/signature-pack',
            '/libraries/vendor/web-token/jwt-signature-algorithm-rsa',
            '/libraries/vendor/web-token/jwt-signature-algorithm-none',
            '/libraries/vendor/web-token/jwt-signature-algorithm-hmac',
            '/libraries/vendor/web-token/jwt-signature-algorithm-experimental',
            '/libraries/vendor/web-token/jwt-signature-algorithm-eddsa',
            '/libraries/vendor/web-token/jwt-signature-algorithm-ecdsa',
            '/libraries/vendor/web-token/jwt-experimental/Signature',
            '/libraries/vendor/web-token/jwt-experimental/KeyEncryption',
            '/libraries/vendor/web-token/jwt-experimental/ContentEncryption',
            '/libraries/vendor/web-token/jwt-experimental',
            '/libraries/src/Event/Router',
            // From 5.1.0-beta2 to 5.1.0-rc1
            '/media/vendor/punycode',
            // From 5.1.0 to 5.1.1
            '/libraries/vendor/cweagans/composer-patches/tests',
            '/libraries/vendor/cweagans/composer-patches/src',
            '/libraries/vendor/cweagans/composer-patches',
            '/libraries/vendor/cweagans',
            // From 5.2.0-alpha2 to 5.2.0-alpha3
            '/libraries/vendor/maximebf/debugbar/src/DebugBar/Resources/vendor/font-awesome/fonts',
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

        /**
         * Fix filesystem permissions when updating a new 5.2.0 installation.
         *
         * @todo: Remove in 6.0
         */
        $this->fixFilesystemPermissions();

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
        // List all components added since 4.0
        $newComponents = [
            // Components to be added here
            'com_guidedtours',
            'com_mails',
            'com_scheduler',
            'com_workflow',
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

        if (empty($this->fromVersion) || version_compare($this->fromVersion, '5.0.0', 'ge')) {
            return true;
        }

        // Add here code which shall be executed only when updating from an older version than 5.0.0
        if (!$this->migrateTinymceConfiguration()) {
            return false;
        }

        if (!$this->migrateDeleteActionlogsConfiguration()) {
            return false;
        }

        if (!$this->migratePrivacyconsentConfiguration()) {
            return false;
        }

        $this->setGuidedToursUid();

        // Refresh versionable assets cache.
        Factory::getApplication()->flushAssets();

        return true;
    }

    /**
     * Migrate Deleteactionlogs plugin configuration
     *
     * @return  boolean  True on success
     *
     * @since   5.0.0
     */
    private function migrateDeleteActionlogsConfiguration(): bool
    {
        $db = Factory::getDbo();

        try {
            // Get the ActionLogs system plugin's parameters
            $row = $db->setQuery(
                $db->getQuery(true)
                    ->select([$db->quotename('enabled'), $db->quoteName('params')])
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote('actionlogs'))
            )->loadObject();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return false;
        }

        // If not existing or disabled there is nothing to migrate
        if (!$row || !$row->enabled) {
            return true;
        }

        $params = new Registry($row->params);

        // If deletion of outdated logs was disabled there is nothing to migrate
        if (!$params->get('logDeletePeriod', 0)) {
            return true;
        }

        /** @var \Joomla\Component\Scheduler\Administrator\Extension\SchedulerComponent $component */
        $component = Factory::getApplication()->bootComponent('com_scheduler');

        /** @var \Joomla\Component\Scheduler\Administrator\Model\TaskModel $model */
        $model = $component->getMVCFactory()->createModel('Task', 'Administrator', ['ignore_request' => true]);
        $task  = [
            'title'           => 'Delete Action Logs',
            'type'            => 'delete.actionlogs',
            'execution_rules' => [
                'rule-type'      => 'interval-hours',
                'interval-hours' => 24,
                'exec-time'      => gmdate('H:i', $params->get('lastrun', time())),
                'exec-day'       => gmdate('d'),
            ],
            'state'  => 1,
            'params' => [
                'logDeletePeriod' => $params->get('logDeletePeriod', 0),
            ],
        ];

        try {
            $model->save($task);
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return false;
        }

        return true;
    }
    /**
     * Migrate privacyconsents system plugin configuration
     *
     * @return  boolean  True on success
     *
     * @since   5.0.0
     */
    private function migratePrivacyconsentConfiguration(): bool
    {
        $db = Factory::getDbo();

        try {
            // Get the PrivacyConsent system plugin's parameters
            $row = $db->setQuery(
                $db->getQuery(true)
                    ->select([$db->quotename('enabled'), $db->quoteName('params')])
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote('privacyconsent'))
            )->loadObject();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return false;
        }

        // If not existing or disabled there is nothing to migrate
        if (!$row || !$row->enabled) {
            return true;
        }

        $params = new Registry($row->params);

        // If consent expiration was disabled there is nothing to migrate
        if (!$params->get('enabled', 0)) {
            return true;
        }

        /** @var \Joomla\Component\Scheduler\Administrator\Extension\SchedulerComponent $component */
        $component = Factory::getApplication()->bootComponent('com_scheduler');

        /** @var \Joomla\Component\Scheduler\Administrator\Model\TaskModel $model */
        $model = $component->getMVCFactory()->createModel('Task', 'Administrator', ['ignore_request' => true]);
        $task  = [
            'title'           => 'Privacy Consent',
            'type'            => 'privacy.consent',
            'execution_rules' => [
                'rule-type'     => 'interval-days',
                'interval-days' => $params->get('cachetimeout', 30),
                'exec-time'     => gmdate('H:i', $params->get('lastrun', time())),
                'exec-day'      => gmdate('d'),
            ],
            'state'  => 1,
            'params' => [
                'consentexpiration' => $params->get('consentexpiration', 360),
                'remind'            => $params->get('remind', 30),
            ],
        ];

        try {
            $model->save($task);
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return false;
        }

        // Refresh versionable assets cache.
        Factory::getApplication()->flushAssets();

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
            $this->collectError(__METHOD__, $e);

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
                $search  = ['blockformats', 'fontformats', 'fontsizes', 'formats'];
                $replace = ['blocks', 'fontfamily', 'fontsize', 'styles'];

                // Don't redo the template
                if (!\in_array('jtemplate', $params['configuration']['toolbars'][$setIdx]['menu'])) {
                    $search[]  = 'template';
                    $replace[] = 'jtemplate';
                }

                $params['configuration']['toolbars'][$setIdx]['menu'] = str_replace($search, $replace, $toolbarConfig['menu']);
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
                    $search  = ['fontselect', 'fontsizeselect', 'formatselect', 'styleselect'];
                    $replace = ['fontfamily', 'fontsize', 'blocks', 'styles'];

                    // Don't redo the template
                    if (!\in_array('jtemplate', $params['configuration']['toolbars'][$setIdx][$toolbarIdx])) {
                        $search[]  = 'template';
                        $replace[] = 'jtemplate';
                    }

                    $params['configuration']['toolbars'][$setIdx][$toolbarIdx] = str_replace($search, $replace, $toolbarConfig[$toolbarIdx]);
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
            $this->collectError(__METHOD__, $e);

            return false;
        }

        return true;
    }

    /**
     * setup Guided Tours Unique Identifiers
     *
     * @return  void
     *
     * @since   5.0.0
     */
    private function setGuidedToursUid()
    {
        /** @var \Joomla\Component\Cache\Administrator\Model\CacheModel $model */
        $model = Factory::getApplication()->bootComponent('com_guidedtours')->getMVCFactory()
            ->createModel('Tours', 'Administrator', ['ignore_request' => true]);

        $items = $model->getItems();

        foreach ($items as $item) {
            // Set uid for tours where it is empty
            if (empty($item->uid)) {
                $tourItem = $model->getTable('Tour');
                $tourItem->load($item->id);

                // Tour follows Joomla naming convention
                if (str_starts_with($tourItem->title, 'COM_GUIDEDTOURS_TOUR_') && str_ends_with($tourItem->title, '_TITLE')) {
                    $uidTitle = 'joomla_' . str_replace('COM_GUIDEDTOURS_TOUR_', '', $tourItem->title);

                    // Remove the last _TITLE part
                    $pos = strrpos($uidTitle, '_TITLE');
                    if ($pos !== false) {
                        $uidTitle = substr($uidTitle, 0, $pos);
                    }
                } elseif (preg_match('#COM_(\w+)_TOUR_#', $tourItem->title) && str_ends_with($tourItem->title, '_TITLE')) {
                    // Tour follows component naming pattern
                    $uidTitle = preg_replace('#COM_(\w+)_TOUR_#', '$1.', $tourItem->title);

                    // Remove the last _TITLE part
                    $pos = strrpos($uidTitle, "_TITLE");
                    if ($pos !== false) {
                        $uidTitle = substr($uidTitle, 0, $pos);
                    }
                } else {
                    $uri      = Uri::getInstance();
                    $host     = $uri->toString(['host']);
                    $host     = ApplicationHelper::stringURLSafe($host, $tourItem->language);
                    $uidTitle = $host . ' ' . str_replace('COM_GUIDEDTOURS_TOUR_', '', $tourItem->title);
                    // Remove the last _TITLE part
                    if (str_ends_with($uidTitle, '_TITLE')) {
                        $pos      = strrpos($uidTitle, '_TITLE');
                        $uidTitle = substr($uidTitle, 0, $pos);
                    }
                }
                // ApplicationHelper::stringURLSafe will replace a period (.) separator so we split the construction into multiple parts
                $uidTitleParts = explode('.', $uidTitle);
                array_walk($uidTitleParts, function (&$value, $key, $tourLanguage) {
                    $value = ApplicationHelper::stringURLSafe($value, $tourLanguage);
                }, $tourItem->language);
                $tourItem->uid = implode('.', $uidTitleParts);

                $tourItem->store();
            }
        }
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
            // From 5.0.0-alpha3 to 5.0.0-alpha4
            '/plugins/schemaorg/blogposting/src/Extension/Blogposting.php' => '/plugins/schemaorg/blogposting/src/Extension/BlogPosting.php',
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

    /**
     * Fix filesystem permissions when updating a new 5.2.0 installation.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     *
     * @todo    6.0 Remove this method
     *
     * @deprecated  __DEPLOY_VERSION__ will be removed in 6.0 without replacement
     */
    protected function fixFilesystemPermissions()
    {
        // Don't do anything if not updating from a 5.2.0 or 5.2.1
        if (
            empty($this->fromVersion)
            || version_compare($this->fromVersion, '5.2.0', 'lt')
            || version_compare($this->fromVersion, '5.2.1', 'gt')
        ) {
            return;
        }

        // First check tmp folder if it has mode 777
        if (decoct(fileperms(JPATH_ROOT . '/tmp') & 0777) === '777') {
            // We are either on Windows where folders always have 777, or we have to fix permissions
            @chmod(JPATH_ROOT . '/tmp', 0755);
            clearstatcache(true, JPATH_ROOT . '/tmp');
        }

        // Check tmp folder again if it still has mode 777
        if (decoct(fileperms(JPATH_ROOT . '/tmp') & 0777) === '777') {
            // We are on Windows or chmod has no effect
            return;
        }

        try {
            // Using hard-coded string because a new language string would not be available in all cases
            Log::add('Fixing permissions for files and folders.', Log::INFO, 'Update');
        } catch (\RuntimeException $exception) {
            // Informational log only
        }

        $files = [
            '/htaccess.txt',
            '/index.php',
            '/libraries/.htaccess',
            '/libraries/vendor/jfcherng/php-diff/.phpstorm.meta.php',
            '/libraries/vendor/joomla/http/.drone.jsonnet',
            '/libraries/vendor/joomla/http/.drone.yml',
            '/libraries/vendor/joomla/oauth1/.drone.jsonnet',
            '/libraries/vendor/joomla/oauth1/.drone.yml',
            '/libraries/vendor/joomla/oauth2/.drone.jsonnet',
            '/libraries/vendor/joomla/oauth2/.drone.yml',
            '/libraries/vendor/joomla/router/.drone.jsonnet',
            '/libraries/vendor/joomla/router/.drone.yml',
            '/libraries/vendor/joomla/string/.drone.jsonnet',
            '/libraries/vendor/joomla/string/.drone.yml',
            '/libraries/vendor/joomla/uri/.drone.jsonnet',
            '/libraries/vendor/joomla/uri/.drone.yml',
            '/libraries/vendor/joomla/utilities/.drone.jsonnet',
            '/libraries/vendor/joomla/utilities/.drone.yml',
            '/LICENSE.txt',
            '/README.txt',
            '/robots.txt',
            '/robots.txt.dist',
            '/tmp/index.html',
            '/web.config.txt',
        ];

        $folders = [
            '/administrator',
            '/administrator/cache',
            '/administrator/components',
            '/administrator/help',
            '/administrator/help/en-GB',
            '/administrator/includes',
            '/administrator/language',
            '/administrator/language/en-GB',
            '/administrator/language/overrides',
            '/administrator/logs',
            '/administrator/manifests',
            '/administrator/manifests/files',
            '/administrator/manifests/libraries',
            '/administrator/manifests/packages',
            '/administrator/modules',
            '/administrator/templates',
            '/api',
            '/api/components',
            '/api/includes',
            '/api/language',
            '/api/language/en-GB',
            '/cache',
            '/cli',
            '/components',
            '/images',
            '/images/banners',
            '/images/headers',
            '/images/sampledata',
            '/images/sampledata/cassiopeia',
            '/includes',
            '/language',
            '/language/en-GB',
            '/language/overrides',
            '/layouts',
            '/layouts/chromes',
            '/layouts/libraries',
            '/layouts/libraries/html',
            '/layouts/libraries/html/bootstrap',
            '/layouts/libraries/html/bootstrap/modal',
            '/layouts/libraries/html/bootstrap/tab',
            '/libraries',
            '/libraries/php-encryption',
            '/libraries/phpass',
            '/media',
            '/media/cache',
            '/media/templates',
            '/media/templates/administrator',
            '/media/templates/site',
            '/media/vendor',
            '/modules',
            '/plugins',
            '/templates',
        ];

        $foldersRecursive = [
            '/administrator/components/com_actionlogs',
            '/administrator/components/com_admin',
            '/administrator/components/com_ajax',
            '/administrator/components/com_associations',
            '/administrator/components/com_banners',
            '/administrator/components/com_cache',
            '/administrator/components/com_categories',
            '/administrator/components/com_checkin',
            '/administrator/components/com_config',
            '/administrator/components/com_contact',
            '/administrator/components/com_content',
            '/administrator/components/com_contenthistory',
            '/administrator/components/com_cpanel',
            '/administrator/components/com_fields',
            '/administrator/components/com_finder',
            '/administrator/components/com_guidedtours',
            '/administrator/components/com_installer',
            '/administrator/components/com_joomlaupdate',
            '/administrator/components/com_languages',
            '/administrator/components/com_login',
            '/administrator/components/com_mails',
            '/administrator/components/com_media',
            '/administrator/components/com_menus',
            '/administrator/components/com_messages',
            '/administrator/components/com_modules',
            '/administrator/components/com_newsfeeds',
            '/administrator/components/com_plugins',
            '/administrator/components/com_postinstall',
            '/administrator/components/com_privacy',
            '/administrator/components/com_redirect',
            '/administrator/components/com_scheduler',
            '/administrator/components/com_tags',
            '/administrator/components/com_templates',
            '/administrator/components/com_users',
            '/administrator/components/com_workflow',
            '/administrator/components/com_wrapper',
            '/administrator/modules/mod_custom',
            '/administrator/modules/mod_feed',
            '/administrator/modules/mod_frontend',
            '/administrator/modules/mod_guidedtours',
            '/administrator/modules/mod_latest',
            '/administrator/modules/mod_latestactions',
            '/administrator/modules/mod_logged',
            '/administrator/modules/mod_login',
            '/administrator/modules/mod_loginsupport',
            '/administrator/modules/mod_menu',
            '/administrator/modules/mod_messages',
            '/administrator/modules/mod_multilangstatus',
            '/administrator/modules/mod_popular',
            '/administrator/modules/mod_post_installation_messages',
            '/administrator/modules/mod_privacy_dashboard',
            '/administrator/modules/mod_privacy_status',
            '/administrator/modules/mod_quickicon',
            '/administrator/modules/mod_sampledata',
            '/administrator/modules/mod_stats_admin',
            '/administrator/modules/mod_submenu',
            '/administrator/modules/mod_title',
            '/administrator/modules/mod_toolbar',
            '/administrator/modules/mod_user',
            '/administrator/modules/mod_version',
            '/administrator/templates/atum',
            '/administrator/templates/system',
            '/api/components/com_banners',
            '/api/components/com_categories',
            '/api/components/com_config',
            '/api/components/com_contact',
            '/api/components/com_content',
            '/api/components/com_contenthistory',
            '/api/components/com_fields',
            '/api/components/com_installer',
            '/api/components/com_languages',
            '/api/components/com_media',
            '/api/components/com_menus',
            '/api/components/com_messages',
            '/api/components/com_modules',
            '/api/components/com_newsfeeds',
            '/api/components/com_plugins',
            '/api/components/com_privacy',
            '/api/components/com_redirect',
            '/api/components/com_tags',
            '/api/components/com_templates',
            '/api/components/com_users',
            '/components/com_ajax',
            '/components/com_banners',
            '/components/com_config',
            '/components/com_contact',
            '/components/com_content',
            '/components/com_contenthistory',
            '/components/com_fields',
            '/components/com_finder',
            '/components/com_media',
            '/components/com_menus',
            '/components/com_modules',
            '/components/com_newsfeeds',
            '/components/com_privacy',
            '/components/com_tags',
            '/components/com_users',
            '/components/com_wrapper',
            '/layouts/joomla',
            '/layouts/plugins',
            '/libraries/src',
            '/libraries/vendor',
            '/media/com_actionlogs',
            '/media/com_admin',
            '/media/com_associations',
            '/media/com_banners',
            '/media/com_cache',
            '/media/com_categories',
            '/media/com_config',
            '/media/com_contact',
            '/media/com_content',
            '/media/com_contenthistory',
            '/media/com_cpanel',
            '/media/com_fields',
            '/media/com_finder',
            '/media/com_guidedtours',
            '/media/com_installer',
            '/media/com_joomlaupdate',
            '/media/com_languages',
            '/media/com_mails',
            '/media/com_media',
            '/media/com_menus',
            '/media/com_modules',
            '/media/com_scheduler',
            '/media/com_tags',
            '/media/com_templates',
            '/media/com_users',
            '/media/com_workflow',
            '/media/com_wrapper',
            '/media/layouts',
            '/media/legacy',
            '/media/mailto',
            '/media/mod_articles',
            '/media/mod_articles_news',
            '/media/mod_languages',
            '/media/mod_login',
            '/media/mod_menu',
            '/media/mod_quickicon',
            '/media/mod_sampledata',
            '/media/plg_behaviour_compat',
            '/media/plg_captcha_recaptcha',
            '/media/plg_captcha_recaptcha_invisible',
            '/media/plg_content_vote',
            '/media/plg_editors-xtd_image',
            '/media/plg_editors_codemirror',
            '/media/plg_editors_none',
            '/media/plg_editors_tinymce',
            '/media/plg_installer_folderinstaller',
            '/media/plg_installer_packageinstaller',
            '/media/plg_installer_urlinstaller',
            '/media/plg_installer_webinstaller',
            '/media/plg_media-action_crop',
            '/media/plg_media-action_resize',
            '/media/plg_media-action_rotate',
            '/media/plg_multifactorauth_email',
            '/media/plg_multifactorauth_fixed',
            '/media/plg_multifactorauth_totp',
            '/media/plg_multifactorauth_webauthn',
            '/media/plg_multifactorauth_yubikey',
            '/media/plg_quickicon_eos',
            '/media/plg_quickicon_extensionupdate',
            '/media/plg_quickicon_joomlaupdate',
            '/media/plg_quickicon_overridecheck',
            '/media/plg_quickicon_privacycheck',
            '/media/plg_system_debug',
            '/media/plg_system_guidedtours',
            '/media/plg_system_jooa11y',
            '/media/plg_system_schedulerunner',
            '/media/plg_system_shortcut',
            '/media/plg_system_stats',
            '/media/plg_system_webauthn',
            '/media/plg_user_token',
            '/media/system',
            '/media/templates/administrator/atum',
            '/media/templates/site/cassiopeia',
            '/media/vendor/accessibility',
            '/media/vendor/awesomplete',
            '/media/vendor/bootstrap',
            '/media/vendor/choicesjs',
            '/media/vendor/chosen',
            '/media/vendor/codemirror',
            '/media/vendor/cropperjs',
            '/media/vendor/debugbar',
            '/media/vendor/diff',
            '/media/vendor/dragula',
            '/media/vendor/es-module-shims',
            '/media/vendor/focus-visible',
            '/media/vendor/fontawesome-free',
            '/media/vendor/hotkeysjs',
            '/media/vendor/joomla-custom-elements',
            '/media/vendor/jquery',
            '/media/vendor/jquery-migrate',
            '/media/vendor/mediaelement',
            '/media/vendor/metismenujs',
            '/media/vendor/minicolors',
            '/media/vendor/qrcode',
            '/media/vendor/roboto-fontface',
            '/media/vendor/sa11y',
            '/media/vendor/shepherdjs',
            '/media/vendor/short-and-sweet',
            '/media/vendor/skipto',
            '/media/vendor/tinymce',
            '/media/vendor/webcomponentsjs',
            '/modules/mod_articles',
            '/modules/mod_articles_archive',
            '/modules/mod_articles_categories',
            '/modules/mod_articles_category',
            '/modules/mod_articles_latest',
            '/modules/mod_articles_news',
            '/modules/mod_articles_popular',
            '/modules/mod_banners',
            '/modules/mod_breadcrumbs',
            '/modules/mod_custom',
            '/modules/mod_feed',
            '/modules/mod_finder',
            '/modules/mod_footer',
            '/modules/mod_languages',
            '/modules/mod_login',
            '/modules/mod_menu',
            '/modules/mod_random_image',
            '/modules/mod_related_items',
            '/modules/mod_stats',
            '/modules/mod_syndicate',
            '/modules/mod_tags_popular',
            '/modules/mod_tags_similar',
            '/modules/mod_users_latest',
            '/modules/mod_whosonline',
            '/modules/mod_wrapper',
            '/plugins/actionlog',
            '/plugins/api-authentication',
            '/plugins/authentication',
            '/plugins/behaviour',
            '/plugins/captcha',
            '/plugins/content',
            '/plugins/editors',
            '/plugins/editors-xtd',
            '/plugins/extension',
            '/plugins/fields',
            '/plugins/filesystem',
            '/plugins/finder',
            '/plugins/installer',
            '/plugins/media-action',
            '/plugins/multifactorauth',
            '/plugins/privacy',
            '/plugins/quickicon',
            '/plugins/sampledata',
            '/plugins/schemaorg',
            '/plugins/system',
            '/plugins/task',
            '/plugins/user',
            '/plugins/webservices',
            '/plugins/workflow',
            '/templates/cassiopeia',
            '/templates/system',
        ];

        foreach ($files as $file) {
            if (is_file(JPATH_ROOT . $file) && decoct(fileperms(JPATH_ROOT . $file) & 0777) === '777') {
                @chmod(JPATH_ROOT . $file, 0644);
            }
        }

        foreach ($folders as $folder) {
            if (is_dir(JPATH_ROOT . $folder) && decoct(fileperms(JPATH_ROOT . $folder) & 0777) === '777') {
                @chmod(JPATH_ROOT . $folder, 0755);
            }

            foreach (Folder::files(JPATH_ROOT . $folder, '.', false, true) as $file) {
                if (decoct(fileperms($file) & 0777) === '777') {
                    @chmod($file, 0644);
                }
            }
        }

        foreach ($foldersRecursive as $parentFolder) {
            if (is_dir(JPATH_ROOT . $parentFolder)) {
                if (decoct(fileperms(JPATH_ROOT . $parentFolder) & 0777) === '777') {
                    @chmod(JPATH_ROOT . $parentFolder, 0755);
                }

                foreach (Folder::folders(JPATH_ROOT . $parentFolder, '.', true, true) as $folder) {
                    if (decoct(fileperms($folder) & 0777) === '777') {
                        @chmod($folder, 0755);
                    }
                }

                foreach (Folder::files(JPATH_ROOT . $parentFolder, '.', true, true) as $file) {
                    if (decoct(fileperms($file) & 0777) === '777') {
                        @chmod($file, 0644);
                    }
                }
            }
        }
    }
}
