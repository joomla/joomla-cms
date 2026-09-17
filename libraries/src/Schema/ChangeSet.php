<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Schema;

use Joomla\Database\DatabaseDriver;
use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Contains a set of JSchemaChange objects for a particular instance of Joomla.
 * Each of these objects contains a DDL query that should have been run against
 * the database when this database was created or updated. This enables the
 * Installation Manager to check that the current database schema is up to date.
 *
 * @since  2.5
 */
class ChangeSet
{
    /**
     * Array of ChangeItem objects
     *
     * @var    ChangeItem[]
     * @since  2.5
     */
    protected $changeItems = [];

    /**
     * DatabaseDriver object
     *
     * @var    DatabaseDriver
     * @since  2.5
     */
    protected $db = null;

    /**
     * Folder where SQL update files will be found
     *
     * @var    string
     * @since  2.5
     */
    protected $folder = null;

    /**
     * The singleton instance of this object
     *
     * @var    ChangeSet
     * @since  3.5.1
     */
    protected static $instance;

    /**
     * Constructor: builds array of $changeItems by processing the .sql files in a folder.
     * The folder for the Joomla core updates is `administrator/components/com_admin/sql/updates/<database>`.
     *
     * @param   DatabaseDriver  $db      The current database object
     * @param   string          $folder  The full path to the folder containing the update queries
     *
     * @since   2.5
     */
    public function __construct($db, $folder = null)
    {
        $this->db     = $db;
        $this->folder = $folder;
        $updateFiles  = $this->getUpdateFiles();

        // If no files were found nothing more we can do - continue
        if ($updateFiles === false) {
            return;
        }

        $updateQueries = $this->getUpdateQueries($updateFiles);

        foreach ($updateQueries as $obj) {
            $this->changeItems[] = ChangeItem::getInstance($db, $obj->file, $obj->updateQuery);
        }

        $this->markSupersededItems();
    }

    /**
     * Flags every change item whose schema object is changed again by a later update file.
     *
     * The update files are processed in ascending version order, so for any given column or
     * index the *last* statement is the one that describes the schema the extension currently
     * intends. Earlier statements describe historical intermediate states which, by design,
     * no longer hold — checking them reports a problem on a database that is perfectly correct.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function markSupersededItems()
    {
        $lastIndexFor = [];

        /**
         * Find the last change item's index for each change key.
         *
         * This records the last $this->changeItems array index for each changed database column
         * or database index.
         */
        foreach ($this->changeItems as $index => $item) {
            if (($key = $this->getTargetKey($item)) !== null) {
                $lastIndexFor[$key] = $index;
            }
        }

        /**
         * Find superseded items.
         *
         * Iterate through $this->changeItems items and compare its index to the index for the
         * specific change key recorded in $lastIndexFor. If it's different, it means that
         * another change item has superseded this one; we need to set the flag in this case.
         */
        foreach ($this->changeItems as $index => $item) {
            $key = $this->getTargetKey($item);

            if ($key !== null && $lastIndexFor[$key] !== $index) {
                $item->superseded = true;
            }
        }
    }

    /**
     * Identify the schema object a change item acts upon.
     *
     * Column-level query types share one key space. An ADD_COLUMN followed by
     * CHANGE_COLUMN_TYPE on the same column is recognised as a single history.
     *
     * Index-level query types share another key space. An ADD_INDEX followed
     * by DROP_INDEX for the same index is recognised as a single history.
     *
     * Everything else, currently creating and renaming tables, cannot be
     * superseded. It returns NULL in this case.
     *
     * @param   ChangeItem  $item  The change item to key.
     *
     * @return  string|null
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getTargetKey(ChangeItem $item)
    {
        switch ($item->queryType) {
            case 'ADD_COLUMN':
            case 'DROP_COLUMN':
            case 'CHANGE_COLUMN_TYPE':
                $scope = 'column';
                break;

            case 'ADD_INDEX':
            case 'DROP_INDEX':
                $scope = 'index';
                break;

                // CREATE_TABLE and RENAME_TABLE cannot be superseded
            default:
                return null;
        }

        // msgElements[0] is the table name, msgElements[1] the column or index name
        if (\count($item->msgElements) < 2) {
            return null;
        }

        return $scope . ':' . strtolower($item->msgElements[0] . '.' . $item->msgElements[1]);
    }

    /**
     * Returns a reference to the ChangeSet object, only creating it if it doesn't already exist.
     *
     * @param   DatabaseDriver  $db      The current database object
     * @param   string          $folder  The full path to the folder containing the update queries
     *
     * @return  ChangeSet
     *
     * @since   2.5
     */
    public static function getInstance($db, $folder = null)
    {
        if (!\is_object(static::$instance)) {
            static::$instance = new static($db, $folder);
        }

        return static::$instance;
    }

    /**
     * Checks the database and returns an array of any errors found.
     * Note these are not database errors but rather situations where
     * the current schema is not up to date.
     *
     * @return   array Array of errors if any.
     *
     * @since    2.5
     */
    public function check()
    {
        $errors = [];

        foreach ($this->changeItems as $item) {
            /**
             * An error is recorded if check() returns -2 (check failed) and the change item has
             * not been superseded by a newer change item.
             *
             * This allows extensions to keep a history of changes in their SQL update files
             * without Joomla erroneously complaining that there are database errors. If a column,
             * for example, is changed by three different versions, it's a database error only
             * if the column does not agree with the column definition derived from the latest
             * version's changes.
             */
            if ($item->check() === -2 && !$item->superseded) {
                // Error found
                $errors[] = $item;
            }
        }

        return $errors;
    }

    /**
     * Runs the update query to apply the change to the database
     *
     * @return  void
     *
     * @since   2.5
     */
    public function fix()
    {
        $this->check();

        foreach ($this->changeItems as $item) {
            $item->fix();
        }
    }

    /**
     * Returns an array of results for this set
     *
     * @return  array  associative array of changeitems grouped by unchecked, ok, error, and skipped
     *
     * @since   2.5
     */
    public function getStatus()
    {
        $result = ['unchecked' => [], 'ok' => [], 'error' => [], 'skipped' => []];

        foreach ($this->changeItems as $item) {
            switch ($item->checkStatus) {
                case 0:
                    $result['unchecked'][] = $item;
                    break;
                case 1:
                    $result['ok'][] = $item;
                    break;
                case -2:
                    // A superseded item's expectation is obsolete, so a failure is not an error
                    $result[$item->superseded ? 'skipped' : 'error'][] = $item;
                    break;
                case -1:
                    $result['skipped'][] = $item;
                    break;
            }
        }

        return $result;
    }

    /**
     * Gets the current database schema, based on the highest version number.
     * Note that the .sql files are named based on the version and date, so
     * the file name of the last file should match the database schema version
     * in the #__schemas table.
     *
     * @return  string  the schema version for the database
     *
     * @since   2.5
     */
    public function getSchema()
    {
        $updateFiles = $this->getUpdateFiles();

        // No schema files found - stop and return empty string
        if (empty($updateFiles)) {
            return '';
        }

        $result = new \SplFileInfo(array_pop($updateFiles));

        return $result->getBasename('.sql');
    }

    /**
     * Get list of SQL update files for this database
     *
     * @return  array|boolean  list of sql update full-path names. False if directory doesn't exist
     *
     * @since   2.5
     */
    private function getUpdateFiles()
    {
        // Get the folder from the database name
        $sqlFolder = $this->db->getServerType();

        // Default folder to core com_admin
        if (!$this->folder) {
            $this->folder = JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/';
        }

        // We don't want to enqueue an error if the directory doesn't exist - this can be handled elsewhere/
        // So bail here.
        if (!is_dir($this->folder . '/' . $sqlFolder)) {
            return [];
        }

        return Folder::files(
            $this->folder . '/' . $sqlFolder,
            '\.sql$',
            1,
            true,
            ['.svn', 'CVS', '.DS_Store', '__MACOSX'],
            ['^\..*', '.*~'],
            true
        );
    }

    /**
     * Get array of SQL queries
     *
     * @param   array  $sqlfiles  Array of .sql update filenames.
     *
     * @return  array  Array of \stdClass objects where:
     *                    file=filename,
     *                    update_query = text of SQL update query
     *
     * @since   2.5
     */
    private function getUpdateQueries(array $sqlfiles)
    {
        // Hold results as array of objects
        $result = [];

        foreach ($sqlfiles as $file) {
            $buffer = file_get_contents($file);

            // Create an array of queries from the sql file
            $queries = DatabaseDriver::splitSql($buffer);

            foreach ($queries as $query) {
                $fileQueries              = new \stdClass();
                $fileQueries->file        = $file;
                $fileQueries->updateQuery = $query;
                $result[]                 = $fileQueries;
            }
        }

        return $result;
    }
}
