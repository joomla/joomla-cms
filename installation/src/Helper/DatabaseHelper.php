<?php

/**
 * @package    Joomla.Installation
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla Installation Database Helper Class.
 *
 * @since  1.6
 */
abstract class DatabaseHelper
{
    /**
     * The minimum database server version for MariaDB databases as required by the CMS.
     * This is not necessarily equal to what the database driver requires.
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $dbMinimumMariaDb = '10.1';

    /**
     * The minimum database server version for MySQL databases as required by the CMS.
     * This is not necessarily equal to what the database driver requires.
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $dbMinimumMySql = '5.6';

    /**
     * The minimum database server version for PostgreSQL databases as required by the CMS.
     * This is not necessarily equal to what the database driver requires.
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $dbMinimumPostgreSql = '11.0';

    /**
     * Method to get a database driver.
     *
     * @param   string   $driver    The database driver to use.
     * @param   string   $host      The hostname to connect on.
     * @param   string   $user      The user name to connect with.
     * @param   string   $password  The password to use for connection authentication.
     * @param   string   $database  The database to use.
     * @param   string   $prefix    The table prefix to use.
     * @param   boolean  $select    True if the database should be selected.
     * @param   array    $ssl       Database TLS connection options.
     *
     * @return  DatabaseInterface
     *
     * @since   1.6
     */
    public static function getDbo($driver, $host, $user, $password, $database, $prefix, $select = true, array $ssl = [])
    {
        static $db;

        if (!$db) {
            // Build the connection options array.
            $options = [
                'driver'   => $driver,
                'host'     => $host,
                'user'     => $user,
                'password' => $password,
                'database' => $database,
                'prefix'   => $prefix,
                'select'   => $select,
            ];

            if (!empty($ssl['dbencryption'])) {
                $options['ssl'] = [
                    'enable'             => true,
                    'verify_server_cert' => (bool) $ssl['dbsslverifyservercert'],
                ];

                foreach (['cipher', 'ca', 'key', 'cert'] as $value) {
                    $confVal = trim($ssl['dbssl' . $value]);

                    if ($confVal !== '') {
                        $options['ssl'][$value] = $confVal;
                    }
                }
            }

            // Enable utf8mb4 connections for mysql adapters
            if (strtolower($driver) === 'mysqli') {
                $options['utf8mb4'] = true;
            }

            if (strtolower($driver) === 'mysql') {
                $options['charset'] = 'utf8mb4';
            }

            // Get a database object.
            $db = DatabaseDriver::getInstance($options);
        }

        return $db;
    }

    /**
     * Convert encryption options to array.
     *
     * @param   \stdClass  $options  The session options
     *
     * @return  array  The encryption settings
     *
     * @since   4.0.0
     */
    public static function getEncryptionSettings($options)
    {
        return [
            'dbencryption'          => $options->db_encryption,
            'dbsslverifyservercert' => $options->db_sslverifyservercert,
            'dbsslkey'              => $options->db_sslkey,
            'dbsslcert'             => $options->db_sslcert,
            'dbsslca'               => $options->db_sslca,
            'dbsslcipher'           => $options->db_sslcipher,
        ];
    }

    /**
     * Get the minimum required database server version.
     *
     * @param   DatabaseDriver  $db       Database object
     * @param   \stdClass       $options  The session options
     *
     * @return  string  The minimum required database server version.
     *
     * @since   4.0.0
     */
    public static function getMinimumServerVersion($db, $options)
    {
        // Get minimum database version required by the database driver
        $minDbVersionRequired = $db->getMinimum();

        // Get minimum database version required by the CMS
        if (in_array($options->db_type, ['mysql', 'mysqli'])) {
            if ($db->isMariaDb()) {
                $minDbVersionCms = self::$dbMinimumMariaDb;
            } else {
                $minDbVersionCms = self::$dbMinimumMySql;
            }
        } else {
            $minDbVersionCms = self::$dbMinimumPostgreSql;
        }

        // Use most restrictive, i.e. largest minimum database version requirement
        if (version_compare($minDbVersionCms, $minDbVersionRequired) > 0) {
            $minDbVersionRequired = $minDbVersionCms;
        }

        return $minDbVersionRequired;
    }

    /**
     * Validate and clean up database connection parameters.
     *
     * @param   \stdClass       $options  The session options
     *
     * @return  string|boolean  A string with the translated error message if
     *                          validation error, otherwise false.
     *
     * @since   4.0.0
     */
    public static function validateConnectionParameters($options)
    {
        // Ensure a database type was selected.
        if (empty($options->db_type)) {
            return Text::_('INSTL_DATABASE_INVALID_TYPE');
        }

        // Ensure that a hostname and user name were input.
        if (empty($options->db_host) || empty($options->db_user)) {
            return Text::_('INSTL_DATABASE_INVALID_DB_DETAILS');
        }

        // Ensure that a database name is given.
        if (empty($options->db_name)) {
            return Text::_('INSTL_DATABASE_EMPTY_NAME');
        }

        // Validate length of database name.
        if (strlen($options->db_name) > 64) {
            return Text::_('INSTL_DATABASE_NAME_TOO_LONG');
        }

        // Validate database table prefix.
        if (empty($options->db_prefix) || !preg_match('#^[a-zA-Z]+[a-zA-Z0-9_]*$#', $options->db_prefix)) {
            return Text::_('INSTL_DATABASE_PREFIX_MSG');
        }

        // Validate length of database table prefix.
        if (strlen($options->db_prefix) > 15) {
            return Text::_('INSTL_DATABASE_FIX_TOO_LONG');
        }

        // Validate database name.
        if (in_array($options->db_type, ['pgsql', 'postgresql']) && !preg_match('#^[a-zA-Z_][0-9a-zA-Z_$]*$#', $options->db_name)) {
            return Text::_('INSTL_DATABASE_NAME_MSG_POSTGRES');
        }

        if (in_array($options->db_type, ['mysql', 'mysqli']) && preg_match('#[\\\\\/]#', $options->db_name)) {
            return Text::_('INSTL_DATABASE_NAME_MSG_MYSQL');
        }

        // Workaround for UPPERCASE table prefix for postgresql
        if (in_array($options->db_type, ['pgsql', 'postgresql'])) {
            if (strtolower($options->db_prefix) != $options->db_prefix) {
                return Text::_('INSTL_DATABASE_FIX_LOWERCASE');
            }
        }

        // Validate and clean up database connection encryption options
        $optionsChanged = false;

        if ($options->db_encryption === 0) {
            // Reset unused options
            if (!empty($options->db_sslkey)) {
                $options->db_sslkey = '';
                $optionsChanged     = true;
            }

            if (!empty($options->db_sslcert)) {
                $options->db_sslcert = '';
                $optionsChanged      = true;
            }

            if ($options->db_sslverifyservercert) {
                $options->db_sslverifyservercert = false;
                $optionsChanged                  = true;
            }

            if (!empty($options->db_sslca)) {
                $options->db_sslca = '';
                $optionsChanged    = true;
            }

            if (!empty($options->db_sslcipher)) {
                $options->db_sslcipher = '';
                $optionsChanged        = true;
            }
        } else {
            // Check localhost
            if (strtolower($options->db_host) === 'localhost') {
                return Text::_('INSTL_DATABASE_ENCRYPTION_MSG_LOCALHOST');
            }

            // Check CA file and folder depending on database type if server certificate verification
            if ($options->db_sslverifyservercert) {
                if (empty($options->db_sslca)) {
                    return Text::sprintf('INSTL_DATABASE_ENCRYPTION_MSG_FILE_FIELD_EMPTY', Text::_('INSTL_DATABASE_ENCRYPTION_CA_LABEL'));
                }

                if (!File::exists(Path::clean($options->db_sslca))) {
                    return Text::sprintf('INSTL_DATABASE_ENCRYPTION_MSG_FILE_FIELD_BAD', Text::_('INSTL_DATABASE_ENCRYPTION_CA_LABEL'));
                }
            } else {
                // Reset unused option
                if (!empty($options->db_sslca)) {
                    $options->db_sslca = '';
                    $optionsChanged    = true;
                }
            }

            // Check key and certificate if two-way encryption
            if ($options->db_encryption === 2) {
                if (empty($options->db_sslkey)) {
                    return Text::sprintf('INSTL_DATABASE_ENCRYPTION_MSG_FILE_FIELD_EMPTY', Text::_('INSTL_DATABASE_ENCRYPTION_KEY_LABEL'));
                }

                if (!File::exists(Path::clean($options->db_sslkey))) {
                    return Text::sprintf('INSTL_DATABASE_ENCRYPTION_MSG_FILE_FIELD_BAD', Text::_('INSTL_DATABASE_ENCRYPTION_KEY_LABEL'));
                }

                if (empty($options->db_sslcert)) {
                    return Text::sprintf('INSTL_DATABASE_ENCRYPTION_MSG_FILE_FIELD_EMPTY', Text::_('INSTL_DATABASE_ENCRYPTION_CERT_LABEL'));
                }

                if (!File::exists(Path::clean($options->db_sslcert))) {
                    return Text::sprintf('INSTL_DATABASE_ENCRYPTION_MSG_FILE_FIELD_BAD', Text::_('INSTL_DATABASE_ENCRYPTION_CERT_LABEL'));
                }
            } else {
                // Reset unused options
                if (!empty($options->db_sslkey)) {
                    $options->db_sslkey = '';
                    $optionsChanged     = true;
                }

                if (!empty($options->db_sslcert)) {
                    $options->db_sslcert = '';
                    $optionsChanged      = true;
                }
            }
        }

        // Save options to session data if changed
        if ($optionsChanged) {
            $optsArr = ArrayHelper::fromObject($options);
            Factory::getSession()->set('setup.options', $optsArr);
        }

        return false;
    }

    /**
     * Security check for remote db hosts
     *
     * @param   \stdClass       $options  The session options
     *
     * @return  boolean         True if passed, otherwise false.
     *
     * @since   4.0.0
     */
    public static function checkRemoteDbHost($options)
    {
        // Security check for remote db hosts: Check env var if disabled. Also disable in CLI.
        $shouldCheckLocalhost = getenv('JOOMLA_INSTALLATION_DISABLE_LOCALHOST_CHECK') !== '1'
            && !defined('_JCLI_INSTALLATION');

        // Per default allowed DB hosts: localhost / 127.0.0.1 / ::1 (optionally with port)
        $localhost = '/^(((localhost|127\.0\.0\.1|\[\:\:1\])(\:[1-9]{1}[0-9]{0,4})?)|(\:\:1))$/';

        // Check the security file if the db_host is not localhost / 127.0.0.1 / ::1
        if ($shouldCheckLocalhost && preg_match($localhost, $options->db_host) !== 1) {
            $remoteDbFileTestsPassed = Factory::getSession()->get('remoteDbFileTestsPassed', false);

            // When all checks have been passed we don't need to do this here again.
            if ($remoteDbFileTestsPassed === false) {
                $generalRemoteDatabaseMessage = Text::sprintf(
                    'INSTL_DATABASE_HOST_IS_NOT_LOCALHOST_GENERAL_MESSAGE',
                    'https://docs.joomla.org/Special:MyLanguage/J3.x:Secured_procedure_for_installing_Joomla_with_a_remote_database'
                );

                $remoteDbFile = Factory::getSession()->get('remoteDbFile', false);

                if ($remoteDbFile === false) {
                    // Add the general message
                    Factory::getApplication()->enqueueMessage($generalRemoteDatabaseMessage, 'warning');

                    // This is the file you need to remove if you want to use a remote database
                    $remoteDbFile = '_Joomla' . UserHelper::genRandomPassword(21) . '.txt';
                    Factory::getSession()->set('remoteDbFile', $remoteDbFile);

                    // Get the path
                    $remoteDbPath = JPATH_INSTALLATION . '/' . $remoteDbFile;

                    // When the path is not writable the user needs to create the file manually
                    if (!File::write($remoteDbPath, '')) {
                        // Request to create the file manually
                        Factory::getApplication()->enqueueMessage(
                            Text::sprintf(
                                'INSTL_DATABASE_HOST_IS_NOT_LOCALHOST_CREATE_FILE',
                                $remoteDbFile,
                                'installation',
                                Text::_('INSTL_INSTALL_JOOMLA')
                            ),
                            'notice'
                        );

                        Factory::getSession()->set('remoteDbFileUnwritable', true);

                        return false;
                    }

                    // Save the file name to the session
                    Factory::getSession()->set('remoteDbFileWrittenByJoomla', true);

                    // Request to delete that file
                    Factory::getApplication()->enqueueMessage(
                        Text::sprintf(
                            'INSTL_DATABASE_HOST_IS_NOT_LOCALHOST_DELETE_FILE',
                            $remoteDbFile,
                            'installation',
                            Text::_('INSTL_INSTALL_JOOMLA')
                        ),
                        'notice'
                    );

                    return false;
                }

                if (
                    Factory::getSession()->get('remoteDbFileWrittenByJoomla', false) === true
                    && File::exists(JPATH_INSTALLATION . '/' . $remoteDbFile)
                ) {
                    // Add the general message
                    Factory::getApplication()->enqueueMessage($generalRemoteDatabaseMessage, 'warning');

                    // Request to delete the file
                    Factory::getApplication()->enqueueMessage(
                        Text::sprintf(
                            'INSTL_DATABASE_HOST_IS_NOT_LOCALHOST_DELETE_FILE',
                            $remoteDbFile,
                            'installation',
                            Text::_('INSTL_INSTALL_JOOMLA')
                        ),
                        'notice'
                    );

                    return false;
                }

                if (Factory::getSession()->get('remoteDbFileUnwritable', false) === true && !File::exists(JPATH_INSTALLATION . '/' . $remoteDbFile)) {
                    // Add the general message
                    Factory::getApplication()->enqueueMessage($generalRemoteDatabaseMessage, 'warning');

                    // Request to create the file manually
                    Factory::getApplication()->enqueueMessage(
                        Text::sprintf(
                            'INSTL_DATABASE_HOST_IS_NOT_LOCALHOST_CREATE_FILE',
                            $remoteDbFile,
                            'installation',
                            Text::_('INSTL_INSTALL_JOOMLA')
                        ),
                        'notice'
                    );

                    return false;
                }

                // All tests for this session passed set it to the session
                Factory::getSession()->set('remoteDbFileTestsPassed', true);
            }
        }

        return true;
    }

    /**
     * Check database server parameters after connection
     *
     * @param   DatabaseDriver  $db       Database object
     * @param   \stdClass       $options  The session options
     *
     * @return  string|boolean  A string with the translated error message if
     *                          some server parameter is not ok, otherwise false.
     *
     * @since   4.0.0
     */
    public static function checkDbServerParameters($db, $options)
    {
        $dbVersion = $db->getVersion();

        // Get required database version
        $minDbVersionRequired = self::getMinimumServerVersion($db, $options);

        // Check minimum database version
        if (version_compare($dbVersion, $minDbVersionRequired) < 0) {
            if (in_array($options->db_type, ['mysql', 'mysqli']) && $db->isMariaDb()) {
                $errorMessage = Text::sprintf(
                    'INSTL_DATABASE_INVALID_MARIADB_VERSION',
                    $minDbVersionRequired,
                    $dbVersion
                );
            } else {
                $errorMessage = Text::sprintf(
                    'INSTL_DATABASE_INVALID_' . strtoupper($options->db_type) . '_VERSION',
                    $minDbVersionRequired,
                    $dbVersion
                );
            }

            return $errorMessage;
        }

        // Check database connection encryption
        if ($options->db_encryption !== 0 && empty($db->getConnectionEncryption())) {
            if ($db->isConnectionEncryptionSupported()) {
                $errorMessage = Text::_('INSTL_DATABASE_ENCRYPTION_MSG_CONN_NOT_ENCRYPT');
            } else {
                $errorMessage = Text::_('INSTL_DATABASE_ENCRYPTION_MSG_SRV_NOT_SUPPORTS');
            }

            return $errorMessage;
        }

        return false;
    }
}
