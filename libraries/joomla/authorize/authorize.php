<?php
/**
 * @package     Joomla
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */


class JAuthorize implements JAuthorizeInterface
{
	private static $instance = null;

	private $implementation = null;

	private $implementationClass;


	public function __construct(JAuthorizeInterface $implementation)
	{
			$this->implementation = $implementation;
			$this->implementationClass = get_class($implementation);
	}

	public static function getInstance($implementationName = 'Joomla')
	{

		$implementationClass = 'JAuthorizeImplementation' . JString::ucfirst($implementationName);

		if (!isset(self::$instance[$implementationClass]))
		{
			$implementation = new $implementationClass();

			self::$instance[$implementationClass] = new JAuthorize($implementation);
		}

		return self::$instance[$implementationClass];

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
			case 'implementation':
				if ($value instanceof JAuthorizeInterface)
				{
					$this->implementation = $value;
				}
			break;

			case 'authorizationMatrix':
				if ($value instanceof $this->implementationClass)
				{
					self::$authorizationMatrix[$this->implementationClass] = $value;
				}
			break;

		}

		return $this;
	}

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
			return isset(self::$authorizationMatrix[$this->implementationClass]) ? self::$authorizationMatrix[$this->implementationClass] : $defaultValue;
		}

		return isset($this->$key) ? $this->$key : $defaultValue;
	}

	public function check($actor, $target, $action)
	{
		return $this->implementation->check($actor, $target, $action);
	}

	public function allow($actor, $target, $action)
	{
		return $this->implementation->allow($actor, $target, $action);
	}

	public function deny($actor, $target, $action)
	{
		return $this->implementation->deny($actor, $target, $action);
	}


}