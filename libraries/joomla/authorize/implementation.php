<?php
/**
 * @package     Joomla
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */



abstract class JAuthorizeImplementation
{
	protected static $authorizationMatrix = array();

	const APPENDSUPPORT = false;

	/**
	 * Method to get the value
	 *
	 * @param   string  $key           Key to search for in the data array
	 * @param   mixed   $defaultValue  Default value to return if the key is not set
	 *
	 * @return  mixed   Value | defaultValue if doesn't exist
	 *
	 * @since   4.0
	 */
	public function get($key, $defaultValue = null)
	{
		if ($key == 'authorizationMatrix')
		{
			return isset(static::$authorizationMatrix[__CLASS__]) ? static::$authorizationMatrix[__CLASS__] : $defaultValue;
		}

		return isset($this->$key) ? $this->$key : $defaultValue;
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
	public function set($name, $value)
	{
		switch ($name)
		{
			case 'authorizationMatrix':
				static::$authorizationMatrix[__CLASS__] = $value;
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

	public function isAppendSupported()
	{
		return static::APPENDSUPPORT;
	}
}