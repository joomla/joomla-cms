<?php
/**
 * @package     Joomla
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

interface JAuthorizeInterface
{
	/**
	 * Method to set a value Example: $access->set('items', $items);
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
	 * Method to get the value
	 *
	 * @param   string  $key           Key to search for in the data array
	 *
	 * @return  mixed   Value
	 *
	 * @since   4.0
	 */
	public function __get($key, $defaultValue = null);


	public function check($actor, $target, $action);

	public function allow($actor, $target, $action);

	public function deny($actor, $target, $action);

	public function appendFilterQuery(&$query, $joinfield, $permission, $orWhere = null, $groups = null);

	public function isAppendSupported();
}