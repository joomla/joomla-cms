<?php

/**
 * @package     Joomla.Tests
 * @subpackage  Helper
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Helper;

use Codeception\Module\Db;

/**
 * JoomlaDb Helper class for Acceptance.
 *
 * @package  Codeception\Module
 *
 * @since    3.7.3
 */
class JoomlaDb extends Db
{
    /**
     * The table prefix.
     *
     * @var    string
     * @since  3.7.3
     */
    protected $prefix;

    /**
     * Codeception Hook: called after configuration is loaded.
     *
     * @return  void
     *
     * @since   3.7.3
     */
    // phpcs:ignore
    public function _initialize()
    {
        $this->prefix = (isset($this->config['prefix'])) ? $this->config['prefix'] : '';

        parent::_initialize();
    }

    /**
     * Inserts an SQL record into a database. This record will be erased after each test.
     *
     * @param   string  $table  Table
     * @param   array   $data   Data
     *
     * @return  integer The last insert id
     *
     * @since   3.7.3
     */
    public function haveInDatabase($table, array $data)
    {
        $table = $this->addPrefix($table);

        return parent::haveInDatabase($table, $data);
    }

    /**
     * See an entry in the database.
     *
     * @param   string  $table     Table
     * @param   array   $criteria  Criteria
     *
     * @return  void
     *
     * @since   3.7.3
     */
    public function seeInDatabase($table, $criteria = [])
    {
        $table = $this->addPrefix($table);

        parent::seeInDatabase($table, $criteria);
    }

    /**
     * Don't see in database.
     *
     * @param   string  $table     Table
     * @param   array   $criteria  Criteria
     *
     * @return  void
     *
     * @since   3.7.3
     */
    public function dontSeeInDatabase($table, $criteria = [])
    {
        $table = $this->addPrefix($table);

        parent::dontSeeInDatabase($table, $criteria);
    }

    /**
     * Grab an entry from the database.
     *
     * @param   string  $table     Table
     * @param   string  $column    Column
     * @param   array   $criteria  Criteria
     *
     * @return  mixed
     *
     * @since   3.7.3
     */
    public function grabFromDatabase($table, $column, $criteria = [])
    {
        $table = $this->addPrefix($table);

        return parent::grabFromDatabase($table, $column, $criteria);
    }

    /**
     * Asserts that the given number of records were found in the database.
     *
     * @param   integer  $expectedNumber  Expected number
     * @param   string   $table           Table name
     * @param   array    $criteria        Search criteria [Optional]
     *
     * @return  void
     *
     * @since   3.7.3
     */
    public function seeNumRecords($expectedNumber, $table, array $criteria = [])
    {
        $table = $this->addPrefix($table);

        parent::seeNumRecords($expectedNumber, $table, $criteria);
    }

    /**
     * Returns the number of rows in a database.
     *
     * @param   string  $table     Table name
     * @param   array   $criteria  Search criteria [Optional]
     *
     * @return  integer
     *
     * @since    3.7.3
     */
    public function grabNumRecords($table, array $criteria = [])
    {
        $table = $this->addPrefix($table);

        return parent::grabNumRecords($table, $criteria);
    }

    /**
     * Update an SQL record into a database.
     *
     * @param   string  $table     Table name
     * @param   array   $data      Data to update in the table. Key=> value is column name => data
     * @param   array   $criteria  Search criteria [Optional]
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function updateInDatabase($table, array $data, array $criteria = [])
    {
        $table = $this->addPrefix($table);

        parent::updateInDatabase($table, $data, $criteria);
    }

    /**
     * Deletes records in a database.
     *
     * @param   string  $table     Table name
     * @param   array   $criteria  Search criteria [Optional]
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function deleteFromDatabase($table, $criteria = []): void
    {
        $table = $this->addPrefix($table);

        $this->driver->deleteQueryByCriteria($table, $criteria);
    }

    /**
     * Add the table prefix.
     *
     * @param   string  $table  Table without prefix
     *
     * @return  string
     *
     * @since   3.7.3
     */
    protected function addPrefix($table)
    {
        return $this->prefix . $table;
    }

    /**
     * getConfig
     *
     * @param   string $value  Get the setting from the option
     *
     * @return mixed
     *
     * @since version
     * @throws \Codeception\Exception\ModuleException
     */
    public function getConfig($value)
    {
        return $this->getModule('Joomla\Browser\JoomlaBrowser')->_getConfig($value);
    }
}
