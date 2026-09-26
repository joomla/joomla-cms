<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Installation\Helper\DatabaseHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Database configuration model for the Joomla Core Installer.
 *
 * @since  3.1
 */
class DatabaseModel extends BaseInstallationModel
{
    /**
     * Method to initialise the database.
     *
     * @param   array    $options  Array with db connection credentials
     * @param   boolean  $select   Select the database when creating the connections.
     *
     * @return  DatabaseInterface|boolean  Database object on success, boolean false on failure
     *
     * @since   3.1
     */
    public function initialise(array $options, bool $select = true)
    {
        // Get the options as an object for easier handling.
        $options = ArrayHelper::toObject($options);

        // Load the backend language files so that the DB error messages work.
        $lang        = Factory::getLanguage();
        $currentLang = $lang->getTag();

        // Load the selected language
        if (LanguageHelper::exists($currentLang, JPATH_ADMINISTRATOR)) {
            $lang->load('joomla', JPATH_ADMINISTRATOR, $currentLang, true);
        } else {
            // Pre-load en-GB in case the chosen language files do not exist.
            $lang->load('joomla', JPATH_ADMINISTRATOR, 'en-GB', true);
        }

        // Validate and clean up connection parameters
        $paramsCheck = DatabaseHelper::validateConnectionParameters($options);

        if ($paramsCheck) {
            Factory::getApplication()->enqueueMessage($paramsCheck, 'warning');

            return false;
        }

        // Security check for remote db hosts
        if (!DatabaseHelper::checkRemoteDbHost($options)) {
            // Messages have been enqueued in the called function.
            return false;
        }

        // Get a database object.
        try {
            return DatabaseHelper::getDbo(
                $options->db_type,
                $options->db_host,
                $options->db_user,
                $options->db_pass_plain,
                $options->db_name,
                $options->db_prefix,
                $select,
                DatabaseHelper::getEncryptionSettings($options)
            );
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage(Text::sprintf('INSTL_DATABASE_COULD_NOT_CONNECT', $e->getMessage()), 'error');

            return false;
        }
    }

    /**
     * Method to create a new database.
     *
     * @param   array  $options  Array of database credentials
     *
     * @return  boolean
     *
     * @since   3.1
     * @throws  \RuntimeException
     */
    public function createDatabase(array $options)
    {
        $db = $this->initialise($options, false);

        if ($db === false) {
            // Error messages are enqueued by the initialise function, we just need to tell the controller how to redirect
            return false;
        }

        // Get the options as an object for easier handling.
        $options = ArrayHelper::toObject($options);

        // Check database version.
        $type = $options->db_type;

        try {
            $db_version = $db->getVersion();
        } catch (\RuntimeException $e) {
            /*
             * We may get here if the database doesn't exist, if so then explain that to users instead of showing the database connector's error
             * This only supports PDO PostgreSQL and the PDO MySQL drivers presently
             *
             * Error Messages:
             * PDO MySQL: [1049] Unknown database 'database_name'
             * PDO PostgreSQL: database "database_name" does not exist
             */
            if (
                $type === 'mysql' && strpos($e->getMessage(), '[1049] Unknown database') === 42
                || $type === 'pgsql' && strpos($e->getMessage(), 'database "' . $options->db_name . '" does not exist')
            ) {
                /*
                 * Now we're really getting insane here; we're going to try building a new DatabaseDriver instance
                 * in order to trick the connection into creating the database
                 */
                if ($type === 'mysql') {
                    // MySQL (PDO): Don't specify database name
                    $altDBoptions = [
                        'driver'   => $options->db_type,
                        'host'     => $options->db_host,
                        'user'     => $options->db_user,
                        'password' => $options->db_pass_plain,
                        'prefix'   => $options->db_prefix,
                        'select'   => false,
                        DatabaseHelper::getEncryptionSettings($options),
                    ];
                } else {
                    // PostgreSQL (PDO): Use 'postgres'
                    $altDBoptions = [
                        'driver'   => $options->db_type,
                        'host'     => $options->db_host,
                        'user'     => $options->db_user,
                        'password' => $options->db_pass_plain,
                        'database' => 'postgres',
                        'prefix'   => $options->db_prefix,
                        'select'   => false,
                        DatabaseHelper::getEncryptionSettings($options),
                    ];
                }

                $altDB = DatabaseDriver::getInstance($altDBoptions);

                // Check database server parameters
                $dbServerCheck = DatabaseHelper::checkDbServerParameters($altDB, $options);

                if ($dbServerCheck) {
                    // Some server parameter is not ok
                    throw new \RuntimeException($dbServerCheck, 500, $e);
                }

                // Try to create the database now using the alternate driver
                try {
                    $this->createDb($altDB, $options, $altDB->hasUtfSupport());
                } catch (\RuntimeException $e) {
                    // We did everything we could
                    throw new \RuntimeException(Text::_('INSTL_DATABASE_COULD_NOT_CREATE_DATABASE'), 500, $e);
                }

                // If we got here, the database should have been successfully created, now try one more time to get the version
                try {
                    $db_version = $db->getVersion();
                } catch (\RuntimeException $e) {
                    // We did everything we could
                    throw new \RuntimeException(Text::sprintf('INSTL_DATABASE_COULD_NOT_CONNECT', $e->getMessage()), 500, $e);
                }
            } else {
                // Anything getting into this part of the conditional either doesn't support manually creating the database or isn't that type of error
                throw new \RuntimeException(Text::sprintf('INSTL_DATABASE_COULD_NOT_CONNECT', $e->getMessage()), 500, $e);
            }
        }

        // Check database server parameters
        $dbServerCheck = DatabaseHelper::checkDbServerParameters($db, $options);

        if ($dbServerCheck) {
            // Some server parameter is not ok
            throw new \RuntimeException($dbServerCheck, 500, $e);
        }

        // @internal Check for spaces in beginning or end of name.
        if (\strlen(trim($options->db_name)) <> \strlen($options->db_name)) {
            throw new \RuntimeException(Text::_('INSTL_DATABASE_NAME_INVALID_SPACES'));
        }

        // @internal Check for asc(00) Null in name.
        if (strpos($options->db_name, \chr(00)) !== false) {
            throw new \RuntimeException(Text::_('INSTL_DATABASE_NAME_INVALID_CHAR'));
        }

        // Get database's UTF support.
        $utfSupport = $db->hasUtfSupport();

        // Try to select the database.
        try {
            $db->select($options->db_name);
        } catch (\RuntimeException $e) {
            // If the database could not be selected, attempt to create it and then select it.
            if (!$this->createDb($db, $options, $utfSupport)) {
                throw new \RuntimeException(Text::sprintf('INSTL_DATABASE_ERROR_CREATE', $options->db_name), 500, $e);
            }

            $db->select($options->db_name);
        }

        // Set the character set to UTF-8 for pre-existing databases.
        try {
            $db->alterDbCharacterSet($options->db_name);
        } catch (\RuntimeException $e) {
            // Continue Anyhow
        }

        $options = (array) $options;

        // Remove *_errors value.
        foreach ($options as $i => $option) {
            if (isset($i['1']) && $i['1'] == '*') {
                unset($options[$i]);

                break;
            }
        }

        $options = array_merge(['db_created' => 1], $options);

        Factory::getSession()->set('setup.options', $options);

        return true;
    }

    /**
     * Method to process the old database.
     *
     * @param   array  $options  Array with database credentials
     *
     * @return  boolean  True on success.
     *
     * @since   3.1
     */
    public function handleOldDatabase(array $options)
    {
        if (!isset($options['db_created']) || !$options['db_created']) {
            return $this->createDatabase($options);
        }

        if (!$db = $this->initialise($options)) {
            return false;
        }

        // Set the character set to UTF-8 for pre-existing databases.
        try {
            $db->alterDbCharacterSet($options['db_name']);
        } catch (\RuntimeException $e) {
            // Continue Anyhow
        }

        // Backup any old database.
        if (!$this->backupDatabase($db, $options['db_prefix'])) {
            return false;
        }

        return true;
    }

    /**
     * Method to create the database tables.
     *
     * @param   string  $schema   The SQL schema file to apply.
     * @param   array   $options  Array with database credentials
     *
     * @return  boolean  True on success.
     *
     * @since   3.1
     */
    public function createTables(string $schema, array $options)
    {
        if (!$db = $this->initialise($options)) {
            return false;
        }

        $serverType = $db->getServerType();

        // Set the appropriate schema script based on UTF-8 support.
        $schemaFile = JPATH_INSTALLATION . '/sql/' . $serverType . '/' . $schema . '.sql';

        // Check if the schema is a valid file
        if (!is_file($schemaFile)) {
            Factory::getApplication()->enqueueMessage(Text::sprintf('INSTL_ERROR_DB', Text::_('INSTL_DATABASE_NO_SCHEMA')), 'error');

            return false;
        }

        // Attempt to import the database schema.
        if (!$this->populateDatabase($db, $schemaFile)) {
            return false;
        }

        return true;
    }

    /**
     * Method to backup all tables in a database with a given prefix.
     *
     * @param   DatabaseDriver  $db      DatabaseDriver object.
     * @param   string          $prefix  Database table prefix.
     *
     * @return  boolean  True on success.
     *
     * @since    3.1
     */
    public function backupDatabase($db, $prefix)
    {
        $return = true;
        $backup = 'bak_' . $prefix;

        // Get the tables in the database.
        $tables = $db->getTableList();

        if ($tables) {
            foreach ($tables as $table) {
                // If the table uses the given prefix, back it up.
                if (strpos($table, $prefix) === 0) {
                    // Backup table name.
                    $backupTable = str_replace($prefix, $backup, $table);

                    // Drop the backup table.
                    try {
                        $db->dropTable($backupTable, true);
                    } catch (\RuntimeException $e) {
                        Factory::getApplication()->enqueueMessage(Text::sprintf('INSTL_DATABASE_ERROR_BACKINGUP', $e->getMessage()), 'error');

                        $return = false;
                    }

                    // Rename the current table to the backup table.
                    try {
                        $db->renameTable($table, $backupTable, $backup, $prefix);
                    } catch (\RuntimeException $e) {
                        Factory::getApplication()->enqueueMessage(Text::sprintf('INSTL_DATABASE_ERROR_BACKINGUP', $e->getMessage()), 'error');

                        $return = false;
                    }
                }
            }
        }

        return $return;
    }

    /**
     * Method to create a new database.
     *
     * @param   DatabaseInterface  $db       Database object.
     * @param   object             $options  Object with database credentials to pass user
     *                                       and database name to database driver.
     * @param   boolean            $utf      True if the database supports the UTF-8 character set.
     *
     * @return  boolean  True on success.
     *
     * @since   3.1
     */
    public function createDb($db, $options, $utf)
    {
        // Build the create database query.
        try {
            // Run the create database query.
            $db->createDatabase($options, $utf);
        } catch (\RuntimeException $e) {
            // If an error occurred return false.
            return false;
        }

        return true;
    }

    /**
     * Method to import a database schema from a file.
     *
     * @param   DatabaseInterface  $db      Database object.
     * @param   string             $schema  Path to the schema file.
     *
     * @return  boolean  True on success.
     *
     * @since   3.1
     */
    public function populateDatabase($db, $schema)
    {
        $return = true;

        // Get the contents of the schema file.
        if (!($buffer = file_get_contents($schema))) {
            Factory::getApplication()->enqueueMessage(Text::_('INSTL_SAMPLE_DATA_NOT_FOUND'), 'error');

            return false;
        }

        // Get an array of queries from the schema and process them.
        $queries = $this->splitQueries($buffer);

        foreach ($queries as $query) {
            // Trim any whitespace.
            $query = trim($query);

            // If the query isn't empty and is not a MySQL or PostgreSQL comment, execute it.
            if (!empty($query) && ($query[0] != '#') && ($query[0] != '-')) {
                // Execute the query.
                $db->setQuery($query);

                try {
                    $db->execute();
                } catch (\RuntimeException $e) {
                    Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

                    $return = false;
                }
            }
        }

        return $return;
    }

    /**
     * Method to split up queries from a schema file into an array.
     *
     * @param   string  $query  SQL schema.
     *
     * @return  array  Queries to perform.
     *
     * @since   3.1
     */
    protected function splitQueries($query)
    {
        $buffer    = [];
        $queries   = [];
        $in_string = false;

        // Trim any whitespace.
        $query = trim($query);

        // Remove comment lines.
        $query = preg_replace("/\n\#[^\n]*/", '', "\n" . $query);

        // Remove PostgreSQL comment lines.
        $query = preg_replace("/\n\--[^\n]*/", '', "\n" . $query);

        // Find function.
        $funct = explode('CREATE OR REPLACE FUNCTION', $query);

        // Save sql before function and parse it.
        $query = $funct[0];

        // Parse the schema file to break up queries.
        for ($i = 0; $i < \strlen($query) - 1; $i++) {
            if ($query[$i] == ';' && !$in_string) {
                $queries[] = substr($query, 0, $i);
                $query     = substr($query, $i + 1);
                $i         = 0;
            }

            if ($in_string && ($query[$i] == $in_string) && $buffer[1] != "\\") {
                $in_string = false;
            } elseif (!$in_string && ($query[$i] == '"' || $query[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\")) {
                $in_string = $query[$i];
            }

            if (isset($buffer[1])) {
                $buffer[0] = $buffer[1];
            }

            $buffer[1] = $query[$i];
        }

        // If the is anything left over, add it to the queries.
        if (!empty($query)) {
            $queries[] = $query;
        }

        // Add function part as is.
        for ($f = 1, $fMax = \count($funct); $f < $fMax; $f++) {
            $queries[] = 'CREATE OR REPLACE FUNCTION ' . $funct[$f];
        }

        return $queries;
    }
}
