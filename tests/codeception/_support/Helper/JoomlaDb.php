<?php
/**
 * @package     Joomla.Test
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
namespace Helper;

use Codeception\Module\Db;

/**
 * JoomlaDb Helper class for Acceptance.
 *
 * You can create DB Helper methods here
 *
 * @package  Codeception\Module
 *
 * @since    __DEPLOY_VERSION__
 */
class JoomlaDb extends Db
{
	/**
	 * @var string The table prefix
	 */
	protected $prefix;

	/**
	 * Codeception Hook: called after configuration is loaded
	 */
	public function _initialize()
	{
		$this->prefix = (isset($this->config['prefix'])) ? $this->config['prefix'] : '';

		return parent::_initialize();
	}

	/**
	 * Inserts an SQL record into a database. This record will be erased after the test.
	 *
	 * @param string $table
	 * @param array  $data
	 *
	 * @return integer $id The last insert id
	 */
	public function haveInDatabase($table, array $data)
	{
		$table = $this->addPrefix($table);

		return parent::haveInDatabase($table, $data);
	}

	/**
	 * See an entry in the database
	 *
	 * @param string $table
	 * @param array  $criteria
	 */
	public function seeInDatabase($table, $criteria = [])
	{
		$table = $this->addPrefix($table);

		parent::seeInDatabase($table, $criteria);
	}

	/**
	 * @param string $table
	 * @param array  $criteria
	 */
	public function dontSeeInDatabase($table, $criteria = [])
	{
		$table = $this->addPrefix($table);

		parent::dontSeeInDatabase($table, $criteria);
	}

	/**
	 * Grab an entry from the database
	 *
	 * @param      $table
	 * @param      $column
	 * @param null $criteria
	 *
	 * @return mixed
	 */
	public function grabFromDatabase($table, $column, $criteria = null)
	{
		$table = $this->addPrefix($table);

		return parent::grabFromDatabase($table, $column, $criteria);
	}

	/**
	 * Asserts that the given number of records were found in the database.
	 *
	 * @param int    $expectedNumber Expected number
	 * @param string $table          Table name
	 * @param array  $criteria       Search criteria [Optional]
	 */
	public function seeNumRecords($expectedNumber, $table, array $criteria = [])
	{
		$table = $this->addPrefix($table);

		parent::seeNumRecords($expectedNumber, $table, $criteria);
	}

	/**
	 * Returns the number of rows in a database
	 *
	 * @param string $table    Table name
	 * @param array  $criteria Search criteria [Optional]
	 *
	 * @return int
	 */
	public function grabNumRecords($table, array $criteria = [])
	{
		$table = $this->addPrefix($table);

		return parent::grabNumRecords($table, $criteria);
	}

	/**
	 * Add the table prefix
	 *
	 * @param $table string
	 *
	 * @return string
	 */
	protected function addPrefix($table)
	{
		return $this->prefix . $table;
	}
}
