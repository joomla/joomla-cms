<?php
/**
 * @package     Joomla.Test
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Helper;

use Codeception\Module\Db;

/**
 * JoomlaDb Helper class for Acceptance.
 *
 * @package  Codeception\Module
 *
 * @since    __DEPLOY_VERSION__
 */
class JoomlaDb extends Db
{
	/**
	 * The table prefix
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $prefix;

	/**
	 * Codeception Hook: called after configuration is loaded
	 *
	 * @return  mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function _initialize()
	{
		$this->prefix = (isset($this->config['prefix'])) ? $this->config['prefix'] : '';

		return parent::_initialize();
	}

	/**
	 * Inserts an SQL record into a database. This record will be
	 * erased after the test.
	 *
	 * @param   string  $table  Table
	 * @param   array   $data   Data
	 *
	 * @return  integer The last insert id
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function haveInDatabase($table, array $data)
	{
		$table = $this->addPrefix($table);

		return parent::haveInDatabase($table, $data);
	}

	/**
	 * Find an entry in the database
	 *
	 * @param   string  $table     Table
	 * @param   array   $criteria  Criteria
	 *
	 * @return  mixed|false
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function findInDatabase($table, $criteria = [])
	{
		$table = $this->addPrefix($table);

		return parent::seeInDatabase($table, $criteria);
	}

	/**
	 * Don't see in database
	 *
	 * @param   string  $table     Table
	 * @param   array   $criteria  Criteria
	 *
	 * @return  mixed|false
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function dontSeeInDatabase($table, $criteria = [])
	{
		$table = $this->addPrefix($table);

		return parent::dontSeeInDatabase($table, $criteria);
	}

	/**
	 * Grab an entry from the database
	 *
	 * @param   string  $table     Table
	 * @param   string  $column    Column
	 * @param   array   $criteria  Criteria
	 *
	 * @return  mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function grabFromDatabase($table, $column, $criteria = null)
	{
		$table = $this->addPrefix($table);

		return parent::grabFromDatabase($table, $column, $criteria);
	}

	/**
	 * Asserts that the given number of records were found in the database.
	 *
	 * @param   int     $expectedNumber  Expected number
	 * @param   string  $table           Table name
	 * @param   array   $criteria        Search criteria [Optional]
	 *
	 * @return  mixed|bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function seeNumRecords($expectedNumber, $table, array $criteria = [])
	{
		$table = $this->addPrefix($table);

		return parent::seeNumRecords($expectedNumber, $table, $criteria);
	}

	/**
	 * Returns the number of rows in a database
	 *
	 * @param   string  $table     Table name
	 * @param   array   $criteria  Search criteria [Optional]
	 *
	 * @return  int
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public function grabNumRecords($table, array $criteria = [])
	{
		$table = $this->addPrefix($table);

		return parent::grabNumRecords($table, $criteria);
	}

	/**
	 * Add the table prefix
	 *
	 * @param   $table  string  Table without prefix
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addPrefix($table)
	{
		return $this->prefix . $table;
	}
}
