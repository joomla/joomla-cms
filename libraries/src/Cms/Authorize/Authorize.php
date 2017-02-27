<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Authorize;

use Joomla\String\StringHelper;
use Joomla\Cms\Authorize\AuthorizeInterface;

defined('JPATH_PLATFORM') or die;


final class Authorize implements AuthorizeInterface
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


	public function __construct(AuthorizeInterface $implementation)
	{
			$this->implementation = $implementation;
	}

	/**
	 * Get Authorize instance.
	 *
	 * Default $iNameStatic value be changed in 4.2 when legacy implementation is removed
	 *
	 * @param   string  $implementationName   Class name to instantiate
	 *
	 * @return  self
	 *
	 * @since   4.0
	 */
	public static function getInstance($implementationName = null)
	{
		static $iNameStatic = 'JoomlaLegacy';

		// Run plugins only once
		if ($iNameStatic == null)
		{
			\JPluginHelper::importPlugin('authorize');
			\JFactory::getApplication()->triggerEvent('onAuthorizationInitalize', array(&$iNameStatic));
		}


		$implementationName = isset($implementationName) ? $implementationName : $iNameStatic;
		$implementationClass = __NAMESPACE__ . '\Implementation\AuthorizeImplementation' . StringHelper::ucfirst($implementationName);

		if (!isset(self::$instance[$implementationClass]))
		{
			$implementation = new $implementationClass();

			self::$instance[$implementationClass] = new Authorize($implementation);
		}
		elseif(!class_exists($implementationClass))
		{
			throw new \RuntimeException('Unable to load Authorize Implementation: ' . $implementationClass, 500);
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
				if ($value instanceof AuthorizeInterface && $value instanceof AuthorizeImplementation)
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
		else
		{
			throw new \BadMethodCallException(sprintf('%s does not exist in %s', $method, get_class($this)));
		}
	}

	/**
	 * Check if actor is authorised to perform an action, optionally on an asset.
	 *
	 * @param   integer  $actor       Id of the actor for which to check authorisation.
	 * @param   mixed    $target      Subject of the check
	 * @param   string   $action      The name of the action to authorise.
	 * @param   string   $actorType   Type of actor.
	 *
	 * @return  mixed  True if authorised and assetId is numeric/named. An array of boolean values if assetId is array.
	 *
	 * @since   4.0
	 */
	public function check($actor, $target, $action, $actorType)
	{
		return $this->implementation->check($actor, $target, $action, $actorType);
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