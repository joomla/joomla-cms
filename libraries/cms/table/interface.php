<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

interface JTableCmsInterface
{
	/**
	 * Method to get the JDatabaseDriver object.
	 *
	 * @return  JDatabaseDriver  The internal database driver object.
	 */
	public function getDbo();

	/**
	 * Method to get the primary key field name for the table.
	 *
	 * @throws InvalidArgumentException
	 * @return  string  The name of the primary key for the table.
	 */
	public function getKeyName();

	/**
	 * Method to create a row in the database from the JTable instance properties.
	 * If primary key values are set they will be ignored.
	 *
	 * @param   array|object $src the data to bind before update
	 * @param   array  $ignore  An optional array properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 */
	public function create($src, $ignore = array());

	/**
	 * Method to bind an associative array or object to this class instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed $src  to bind to this class instance.
	 * @param   array  $ignore  An optional array properties to ignore while binding.
	 *
	 * @throws InvalidArgumentException
	 * @return  boolean  True on success.
	 */
	function bind($src, $ignore = array());

	/**
	 * Method to perform sanity checks on the JTable instance properties to ensure
	 * they are safe to store in the database. Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return boolean True if the instance is sane and able to be stored in the database.
	 */
	public function check();

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the JTable instance properties.
	 *
	 * @param   int     $pk    The primary key of the record to load,
	 * @param   array  $ignore  An optional array or space separated list of properties to ignore while binding.
	 * @param   boolean $reset True to reset the default values before loading the new row.
	 *
	 * @throws ErrorException if no record is found
	 * @throws InvalidArgumentException if $pk is empty
	 * @return  boolean  True if successful. False if row not found.
	 */
	public function load($pk, $ignore = array(), $reset = true);

	/**
	 * Method to reset all table fields to the defaults set field definition
	 * It will ignore the primary key as well as any class properties not in the table schema.
	 *
	 * @return  void
	 */
	public function reset();

	/**
	 * Method to update a row in the database from the JTable instance properties.
	 * If primary key values are not set this method will call the create method
	 *
	 * @param   array|object $src the data to bind before update
	 * @param   array  $ignore        An optional array properties to ignore while binding.
	 * @param   boolean $updateNulls  True to update fields even if they are null.
	 * @param   boolean $loadFirst    True to load the table before updating.
	 *
	 * @return  boolean  True on success.
	 */
	public function update($src, $ignore = array(), $updateNulls = false, $loadFirst = false);

	/**
	 * Method to delete a record by primary key
	 *
	 * @param   int $pk The primary key of the record to delete,
	 *
	 * @throws ErrorException
	 * @throws InvalidArgumentException
	 * @return  boolean  True on success.
	 */
	public function delete($pk);

}
