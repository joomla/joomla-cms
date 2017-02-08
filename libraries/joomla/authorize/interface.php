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


	public function check($actor, $target, $action);

	public function allow($actor, $target, $action);

	public function deny($actor, $target, $action);

	public function appendFilterQuery(&$query, $joinfield, $permission, $orWhere = null, $groups = null);

}