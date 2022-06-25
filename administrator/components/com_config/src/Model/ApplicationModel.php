<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Model;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Cache\Exception\CacheConnectingException;
use Joomla\CMS\Cache\Exception\UnsupportedCacheException;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Table\Asset;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use PHPMailer\PHPMailer\Exception as phpMailerException;

/**
 * Model for the global configuration
 *
 * @since  3.2
 */
class ApplicationModel extends FormModel
{
    /**
     * Array of protected password fields from the configuration.php
     *
     * @var    array
     * @since  3.9.23
     */
    private $protectedConfigurationFields = array('password', 'secret', 'smtppass', 'redis_server_auth', 'session_redis_server_auth');

    /**
     * Method to get a form object.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed  A JForm object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_config.application', 'application', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the configuration data.
     *
     * This method will load the global configuration data straight from
     * JConfig. If configuration data has been saved in the session, that
     * data will be merged into the original data, overwriting it.
     *
     * @return  array  An array containing all global config data.
     *
     * @since   1.6
     */
    public function getData()
    {
        // Get the config data.
        $config = new \JConfig();
        $data   = ArrayHelper::fromObject($config);

        // Get the correct driver at runtime
        $data['dbtype'] = $this->getDatabase()->getName();

        // Prime the asset_id for the rules.
        $data['asset_id'] = 1;

        // Get the text filter data
        $params          = ComponentHelper::getParams('com_config');
        $data['filters'] = ArrayHelper::fromObject($params->get('filters'));

        // If no filter data found, get from com_content (update of 1.6/1.7 site)
        if (empty($data['filters'])) {
            $contentParams = ComponentHelper::getParams('com_content');
            $data['filters'] = ArrayHelper::fromObject($contentParams->get('filters'));
        }

        // Check for data in the session.
        $temp = Factory::getApplication()->getUserState('com_config.config.global.data');

        // Merge in the session data.
        if (!empty($temp)) {
            // $temp can sometimes be an object, and we need it to be an array
            if (is_object($temp)) {
                $temp = ArrayHelper::fromObject($temp);
            }

            $data = array_merge($temp, $data);
        }

        // Correct error_reporting value, since we removed "development", the "maximum" should be set instead
        // @TODO: This can be removed in 5.0
        if (!empty($data['error_reporting']) && $data['error_reporting'] === 'development') {
            $data['error_reporting'] = 'maximum';
        }

        return $data;
    }

    /**
     * Method to validate the db connection properties.
     *
     * @param   array  $data  An array containing all global config data.
     *
     * @return  array|boolean  Array with the validated global config data or boolean false on a validation failure.
     *
     * @since   4.0.0
     */
    public function validateDbConnection($data)
    {
        // Validate database connection encryption options
        if ((int) $data['dbencryption'] === 0) {
            // Reset unused options
            if (!empty($data['dbsslkey'])) {
                $data['dbsslkey'] = '';
            }

            if (!empty($data['dbsslcert'])) {
                $data['dbsslcert'] = '';
            }

            if ((bool) $data['dbsslverifyservercert'] === true) {
                $data['dbsslverifyservercert'] = false;
            }

            if (!empty($data['dbsslca'])) {
                $data['dbsslca'] = '';
            }

            if (!empty($data['dbsslcipher'])) {
                $data['dbsslcipher'] = '';
            }
        } else {
            // Check localhost
            if (strtolower($data['host']) === 'localhost') {
                Factory::getApplication()->enqueueMessage(Text::_('COM_CONFIG_ERROR_DATABASE_ENCRYPTION_LOCALHOST'), 'error');

                return false;
            }

            // Check CA file and folder depending on database type if server certificate verification
            if ((bool) $data['dbsslverifyservercert'] === true) {
                if (empty($data['dbsslca'])) {
                    Factory::getApplication()->enqueueMessage(
                        Text::sprintf(
                            'COM_CONFIG_ERROR_DATABASE_ENCRYPTION_FILE_FIELD_EMPTY',
                            Text::_('COM_CONFIG_FIELD_DATABASE_ENCRYPTION_CA_LABEL')
                        ),
                        'error'
                    );

                    return false;
                }

                if (!File::exists(Path::clean($data['dbsslca']))) {
                    Factory::getApplication()->enqueueMessage(
                        Text::sprintf(
                            'COM_CONFIG_ERROR_DATABASE_ENCRYPTION_FILE_FIELD_BAD',
                            Text::_('COM_CONFIG_FIELD_DATABASE_ENCRYPTION_CA_LABEL')
                        ),
                        'error'
                    );

                    return false;
                }
            } else {
                // Reset unused option
                if (!empty($data['dbsslca'])) {
                    $data['dbsslca'] = '';
                }
            }

            // Check key and certificate if two-way encryption
            if ((int) $data['dbencryption'] === 2) {
                if (empty($data['dbsslkey'])) {
                    Factory::getApplication()->enqueueMessage(
                        Text::sprintf(
                            'COM_CONFIG_ERROR_DATABASE_ENCRYPTION_FILE_FIELD_EMPTY',
                            Text::_('COM_CONFIG_FIELD_DATABASE_ENCRYPTION_KEY_LABEL')
                        ),
                        'error'
                    );

                    return false;
                }

                if (!File::exists(Path::clean($data['dbsslkey']))) {
                    Factory::getApplication()->enqueueMessage(
                        Text::sprintf(
                            'COM_CONFIG_ERROR_DATABASE_ENCRYPTION_FILE_FIELD_BAD',
                            Text::_('COM_CONFIG_FIELD_DATABASE_ENCRYPTION_KEY_LABEL')
                        ),
                        'error'
                    );

                    return false;
                }

                if (empty($data['dbsslcert'])) {
                    Factory::getApplication()->enqueueMessage(
                        Text::sprintf(
                            'COM_CONFIG_ERROR_DATABASE_ENCRYPTION_FILE_FIELD_EMPTY',
                            Text::_('COM_CONFIG_FIELD_DATABASE_ENCRYPTION_CERT_LABEL')
                        ),
                        'error'
                    );

                    return false;
                }

                if (!File::exists(Path::clean($data['dbsslcert']))) {
                    Factory::getApplication()->enqueueMessage(
                        Text::sprintf(
                            'COM_CONFIG_ERROR_DATABASE_ENCRYPTION_FILE_FIELD_BAD',
                            Text::_('COM_CONFIG_FIELD_DATABASE_ENCRYPTION_CERT_LABEL')
                        ),
                        'error'
                    );

                    return false;
                }
            } else {
                // Reset unused options
                if (!empty($data['dbsslkey'])) {
                    $data['dbsslkey'] = '';
                }

                if (!empty($data['dbsslcert'])) {
                    $data['dbsslcert'] = '';
                }
            }
        }

        return $data;
    }

    /**
     * Method to save the configuration data.
     *
     * @param   array  $data  An array containing all global config data.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.6
     */
    public function save($data)
    {
        $app = Factory::getApplication();

        // Try to load the values from the configuration file
        foreach ($this->protectedConfigurationFields as $fieldKey) {
            if (!isset($data[$fieldKey])) {
                $data[$fieldKey] = $app->get($fieldKey, '');
            }
        }

        // Check that we aren't setting wrong database configuration
        $options = array(
            'driver'   => $data['dbtype'],
            'host'     => $data['host'],
            'user'     => $data['user'],
            'password' => $data['password'],
            'database' => $data['db'],
            'prefix'   => $data['dbprefix'],
        );

        if ((int) $data['dbencryption'] !== 0) {
            $options['ssl'] = [
                'enable'             => true,
                'verify_server_cert' => (bool) $data['dbsslverifyservercert'],
            ];

            foreach (['cipher', 'ca', 'key', 'cert'] as $value) {
                $confVal = trim($data['dbssl' . $value]);

                if ($confVal !== '') {
                    $options['ssl'][$value] = $confVal;
                }
            }
        }

        try {
            $revisedDbo = DatabaseDriver::getInstance($options);
            $revisedDbo->getVersion();
        } catch (\Exception $e) {
            $app->enqueueMessage(Text::sprintf('COM_CONFIG_ERROR_DATABASE_NOT_AVAILABLE', $e->getCode(), $e->getMessage()), 'error');

            return false;
        }

        if ((int) $data['dbencryption'] !== 0 && empty($revisedDbo->getConnectionEncryption())) {
            if ($revisedDbo->isConnectionEncryptionSupported()) {
                Factory::getApplication()->enqueueMessage(Text::_('COM_CONFIG_ERROR_DATABASE_ENCRYPTION_CONN_NOT_ENCRYPT'), 'error');
            } else {
                Factory::getApplication()->enqueueMessage(Text::_('COM_CONFIG_ERROR_DATABASE_ENCRYPTION_SRV_NOT_SUPPORTS'), 'error');
            }

            return false;
        }

        // Check if we can set the Force SSL option
        if ((int) $data['force_ssl'] !== 0 && (int) $data['force_ssl'] !== (int) $app->get('force_ssl', '0')) {
            try {
                // Make an HTTPS request to check if the site is available in HTTPS.
                $host    = Uri::getInstance()->getHost();
                $options = new Registry();
                $options->set('userAgent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0');

                // Do not check for valid server certificate here, leave this to the user, moreover disable using a proxy if any is configured.
                $options->set(
                    'transport.curl',
                    array(
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                        CURLOPT_PROXY => null,
                        CURLOPT_PROXYUSERPWD => null,
                    )
                );
                $response = HttpFactory::getHttp($options)->get('https://' . $host . Uri::root(true) . '/', array('Host' => $host), 10);

                // If available in HTTPS check also the status code.
                if (!in_array($response->code, array(200, 503, 301, 302, 303, 304, 305, 306, 307, 308, 309, 310, 401), true)) {
                    throw new \RuntimeException(Text::_('COM_CONFIG_ERROR_SSL_NOT_AVAILABLE_HTTP_CODE'));
                }
            } catch (\RuntimeException $e) {
                $data['force_ssl'] = 0;

                // Also update the user state
                $app->setUserState('com_config.config.global.data.force_ssl', 0);

                // Inform the user
                $app->enqueueMessage(Text::sprintf('COM_CONFIG_ERROR_SSL_NOT_AVAILABLE', $e->getMessage()), 'warning');
            }
        }

        // Save the rules
        if (isset($data['rules'])) {
            $rules = new Rules($data['rules']);

            // Check that we aren't removing our Super User permission
            // Need to get groups from database, since they might have changed
            $myGroups      = Access::getGroupsByUser(Factory::getUser()->get('id'));
            $myRules       = $rules->getData();
            $hasSuperAdmin = $myRules['core.admin']->allow($myGroups);

            if (!$hasSuperAdmin) {
                $app->enqueueMessage(Text::_('COM_CONFIG_ERROR_REMOVING_SUPER_ADMIN'), 'error');

                return false;
            }

            $asset = Table::getInstance('asset');

            if ($asset->loadByName('root.1')) {
                $asset->rules = (string) $rules;

                if (!$asset->check() || !$asset->store()) {
                    $app->enqueueMessage($asset->getError(), 'error');

                    return false;
                }
            } else {
                $app->enqueueMessage(Text::_('COM_CONFIG_ERROR_ROOT_ASSET_NOT_FOUND'), 'error');

                return false;
            }

            unset($data['rules']);
        }

        // Save the text filters
        if (isset($data['filters'])) {
            $registry = new Registry(array('filters' => $data['filters']));

            $extension = Table::getInstance('extension');

            // Get extension_id
            $extensionId = $extension->find(array('name' => 'com_config'));

            if ($extension->load((int) $extensionId)) {
                $extension->params = (string) $registry;

                if (!$extension->check() || !$extension->store()) {
                    $app->enqueueMessage($extension->getError(), 'error');

                    return false;
                }
            } else {
                $app->enqueueMessage(Text::_('COM_CONFIG_ERROR_CONFIG_EXTENSION_NOT_FOUND'), 'error');

                return false;
            }

            unset($data['filters']);
        }

        // Get the previous configuration.
        $prev = new \JConfig();
        $prev = ArrayHelper::fromObject($prev);

        // Merge the new data in. We do this to preserve values that were not in the form.
        $data = array_merge($prev, $data);

        /*
         * Perform miscellaneous options based on configuration settings/changes.
         */

        // Escape the offline message if present.
        if (isset($data['offline_message'])) {
            $data['offline_message'] = OutputFilter::ampReplace($data['offline_message']);
        }

        // Purge the database session table if we are changing to the database handler.
        if ($prev['session_handler'] != 'database' && $data['session_handler'] == 'database') {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__session'))
                ->where($db->quoteName('time') . ' < ' . (time() - 1));
            $db->setQuery($query);
            $db->execute();
        }

        // Purge the database session table if we are disabling session metadata
        if ($prev['session_metadata'] == 1 && $data['session_metadata'] == 0) {
            try {
                // If we are are using the session handler, purge the extra columns, otherwise truncate the whole session table
                if ($data['session_handler'] === 'database') {
                    $revisedDbo->setQuery(
                        $revisedDbo->getQuery(true)
                            ->update('#__session')
                            ->set(
                                [
                                    $revisedDbo->quoteName('client_id') . ' = 0',
                                    $revisedDbo->quoteName('guest') . ' = NULL',
                                    $revisedDbo->quoteName('userid') . ' = NULL',
                                    $revisedDbo->quoteName('username') . ' = NULL',
                                ]
                            )
                    )->execute();
                } else {
                    $revisedDbo->truncateTable('#__session');
                }
            } catch (\RuntimeException $e) {
                /*
                 * The database API logs errors on failures so we don't need to add any error handling mechanisms here.
                 * Also, this data won't be added or checked anymore once the configuration is saved, so it'll purge itself
                 * through normal garbage collection anyway or if not using the database handler someone can purge the
                 * table on their own.  Either way, carry on Soldier!
                 */
            }
        }

        // Ensure custom session file path exists or try to create it if changed
        if (!empty($data['session_filesystem_path'])) {
            $currentPath = $prev['session_filesystem_path'] ?? null;

            if ($currentPath) {
                $currentPath = Path::clean($currentPath);
            }

            $data['session_filesystem_path'] = Path::clean($data['session_filesystem_path']);

            if ($currentPath !== $data['session_filesystem_path']) {
                if (!Folder::exists($data['session_filesystem_path']) && !Folder::create($data['session_filesystem_path'])) {
                    try {
                        Log::add(
                            Text::sprintf(
                                'COM_CONFIG_ERROR_CUSTOM_SESSION_FILESYSTEM_PATH_NOTWRITABLE_USING_DEFAULT',
                                $data['session_filesystem_path']
                            ),
                            Log::WARNING,
                            'jerror'
                        );
                    } catch (\RuntimeException $logException) {
                        $app->enqueueMessage(
                            Text::sprintf(
                                'COM_CONFIG_ERROR_CUSTOM_SESSION_FILESYSTEM_PATH_NOTWRITABLE_USING_DEFAULT',
                                $data['session_filesystem_path']
                            ),
                            'warning'
                        );
                    }

                    $data['session_filesystem_path'] = $currentPath;
                }
            }
        }

        // Set the shared session configuration
        if (isset($data['shared_session'])) {
            $currentShared = $prev['shared_session'] ?? '0';

            // Has the user enabled shared sessions?
            if ($data['shared_session'] == 1 && $currentShared == 0) {
                // Generate a random shared session name
                $data['session_name'] = UserHelper::genRandomPassword(16);
            }

            // Has the user disabled shared sessions?
            if ($data['shared_session'] == 0 && $currentShared == 1) {
                // Remove the session name value
                unset($data['session_name']);
            }
        }

        // Set the shared session configuration
        if (isset($data['shared_session'])) {
            $currentShared = $prev['shared_session'] ?? '0';

            // Has the user enabled shared sessions?
            if ($data['shared_session'] == 1 && $currentShared == 0) {
                // Generate a random shared session name
                $data['session_name'] = UserHelper::genRandomPassword(16);
            }

            // Has the user disabled shared sessions?
            if ($data['shared_session'] == 0 && $currentShared == 1) {
                // Remove the session name value
                unset($data['session_name']);
            }
        }

        if (empty($data['cache_handler'])) {
            $data['caching'] = 0;
        }

        /*
         * Look for a custom cache_path
         * First check if a path is given in the submitted data, then check if a path exists in the previous data, otherwise use the default
         */
        if (!empty($data['cache_path'])) {
            $path = $data['cache_path'];
        } elseif (!empty($prev['cache_path'])) {
            $path = $prev['cache_path'];
        } else {
            $path = JPATH_CACHE;
        }

        // Give a warning if the cache-folder can not be opened
        if ($data['caching'] > 0 && $data['cache_handler'] == 'file' && @opendir($path) == false) {
            $error = true;

            // If a custom path is in use, try using the system default instead of disabling cache
            if ($path !== JPATH_CACHE && @opendir(JPATH_CACHE) != false) {
                try {
                    Log::add(
                        Text::sprintf('COM_CONFIG_ERROR_CUSTOM_CACHE_PATH_NOTWRITABLE_USING_DEFAULT', $path, JPATH_CACHE),
                        Log::WARNING,
                        'jerror'
                    );
                } catch (\RuntimeException $logException) {
                    $app->enqueueMessage(
                        Text::sprintf('COM_CONFIG_ERROR_CUSTOM_CACHE_PATH_NOTWRITABLE_USING_DEFAULT', $path, JPATH_CACHE),
                        'warning'
                    );
                }

                $path  = JPATH_CACHE;
                $error = false;

                $data['cache_path'] = '';
            }

            if ($error) {
                try {
                    Log::add(Text::sprintf('COM_CONFIG_ERROR_CACHE_PATH_NOTWRITABLE', $path), Log::WARNING, 'jerror');
                } catch (\RuntimeException $exception) {
                    $app->enqueueMessage(Text::sprintf('COM_CONFIG_ERROR_CACHE_PATH_NOTWRITABLE', $path), 'warning');
                }

                $data['caching'] = 0;
            }
        }

        // Did the user remove their custom cache path?  Don't save the variable to the config
        if (empty($data['cache_path'])) {
            unset($data['cache_path']);
        }

        // Clean the cache if disabled but previously enabled or changing cache handlers; these operations use the `$prev` data already in memory
        if ((!$data['caching'] && $prev['caching']) || $data['cache_handler'] !== $prev['cache_handler']) {
            try {
                Factory::getCache()->clean();
            } catch (CacheConnectingException $exception) {
                try {
                    Log::add(Text::_('COM_CONFIG_ERROR_CACHE_CONNECTION_FAILED'), Log::WARNING, 'jerror');
                } catch (\RuntimeException $logException) {
                    $app->enqueueMessage(Text::_('COM_CONFIG_ERROR_CACHE_CONNECTION_FAILED'), 'warning');
                }
            } catch (UnsupportedCacheException $exception) {
                try {
                    Log::add(Text::_('COM_CONFIG_ERROR_CACHE_DRIVER_UNSUPPORTED'), Log::WARNING, 'jerror');
                } catch (\RuntimeException $logException) {
                    $app->enqueueMessage(Text::_('COM_CONFIG_ERROR_CACHE_DRIVER_UNSUPPORTED'), 'warning');
                }
            }
        }

        /*
         * Look for a custom tmp_path
         * First check if a path is given in the submitted data, then check if a path exists in the previous data, otherwise use the default
         */
        $defaultTmpPath = JPATH_ROOT . '/tmp';

        if (!empty($data['tmp_path'])) {
            $path = $data['tmp_path'];
        } elseif (!empty($prev['tmp_path'])) {
            $path = $prev['tmp_path'];
        } else {
            $path = $defaultTmpPath;
        }

        $path = Path::clean($path);

        // Give a warning if the tmp-folder is not valid or not writable
        if (!is_dir($path) || !is_writable($path)) {
            $error = true;

            // If a custom path is in use, try using the system default tmp path
            if ($path !== $defaultTmpPath && is_dir($defaultTmpPath) && is_writable($defaultTmpPath)) {
                try {
                    Log::add(
                        Text::sprintf('COM_CONFIG_ERROR_CUSTOM_TEMP_PATH_NOTWRITABLE_USING_DEFAULT', $path, $defaultTmpPath),
                        Log::WARNING,
                        'jerror'
                    );
                } catch (\RuntimeException $logException) {
                    $app->enqueueMessage(
                        Text::sprintf('COM_CONFIG_ERROR_CUSTOM_TEMP_PATH_NOTWRITABLE_USING_DEFAULT', $path, $defaultTmpPath),
                        'warning'
                    );
                }

                $error = false;

                $data['tmp_path'] = $defaultTmpPath;
            }

            if ($error) {
                try {
                    Log::add(Text::sprintf('COM_CONFIG_ERROR_TMP_PATH_NOTWRITABLE', $path), Log::WARNING, 'jerror');
                } catch (\RuntimeException $exception) {
                    $app->enqueueMessage(Text::sprintf('COM_CONFIG_ERROR_TMP_PATH_NOTWRITABLE', $path), 'warning');
                }
            }
        }

        /*
         * Look for a custom log_path
         * First check if a path is given in the submitted data, then check if a path exists in the previous data, otherwise use the default
         */
        $defaultLogPath = JPATH_ADMINISTRATOR . '/logs';

        if (!empty($data['log_path'])) {
            $path = $data['log_path'];
        } elseif (!empty($prev['log_path'])) {
            $path = $prev['log_path'];
        } else {
            $path = $defaultLogPath;
        }

        $path = Path::clean($path);

        // Give a warning if the log-folder is not valid or not writable
        if (!is_dir($path) || !is_writable($path)) {
            $error = true;

            // If a custom path is in use, try using the system default log path
            if ($path !== $defaultLogPath && is_dir($defaultLogPath) && is_writable($defaultLogPath)) {
                try {
                    Log::add(
                        Text::sprintf('COM_CONFIG_ERROR_CUSTOM_LOG_PATH_NOTWRITABLE_USING_DEFAULT', $path, $defaultLogPath),
                        Log::WARNING,
                        'jerror'
                    );
                } catch (\RuntimeException $logException) {
                    $app->enqueueMessage(
                        Text::sprintf('COM_CONFIG_ERROR_CUSTOM_LOG_PATH_NOTWRITABLE_USING_DEFAULT', $path, $defaultLogPath),
                        'warning'
                    );
                }

                $error = false;
                $data['log_path'] = $defaultLogPath;
            }

            if ($error) {
                try {
                    Log::add(Text::sprintf('COM_CONFIG_ERROR_LOG_PATH_NOTWRITABLE', $path), Log::WARNING, 'jerror');
                } catch (\RuntimeException $exception) {
                    $app->enqueueMessage(Text::sprintf('COM_CONFIG_ERROR_LOG_PATH_NOTWRITABLE', $path), 'warning');
                }
            }
        }

        // Create the new configuration object.
        $config = new Registry($data);

        // Overwrite webservices cors settings
        $app->set('cors', $data['cors']);
        $app->set('cors_allow_origin', $data['cors_allow_origin']);
        $app->set('cors_allow_headers', $data['cors_allow_headers']);
        $app->set('cors_allow_methods', $data['cors_allow_methods']);

        // Clear cache of com_config component.
        $this->cleanCache('_system');

        $result = $app->triggerEvent('onApplicationBeforeSave', array($config));

        // Store the data.
        if (in_array(false, $result, true)) {
            throw new \RuntimeException(Text::_('COM_CONFIG_ERROR_UNKNOWN_BEFORE_SAVING'));
        }

        // Write the configuration file.
        $result = $this->writeConfigFile($config);

        // Trigger the after save event.
        $app->triggerEvent('onApplicationAfterSave', array($config));

        return $result;
    }

    /**
     * Method to unset the root_user value from configuration data.
     *
     * This method will load the global configuration data straight from
     * JConfig and remove the root_user value for security, then save the configuration.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.6
     */
    public function removeroot()
    {
        $app = Factory::getApplication();

        // Get the previous configuration.
        $prev = new \JConfig();
        $prev = ArrayHelper::fromObject($prev);

        // Create the new configuration object, and unset the root_user property
        unset($prev['root_user']);
        $config = new Registry($prev);

        $result = $app->triggerEvent('onApplicationBeforeSave', array($config));

        // Store the data.
        if (in_array(false, $result, true)) {
            throw new \RuntimeException(Text::_('COM_CONFIG_ERROR_UNKNOWN_BEFORE_SAVING'));
        }

        // Write the configuration file.
        $result = $this->writeConfigFile($config);

        // Trigger the after save event.
        $app->triggerEvent('onApplicationAfterSave', array($config));

        return $result;
    }

    /**
     * Method to write the configuration to a file.
     *
     * @param   Registry  $config  A Registry object containing all global config data.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   2.5.4
     * @throws  \RuntimeException
     */
    private function writeConfigFile(Registry $config)
    {
        // Set the configuration file path.
        $file = JPATH_CONFIGURATION . '/configuration.php';

        $app = Factory::getApplication();

        // Attempt to make the file writeable.
        if (Path::isOwner($file) && !Path::setPermissions($file, '0644')) {
            $app->enqueueMessage(Text::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTWRITABLE'), 'notice');
        }

        // Attempt to write the configuration file as a PHP class named JConfig.
        $configuration = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));

        if (!File::write($file, $configuration)) {
            throw new \RuntimeException(Text::_('COM_CONFIG_ERROR_WRITE_FAILED'));
        }

        // Attempt to make the file unwriteable.
        if (Path::isOwner($file) && !Path::setPermissions($file, '0444')) {
            $app->enqueueMessage(Text::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTUNWRITABLE'), 'notice');
        }

        return true;
    }

    /**
     * Method to store the permission values in the asset table.
     *
     * This method will get an array with permission key value pairs and transform it
     * into json and update the asset table in the database.
     *
     * @param   string  $permission  Need an array with Permissions (component, rule, value and title)
     *
     * @return  array|bool  A list of result data or false on failure.
     *
     * @since   3.5
     */
    public function storePermissions($permission = null)
    {
        $app  = Factory::getApplication();
        $user = Factory::getUser();

        if (is_null($permission)) {
            // Get data from input.
            $permission = array(
                'component' => $app->input->Json->get('comp'),
                'action'    => $app->input->Json->get('action'),
                'rule'      => $app->input->Json->get('rule'),
                'value'     => $app->input->Json->get('value'),
                'title'     => $app->input->Json->get('title', '', 'RAW')
            );
        }

        // We are creating a new item so we don't have an item id so don't allow.
        if (substr($permission['component'], -6) === '.false') {
            $app->enqueueMessage(Text::_('JLIB_RULES_SAVE_BEFORE_CHANGE_PERMISSIONS'), 'error');

            return false;
        }

        // Check if the user is authorized to do this.
        if (!$user->authorise('core.admin', $permission['component'])) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

            return false;
        }

        $permission['component'] = empty($permission['component']) ? 'root.1' : $permission['component'];

        // Current view is global config?
        $isGlobalConfig = $permission['component'] === 'root.1';

        // Check if changed group has Super User permissions.
        $isSuperUserGroupBefore = Access::checkGroup($permission['rule'], 'core.admin');

        // Check if current user belongs to changed group.
        $currentUserBelongsToGroup = in_array((int) $permission['rule'], $user->groups) ? true : false;

        // Get current user groups tree.
        $currentUserGroupsTree = Access::getGroupsByUser($user->id, true);

        // Check if current user belongs to changed group.
        $currentUserSuperUser = $user->authorise('core.admin');

        // If user is not Super User cannot change the permissions of a group it belongs to.
        if (!$currentUserSuperUser && $currentUserBelongsToGroup) {
            $app->enqueueMessage(Text::_('JLIB_USER_ERROR_CANNOT_CHANGE_OWN_GROUPS'), 'error');

            return false;
        }

        // If user is not Super User cannot change the permissions of a group it belongs to.
        if (!$currentUserSuperUser && in_array((int) $permission['rule'], $currentUserGroupsTree)) {
            $app->enqueueMessage(Text::_('JLIB_USER_ERROR_CANNOT_CHANGE_OWN_PARENT_GROUPS'), 'error');

            return false;
        }

        // If user is not Super User cannot change the permissions of a Super User Group.
        if (!$currentUserSuperUser && $isSuperUserGroupBefore && !$currentUserBelongsToGroup) {
            $app->enqueueMessage(Text::_('JLIB_USER_ERROR_CANNOT_CHANGE_SUPER_USER'), 'error');

            return false;
        }

        // If user is not Super User cannot change the Super User permissions in any group it belongs to.
        if ($isSuperUserGroupBefore && $currentUserBelongsToGroup && $permission['action'] === 'core.admin') {
            $app->enqueueMessage(Text::_('JLIB_USER_ERROR_CANNOT_DEMOTE_SELF'), 'error');

            return false;
        }

        try {
            /** @var Asset $asset */
            $asset  = Table::getInstance('asset');
            $result = $asset->loadByName($permission['component']);

            if ($result === false) {
                $data = array($permission['action'] => array($permission['rule'] => $permission['value']));

                $rules        = new Rules($data);
                $asset->rules = (string) $rules;
                $asset->name  = (string) $permission['component'];
                $asset->title = (string) $permission['title'];

                // Get the parent asset id so we have a correct tree.
                /** @var Asset $parentAsset */
                $parentAsset = Table::getInstance('Asset');

                if (strpos($asset->name, '.') !== false) {
                    $assetParts = explode('.', $asset->name);
                    $parentAsset->loadByName($assetParts[0]);
                    $parentAssetId = $parentAsset->id;
                } else {
                    $parentAssetId = $parentAsset->getRootId();
                }

                /**
                 * @todo: incorrect ACL stored
                 * When changing a permission of an item that doesn't have a row in the asset table the row a new row is created.
                 * This works fine for item <-> component <-> global config scenario and component <-> global config scenario.
                 * But doesn't work properly for item <-> section(s) <-> component <-> global config scenario,
                 * because a wrong parent asset id (the component) is stored.
                 * Happens when there is no row in the asset table (ex: deleted or not created on update).
                 */

                $asset->setLocation($parentAssetId, 'last-child');
            } else {
                // Decode the rule settings.
                $temp = json_decode($asset->rules, true);

                // Check if a new value is to be set.
                if (isset($permission['value'])) {
                    // Check if we already have an action entry.
                    if (!isset($temp[$permission['action']])) {
                        $temp[$permission['action']] = array();
                    }

                    // Check if we already have a rule entry.
                    if (!isset($temp[$permission['action']][$permission['rule']])) {
                        $temp[$permission['action']][$permission['rule']] = array();
                    }

                    // Set the new permission.
                    $temp[$permission['action']][$permission['rule']] = (int) $permission['value'];

                    // Check if we have an inherited setting.
                    if ($permission['value'] === '') {
                        unset($temp[$permission['action']][$permission['rule']]);
                    }

                    // Check if we have any rules.
                    if (!$temp[$permission['action']]) {
                        unset($temp[$permission['action']]);
                    }
                } else {
                    // There is no value so remove the action as it's not needed.
                    unset($temp[$permission['action']]);
                }

                $asset->rules = json_encode($temp, JSON_FORCE_OBJECT);
            }

            if (!$asset->check() || !$asset->store()) {
                $app->enqueueMessage(Text::_('JLIB_UNKNOWN'), 'error');

                return false;
            }
        } catch (\Exception $e) {
            $app->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        // All checks done.
        $result = array(
            'text'    => '',
            'class'   => '',
            'result'  => true,
        );

        // Show the current effective calculated permission considering current group, path and cascade.

        try {
            // The database instance
            $db = $this->getDatabase();

            // Get the asset id by the name of the component.
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__assets'))
                ->where($db->quoteName('name') . ' = :component')
                ->bind(':component', $permission['component']);

            $db->setQuery($query);

            $assetId = (int) $db->loadResult();

            // Fetch the parent asset id.
            $parentAssetId = null;

            /**
             * @todo: incorrect info
             * When creating a new item (not saving) it uses the calculated permissions from the component (item <-> component <-> global config).
             * But if we have a section too (item <-> section(s) <-> component <-> global config) this is not correct.
             * Also, currently it uses the component permission, but should use the calculated permissions for a child of the component/section.
             */

            // If not in global config we need the parent_id asset to calculate permissions.
            if (!$isGlobalConfig) {
                // In this case we need to get the component rules too.
                $query->clear()
                    ->select($db->quoteName('parent_id'))
                    ->from($db->quoteName('#__assets'))
                    ->where($db->quoteName('id') . ' = :assetid')
                    ->bind(':assetid', $assetId, ParameterType::INTEGER);

                $db->setQuery($query);

                $parentAssetId = (int) $db->loadResult();
            }

            // Get the group parent id of the current group.
            $rule = (int) $permission['rule'];
            $query->clear()
                ->select($db->quoteName('parent_id'))
                ->from($db->quoteName('#__usergroups'))
                ->where($db->quoteName('id') . ' = :rule')
                ->bind(':rule', $rule, ParameterType::INTEGER);

            $db->setQuery($query);

            $parentGroupId = (int) $db->loadResult();

            // Count the number of child groups of the current group.
            $query->clear()
                ->select('COUNT(' . $db->quoteName('id') . ')')
                ->from($db->quoteName('#__usergroups'))
                ->where($db->quoteName('parent_id') . ' = :rule')
                ->bind(':rule', $rule, ParameterType::INTEGER);

            $db->setQuery($query);

            $totalChildGroups = (int) $db->loadResult();
        } catch (\Exception $e) {
            $app->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        // Clear access statistics.
        Access::clearStatics();

        // After current group permission is changed we need to check again if the group has Super User permissions.
        $isSuperUserGroupAfter = Access::checkGroup($permission['rule'], 'core.admin');

        // Get the rule for just this asset (non-recursive) and get the actual setting for the action for this group.
        $assetRule = Access::getAssetRules($assetId, false, false)->allow($permission['action'], $permission['rule']);

        // Get the group, group parent id, and group global config recursive calculated permission for the chosen action.
        $inheritedGroupRule = Access::checkGroup($permission['rule'], $permission['action'], $assetId);

        if (!empty($parentAssetId)) {
            $inheritedGroupParentAssetRule = Access::checkGroup($permission['rule'], $permission['action'], $parentAssetId);
        } else {
            $inheritedGroupParentAssetRule = null;
        }

        $inheritedParentGroupRule = !empty($parentGroupId) ? Access::checkGroup($parentGroupId, $permission['action'], $assetId) : null;

        // Current group is a Super User group, so calculated setting is "Allowed (Super User)".
        if ($isSuperUserGroupAfter) {
            $result['class'] = 'badge bg-success';
            $result['text'] = '<span class="icon-lock icon-white" aria-hidden="true"></span>' . Text::_('JLIB_RULES_ALLOWED_ADMIN');
        } else {
            // Not super user.
            // First get the real recursive calculated setting and add (Inherited) to it.

            // If recursive calculated setting is "Denied" or null. Calculated permission is "Not Allowed (Inherited)".
            if ($inheritedGroupRule === null || $inheritedGroupRule === false) {
                $result['class'] = 'badge bg-danger';
                $result['text']  = Text::_('JLIB_RULES_NOT_ALLOWED_INHERITED');
            } else {
                // If recursive calculated setting is "Allowed". Calculated permission is "Allowed (Inherited)".
                $result['class'] = 'badge bg-success';
                $result['text']  = Text::_('JLIB_RULES_ALLOWED_INHERITED');
            }

            // Second part: Overwrite the calculated permissions labels if there is an explicit permission in the current group.

            /**
             * @todo: incorrect info
             * If a component has a permission that doesn't exists in global config (ex: frontend editing in com_modules) by default
             * we get "Not Allowed (Inherited)" when we should get "Not Allowed (Default)".
             */

            // If there is an explicit permission "Not Allowed". Calculated permission is "Not Allowed".
            if ($assetRule === false) {
                $result['class'] = 'badge bg-danger';
                $result['text']  = Text::_('JLIB_RULES_NOT_ALLOWED');
            } elseif ($assetRule === true) {
                // If there is an explicit permission is "Allowed". Calculated permission is "Allowed".
                $result['class'] = 'badge bg-success';
                $result['text']  = Text::_('JLIB_RULES_ALLOWED');
            }

            // Third part: Overwrite the calculated permissions labels for special cases.

            // Global configuration with "Not Set" permission. Calculated permission is "Not Allowed (Default)".
            if (empty($parentGroupId) && $isGlobalConfig === true && $assetRule === null) {
                $result['class'] = 'badge bg-danger';
                $result['text']  = Text::_('JLIB_RULES_NOT_ALLOWED_DEFAULT');
            } elseif ($inheritedGroupParentAssetRule === false || $inheritedParentGroupRule === false) {
                /**
                 * Component/Item with explicit "Denied" permission at parent Asset (Category, Component or Global config) configuration.
                 * Or some parent group has an explicit "Denied".
                 * Calculated permission is "Not Allowed (Locked)".
                 */
                $result['class'] = 'badge bg-danger';
                $result['text']  = '<span class="icon-lock icon-white" aria-hidden="true"></span>' . Text::_('JLIB_RULES_NOT_ALLOWED_LOCKED');
            }
        }

        // If removed or added super user from group, we need to refresh the page to recalculate all settings.
        if ($isSuperUserGroupBefore != $isSuperUserGroupAfter) {
            $app->enqueueMessage(Text::_('JLIB_RULES_NOTICE_RECALCULATE_GROUP_PERMISSIONS'), 'notice');
        }

        // If this group has child groups, we need to refresh the page to recalculate the child settings.
        if ($totalChildGroups > 0) {
            $app->enqueueMessage(Text::_('JLIB_RULES_NOTICE_RECALCULATE_GROUP_CHILDS_PERMISSIONS'), 'notice');
        }

        return $result;
    }

    /**
     * Method to send a test mail which is called via an AJAX request
     *
     * @return   boolean
     *
     * @since  3.5
     */
    public function sendTestMail()
    {
        // Set the new values to test with the current settings
        $app = Factory::getApplication();
        $user = Factory::getUser();
        $input = $app->input->json;
        $smtppass = $input->get('smtppass', null, 'RAW');

        $app->set('smtpauth', $input->get('smtpauth'));
        $app->set('smtpuser', $input->get('smtpuser', '', 'STRING'));
        $app->set('smtphost', $input->get('smtphost'));
        $app->set('smtpsecure', $input->get('smtpsecure'));
        $app->set('smtpport', $input->get('smtpport'));
        $app->set('mailfrom', $input->get('mailfrom', '', 'STRING'));
        $app->set('fromname', $input->get('fromname', '', 'STRING'));
        $app->set('mailer', $input->get('mailer'));
        $app->set('mailonline', $input->get('mailonline'));

        // Use smtppass only if it was submitted
        if ($smtppass !== null) {
            $app->set('smtppass', $smtppass);
        }

        $mail = Factory::getMailer();

        // Prepare email and try to send it
        $mailer = new MailTemplate('com_config.test_mail', $user->getParam('language', $app->get('language')), $mail);
        $mailer->addTemplateData(
            array(
                'sitename' => $app->get('sitename'),
                'method' => Text::_('COM_CONFIG_SENDMAIL_METHOD_' . strtoupper($mail->Mailer))
            )
        );
        $mailer->addRecipient($app->get('mailfrom'), $app->get('fromname'));

        try {
            $mailSent = $mailer->send();
        } catch (MailDisabledException | phpMailerException $e) {
            $app->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        if ($mailSent === true) {
            $methodName = Text::_('COM_CONFIG_SENDMAIL_METHOD_' . strtoupper($mail->Mailer));

            // If JMail send the mail using PHP Mail as fallback.
            if ($mail->Mailer !== $app->get('mailer')) {
                $app->enqueueMessage(Text::sprintf('COM_CONFIG_SENDMAIL_SUCCESS_FALLBACK', $app->get('mailfrom'), $methodName), 'warning');
            } else {
                $app->enqueueMessage(Text::sprintf('COM_CONFIG_SENDMAIL_SUCCESS', $app->get('mailfrom'), $methodName), 'message');
            }

            return true;
        }

        $app->enqueueMessage(Text::_('COM_CONFIG_SENDMAIL_ERROR'), 'error');

        return false;
    }
}
