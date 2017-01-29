<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Table;

defined('JPATH_PLATFORM') or die;

/**
 * Table class interface.
 *
 * @since  3.2
 */
interface TableInterface
{
	/**
	 * Method to bind an associative array or object to the TableInterface instance.
	 *
	 * This method only binds properties that are publicly accessible and optionally takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the TableInterface instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 * @throws  \UnexpectedValueException
	 */
	public function bind($src, $ignore = array());

	/**
	 * Method to perform sanity checks on the TableInterface instance properties to ensure they are safe to store in the database.
	 *
	 * Implementations of this interface should use this method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @since   3.2
	 */
	public function check();

	/**
	 * Method to delete a record.
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 * @throws  \UnexpectedValueException
	 */
	public function delete($pk = null);

	/**
	 * Method to get the \JDatabaseDriver object.
	 *
	 * @return  \JDatabaseDriver  The internal database driver object.
	 *
	 * @since   3.2
	 */
	public function getDbo();

	/**
	 * Method to get the primary key field name for the table.
	 *
	 * @return  string  The name of the primary key for the table.
	 *
	 * @since   3.2
	 */
	public function getKeyName();

	/**
	 * Method to load a row from the database by primary key and bind the fields to the TableInterface instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 *
	 * @since   3.2
	 * @throws  \RuntimeException
	 * @throws  \UnexpectedValueException
	 */
	public function load($keys = null, $reset = true);

	/**
	 * Method to reset class properties to the defaults set in the class definition.
	 *
	 * It will ignore the primary key as well as any private class properties.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function reset();

	/**
	 * Method to store a row in the database from the TableInterface instance properties.
	 *
	 * If a primary key value is set the row with that primary key value will be updated with the instance property values.
	 * If no primary key value is set a new row will be inserted into the database with the properties from the TableInterface instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function store($updateNulls = false);

	/**
	 * Returns the identity (primary key) value of this record
	 *
	 * @return  mixed
	 *
	 * @since  4.0
	 */
	public function getId();
}
