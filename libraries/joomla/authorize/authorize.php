<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Authorize
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;


final class JAuthorize implements JAuthorizeInterface
{
	private static $instance = null;

	/**
	 * Implementation object
	 * _ suffixed to force usage of getters and setters, use property name without_ to get or set the value
	 *
	 * @var    array
	 * @since  4.0
	 */
	private $implementation_ = null;


	public function __construct(JAuthorizeInterface $implementation)
	{
			$this->implementation = $implementation;
	}

	public static function getInstance($implementationName = null)
	{
		if ($implementationName == null)
		{
			JEventDispatcher::getInstance()->trigger('onAuthorizationInitalize', array(&$implementationName));
		}

		$implementationClass = empty($implementationName) ? 'JAuthorizeImplementationJoomla' :
			'JAuthorizeImplementation' . JString::ucfirst($implementationName);

		if (!isset(self::$instance[$implementationClass]))
		{
			$implementation = new $implementationClass();

			self::$instance[$implementationClass] = new JAuthorize($implementation);
		}

		return self::$instance[$implementationClass];
	}

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
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'implementation':
				if ($value instanceof JAuthorizeInterface && $value instanceof JAuthorizeImplementation)
				{
					$this->implementation_ = $value;
				}
			break;

			case 'authorizationMatrix':
				$this->implementation->authorizationMatrix = $value;
			break;

			default:
				$this->implementation->$name = $value;
		}

		return $this;
	}

	/**
	 * Method to get the value
	 *
	 * @param   string  $key   Key to search for in the data array
	 *
	 * @return  mixed   Value | null if doesn't exist
	 *
	 * @since   4.0
	 */
	public function __get($key)
	{
		if ($key == 'implementation')
		{
			return $this->implementation_;
		}

		return $this->implementation->$key;
	}

	/**
	 * Method to call otherwise inaccessible methods
	 *
	 * @param   string  $key           Key to search for in the data array
	 * @param   mixed   $defaultValue  Default value to return if the key is not set
	 *
	 * @return  mixed   Value | null if doesn't exist
	 *
	 * @since   4.0
	 */
	public function __call($method, $parameters)
	{
		if (method_exists($this->implementation, $method))
		{
			return call_user_func_array(array($this->implementation, $method), $parameters);
		}
	}

	/**
	 * Check if actor is authorised to perform an action, optionally on an asset.
	 *
	 * @param   integer  $actor       Id of the actor for which to check authorisation.
	 * @param   mixed    $target      Subject of the check
	 * @param   string   $action      The name of the action to authorise.
	 * @param   string   $actorType   Optional type of actor.
	 *
	 * @return  boolean  True if authorised.
	 *
	 * @since   4.0
	 */
	public function check($actor, $target, $action, $actorType = null)
	{
		return $this->implementation->check($actor, $target, $action, $actorType);
	}

	/**
	 * Set actor as authorised to perform an action
	 *
	 * @param   integer  $actor       Id of the actor for which to check authorisation.
	 * @param   mixed    $target      Subject of the check
	 * @param   string   $action      The name of the action to authorise.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function allow($actor, $target, $action)
	{
		$this->implementation->allow($actor, $target, $action);
	}

	/**
	 * Set actor as not authorised to perform an action
	 *
	 * @param   integer  $actor       Id of the actor for which to check authorisation.
	 * @param   mixed    $target      Subject of the check
	 * @param   string   $action      The name of the action to authorise.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function deny($actor, $target, $action)
	{
		$this->implementation->deny($actor, $target, $action);
	}

	/** Inject permissions filter in the database object
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
	public function appendFilterQuery(&$query, $joincolumn, $action, $orWhere = null, $groups = null)
	{
		if ($this->implementation->appendsupport)
		{
			return $this->implementation->appendFilterQuery($query, $joincolumn, $action, $orWhere, $groups);
		}

		return false;
	}
}