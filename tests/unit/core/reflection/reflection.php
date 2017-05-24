<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Reflection helper class.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
class TestReflection
{
	/**
	 * Helper method that gets a protected or private property in a class by relfection.
	 *
	 * @param   object  $object        The object from which to return the property value.
	 * @param   string  $propertyName  The name of the property to return.
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
	 * $this->assertTrue(TestReflection::invoke('methodName', $this->object, 123));
	 *
	 * @param   object  $object      The object on which to invoke the method.
	 * @param   string  $methodName  The name of the method to invoke.
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

		$method = new ReflectionMethod($object, $methodName);
		$method->setAccessible(true);

		$result = $method->invokeArgs(is_object($object) ? $object : null, $args);

		return $result;
	}

	/**
	 * Helper method that sets a protected or private property in a class by relfection.
	 *
	 * @param   object  $object        The object for which to set the property.
	 * @param   string  $propertyName  The name of the property to set.
	 * @param   mixed   $value         The value to set for the property.
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
		elseif (get_parent_class($object))
		{
			$property = new \ReflectionProperty(get_parent_class($object), $propertyName);
			$property->setAccessible(true);

			$property->setValue($object, $value);
		}
	}
}
