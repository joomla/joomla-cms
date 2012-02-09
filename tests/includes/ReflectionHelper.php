<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Reflection helper class.
 *
 * @package  Joomla.UnitTest
 * @since    11.3
 */
class ReflectionHelper
{
	/**
	 * Helper method that gets a protected or private property in a class by relfection.
	 *
	 * @param   object  $object
	 * @param   string  $propertyName
	 *
	 * @return  mixed  The value of the property.
	 *
	 * @since   11.3
	 * @throws  InvalidArgumentException if property not available.
	 */
	public static function getValue($object, $propertyName)
	{
		$refl = new ReflectionClass($object);

		// First check if the property is easily accessible.
		if ($refl->hasProperty($propertyName))
		{
			$property = $refl->getProperty($propertyName);
			$property->setAccessible(true);

			return $property->getValue($object);
		}

		// Hrm, maybe dealing with a private property in the parent class.
		if (get_parent_class($object))
		{
			$property = new \ReflectionProperty(get_parent_class($object), $propertyName);
			$property->setAccessible(true);

			return $property->getValue($object);
		}

		throw new InvalidArgumentException(sprintf('Invalid property [%s] for class [%s]', $propertyName, get_class($object)));
	}

	/**
	 * Helper method that invokes a protected or private method in a class by reflection.
	 *
	 * Example usage:
	 *
	 * $this->asserTrue(JoomlaTestCase::invoke('methodName', $this->object, 123));
	 *
	 * @param   object  $object
	 * @param   string  $methodName
	 *
	 * @return  mixed
	 *
	 * @since   11.3
	 */
	public static function invoke($object, $methodName)
	{
		// Get the full argument list for the method.
		$args = func_get_args();

		// Remove the method name from the argument list.
		array_shift($args);
		array_shift($args);
		array_unshift($args, $object);

		$refl = new ReflectionClass($object);

		$method = $refl->getMethod($methodName);
		$method->setAccessible(true);

		return call_user_func_array(array($method, 'invoke'), $args);
	}

	/**
	 * Helper method that sets a protected or private property in a class by relfection.
	 *
	 * @param   object  $object
	 * @param   string  $propertyName
	 * @param   mixed   $value
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public static function setValue($object, $propertyName, $value)
	{
		$refl = new ReflectionClass($object);

		// First check if the property is easily accessible.
		if ($refl->hasProperty($propertyName))
		{
			$property = $refl->getProperty($propertyName);
			$property->setAccessible(true);

			$property->setValue($object, $value);
		}
		// Hrm, maybe dealing with a private property in the parent class.
		else if (get_parent_class($object))
		{
			$property = new \ReflectionProperty(get_parent_class($object), $propertyName);
			$property->setAccessible(true);

			$property->setValue($object, $value);
		}
	}
}
