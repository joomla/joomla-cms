<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Authorize
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

abstract class JAuthorizeImplementation
{
	/**
	 * A multidimensional array with authorization matryx [authorizationclass][assetid][action1][group] = valuem
	 *
	 * @var    array
	 * @since  4.0
	 */
	private static $authorizationMatrix = array();

	/**
	 * Database object
	 *
	 * @var    object
	 * @since  4.0
	 */
	private $db = null;

	const APPENDSUPPORT = false;

	/**
	 * Method to get the value
	 *
	 * @param   string  $key           Key to search for in the data array
	 * @param   mixed   $defaultValue  Default value to return if the key is not set
	 *
	 * @return  mixed   Value | null if doesn't exist
	 *
	 * @since   4.0
	 */
	public function __get($key)
	{
		switch ($key)
		{
			case 'authorizationMatrix':
				return isset(static::$authorizationMatrix[__CLASS__]) ? static::$authorizationMatrix[__CLASS__] : array();
				break;

			case 'appendsupport':
				return static::APPENDSUPPORT;
				break;

			default:
				return isset($this->$key) ? $this->$key : null;
				break;
		}

	}

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
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'authorizationMatrix':
				static::$authorizationMatrix[__CLASS__] = $value;
				break;

			case 'db':
				if ($value instanceof JDatabaseDriver)
				{
					$this->db = $value;
				}
				break;

			default:
				if (property_exists('this', $name))
				{
					$this->$name = $value;
				}
				break;
		}

		return $this;
	}


	public function allow($actor, $target, $action){

		if (isset(static::$authorizationMatrix[__CLASS__]))
		{
			static::$authorizationMatrix[__CLASS__][$target][$action][$actor] = 1;
		}

	}

	public function deny($actor, $target, $action)
	{
		if (isset(static::$authorizationMatrix[__CLASS__]))
		{
			static::$authorizationMatrix[__CLASS__][$target][$action][$actor] = 0;
		}

	}


	public function appendFilterQuery(&$query, $joinfield, $permission, $orWhere = null, $groups = null)
	{
		return $query;
	}


	protected function cleanAssetId($assetId)
	{
		return  strtolower(preg_replace('#[\s\-]+#', '.', trim($assetId)));
	}

	protected function cleanAction($action)
	{
		return  $this->cleanAssetId($action);
	}
}