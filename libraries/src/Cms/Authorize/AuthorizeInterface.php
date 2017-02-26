<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Authorize;

defined('JPATH_PLATFORM') or die;

interface AuthorizeInterface
{
	/**
	 * Method to allow controlled property value setting;
	 *
	 * @param   string  $name   Name of the property
	 * @param   mixed   $value  Value to assign to the property
	 *
	 * @return  self
	 *
	 * @since   4.0
	 */
	public function __set($name, $value);

	/**
	 * Method to allow controlled property value getting
	 *
	 * @param   string  $key   Key to search for in the data array
	 *
	 * @return  mixed   Value | null if doesn't exist
	 *
	 * @since   4.0
	 */
	public function __get($key);

	/**
	 * Check if actor is authorised to perform an action, optionally on an asset.
	 *
	 * @param   integer  $actor   Id of the actor for which to check authorisation.
	 * @param   mixed    $target  Subject of the check
	 * @param   string   $action  The name of the action to authorise.
	 * @param   string   $actorType   Type of actor.
	 *
	 * @return  boolean  True if authorised.
	 *
	 * @since   4.0
	 */
	public function check($actor, $target, $action, $actorType);


	/** Inject permissions filter in database object
	 *
	 * @param   object $query     Database query object to append to
	 * @param   string $joincolumn Name of the database column used for join ON
	 * @param   string $action    The name of the action to authorise.
	 * @param   string $orWhere   Appended to generated where condition with OR clause.
	 * @param   array  $groups    Array of group ids to get permissions for
	 *
	 * @param   object $query database query object to append to
	 *
	 * @return  mixed database query object or false if this function is not implemented
	 *                 	 *
	 * @since   4.0
	 */
	public function appendFilterQuery(&$query, $joincolumn, $action, $orWhere = null, $groups = null);

}