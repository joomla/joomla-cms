<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Authorize
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_PLATFORM') or die;

interface JAuthorizeInterface
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
	 * @param   string   $actorType   Optional type of actor.
	 *
	 * @return  boolean  True if authorised.
	 *
	 * @since   4.0
	 */
	public function check($actor, $target, $action, $actorType = null);

	public function allow($actor, $target, $action);

	public function deny($actor, $target, $action);

	public function appendFilterQuery(&$query, $joinfield, $permission, $orWhere = null, $groups = null);

}