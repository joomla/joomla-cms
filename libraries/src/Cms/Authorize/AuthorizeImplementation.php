<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Authorize;

defined('JPATH_PLATFORM') or die;

/**
 * AuthorizeImplementation class.
 *
 * @since  1.0
 */
abstract class AuthorizeImplementation
{
	/**
	 * A multidimensional array with authorization matryx [authorizationclass][assetid][action1][group] = value
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

	/**
	 * @const  boolean is append query supported?
	 * @since  4.0
	 */
	const APPENDSUPPORT = false;

	/**
	 * Method to get the value
	 *
	 * @param   string  $key  Key to search for in the data array
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
				$class = get_class($this);

				return self::getMatrix($class);
				break;

			case 'appendsupport':
				return static::APPENDSUPPORT;
				break;

			default:
				if (isset($this->$key))
				{
					return $this->$key;
				}
				else
				{
					throw new \UnexpectedValueException(sprintf('%s does not exist in %s', $key, get_class($this)));
				}
				break;
		}
	}

	/**
	 * A workaround method to get value of the authorization matrix.
	 * Can be removed/changed when there is support for static getters
	 *
	 * @param   string  $class  Child class name
	 *
	 * @return  array
	 *
	 * @since   4.0
	 */
	protected static function getMatrix($class)
	{
		return isset(self::$authorizationMatrix[$class]) ? self::$authorizationMatrix[$class] : array();
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
				$class = get_class($this);
				self::setMatrix($value, $class);
				break;

			case 'db':
				if ($value instanceof \JDatabaseDriver)
				{
					$this->db = $value;
				}
				break;

			default:
				if (property_exists('this', $name))
				{
					$this->$name = $value;
				}
				else
				{
					throw new \UnexpectedValueException(sprintf('%s does not exist in %s', $name, get_class($this)));
				}
				break;
		}

		return $this;
	}

	/**
	 * A workaround method to set value of the authorization matrix. Even that it is protected it will
	 * throw exception when called from child classes. Can be removed/changed when there is support for static setters
	 *
	 * @param   mixed   $value  Value to assign to the property
	 * @param   string  $class  Child class name
	 *
	 * @return  self
	 *
	 * @since   4.0
	 */
	protected static function setMatrix($value, $class)
	{
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];

		if ($trace['function'] == '__set' && $trace['class'] == 'Joomla\Cms\Authorize\AuthorizeImplementation')
		{
			self::$authorizationMatrix[$class] = $value;
		}
		else
		{
			throw new \BadMethodCallException('setMatrix should not be called from child classes directly, use $this->authorizationMatrix');
		}
	}


	/**
	 * Set actor as authorised to perform an action
	 *
	 * @param   integer  $actor   Id of the actor for which to check authorisation.
	 * @param   mixed    $target  Subject of the check
	 * @param   string   $action  The name of the action to authorise.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	protected function allow($actor, $target, $action)
	{

		$class = get_class($this);

		$authorizationMatrix = $this->authorizationMatrix;

		if (isset($authorizationMatrix[$class]))
		{
			$this->authorizationMatrix[$class][$target][$action][$actor] = 1;
		}

	}

	/**
	 * Set actor as not authorised to perform an action
	 *
	 * @param   integer  $actor   Id of the actor for which to check authorisation.
	 * @param   mixed    $target  Subject of the check
	 * @param   string   $action  The name of the action to authorise.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	protected function deny($actor, $target, $action)
	{
		$class = get_class($this);

		$authorizationMatrix = $this->authorizationMatrix;

		if (isset($authorizationMatrix[$class]))
		{
			$this->authorizationMatrix[$class][$target][$action][$actor] = 0;
		}

	}

	/** Inject permissions filter in the database object
	 *
	 * @param   object  &$query      Database query object to append to
	 * @param   string  $joincolumn  Name of the database column used for join ON
	 * @param   string  $action      The name of the action to authorise.
	 * @param   string  $orWhere     Appended to generated where condition with OR clause.
	 * @param   array   $groups      Array of group ids to get permissions for
	 *
	 * @return  mixed database query object or false if this function is not implemented
	 *
	 * @since   4.0
	 */
	public function appendFilterQuery(&$query, $joincolumn, $action, $orWhere = null, $groups = null)
	{
		return false;
	}
}
