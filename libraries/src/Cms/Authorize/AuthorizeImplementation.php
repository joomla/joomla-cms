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
	 * @since  __DEPLOY_VERSION__
	 */
	private static $authorizationMatrix = array();

	/**
	 * Database object
	 *
	 * @var    object
	 * @since  __DEPLOY_VERSION__
	 */
	private $db = null;


	/**
	 * Method to get the value
	 *
	 * @param   string  $key  Key to search for in the data array
	 *
	 * @return  mixed   Value | null if doesn't exist
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __get($key)
	{
		switch ($key)
		{
			case 'authorizationMatrix':

				if ($this instanceof AuthorizeInterface)
				{
					$class = get_class($this);

					return self::getMatrix($class);
				}
				else
				{
					throw new \UnexpectedValueException('authorizationMatrix can only be read from classes implementing AuthorizeInterface');
				}

				break;

			default:
				if (isset($this->$key))
				{
					return $this->$key;
				}
				else
				{
					trigger_error(sprintf('Trying to __get %s that does not exist in %s', $key, get_class($this)), E_USER_NOTICE);
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
	 * @since   __DEPLOY_VERSION__
	 */
	final private static function getMatrix($class)
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'authorizationMatrix':

				if ($this instanceof AuthorizeInterface)
				{
					$class = get_class($this);

					self::setMatrix($value, $class);
				}
				else
				{
					throw new \UnexpectedValueException('authorizationMatrix can only be set from classes implementing AuthorizeInterface');
				}

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
					trigger_error(sprintf('Trying to __set %s that does not exist in %s', $name, get_class($this)), E_USER_NOTICE);
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
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	final private static function setMatrix($value, $class)
	{
		self::$authorizationMatrix[$class] = $value;
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
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
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
	 * @param   \JDatabaseQuery  &$query      Database query object to append to
	 * @param   string           $joincolumn  Name of the database column used for join ON
	 * @param   string           $action      The name of the action to authorise.
	 * @param   string           $orWhere     Appended to generated where condition with OR clause.
	 * @param   array            $groups      Array of group ids to get permissions for
	 *
	 * @return  mixed database query object or false if this function is not implemented
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function appendFilterQuery(\JDatabaseQuery &$query, $joincolumn, $action, $orWhere = null, $groups = null)
	{
		return false;
	}
}
