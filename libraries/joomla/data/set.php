<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Data
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JDataSet is a collection class that allows the developer to operate on a set of JData objects as if they were in a
 * typical PHP array.
 *
 * @since  12.3
 */
class JDataSet implements JDataDumpable, ArrayAccess, Countable, Iterator
{
	/**
	 * The current position of the iterator.
	 *
	 * @var    integer
	 * @since  12.3
	 */
	private $_current = false;

	/**
	 * The iterator objects.
	 *
	 * @var    array
	 * @since  12.3
	 */
	private $_objects = array();

	/**
	 * The class constructor.
	 *
	 * @param   array  $objects  An array of JData objects to bind to the data set.
	 *
	 * @since   12.3
	 * @throws  InvalidArgumentException if an object is not an instance of JData.
	 */
	public function __construct(array $objects = array())
	{
		// Set the objects.
		$this->_initialise($objects);
	}

	/**
	 * The magic call method is used to call object methods using the iterator.
	 *
	 * Example: $array = $objectList->foo('bar');
	 *
	 * The object list will iterate over its objects and see if each object has a callable 'foo' method.
	 * If so, it will pass the argument list and assemble any return values. If an object does not have
	 * a callable method no return value is recorded.
	 * The keys of the objects and the result array are maintained.
	 *
	 * @param   string  $method     The name of the method called.
	 * @param   array   $arguments  The arguments of the method called.
	 *
	 * @return  array   An array of values returned by the methods called on the objects in the data set.
	 *
	 * @since   12.3
	 */
	public function __call($method, $arguments = array())
	{
		$return = array();

		// Iterate through the objects.
		foreach ($this->_objects as $key => $object)
		{
			// Create the object callback.
			$callback = array($object, $method);

			// Check if the callback is callable.
			if (is_callable($callback))
			{
				// Call the method for the object.
				$return[$key] = call_user_func_array($callback, $arguments);
			}
		}

		return $return;
	}

	/**
	 * The magic get method is used to get a list of properties from the objects in the data set.
	 *
	 * Example: $array = $dataSet->foo;
	 *
	 * This will return a column of the values of the 'foo' property in all the objects
	 * (or values determined by custom property setters in the individual JData's).
	 * The result array will contain an entry for each object in the list (compared to __call which may not).
	 * The keys of the objects and the result array are maintained.
	 *
	 * @param   string  $property  The name of the data property.
	 *
	 * @return  array  An associative array of the values.
	 *
	 * @since   12.3
	 */
	public function __get($property)
	{
		$return = array();

		// Iterate through the objects.
		foreach ($this->_objects as $key => $object)
		{
			// Get the property.
			$return[$key] = $object->$property;
		}

		return $return;
	}

	/**
	 * The magic isset method is used to check the state of an object property using the iterator.
	 *
	 * Example: $array = isset($objectList->foo);
	 *
	 * @param   string  $property  The name of the property.
	 *
	 * @return  boolean  True if the property is set in any of the objects in the data set.
	 *
	 * @since   12.3
	 */
	public function __isset($property)
	{
		$return = array();

		// Iterate through the objects.
		foreach ($this->_objects as $object)
		{
			// Check the property.
			$return[] = isset($object->$property);
		}

		return in_array(true, $return, true) ? true : false;
	}

	/**
	 * The magic set method is used to set an object property using the iterator.
	 *
	 * Example: $objectList->foo = 'bar';
	 *
	 * This will set the 'foo' property to 'bar' in all of the objects
	 * (or a value determined by custom property setters in the JData).
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value to give the data property.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function __set($property, $value)
	{
		// Iterate through the objects.
		foreach ($this->_objects as $object)
		{
			// Set the property.
			$object->$property = $value;
		}
	}

	/**
	 * The magic unset method is used to unset an object property using the iterator.
	 *
	 * Example: unset($objectList->foo);
	 *
	 * This will unset all of the 'foo' properties in the list of JData's.
	 *
	 * @param   string  $property  The name of the property.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function __unset($property)
	{
		// Iterate through the objects.
		foreach ($this->_objects as $object)
		{
			unset($object->$property);
		}
	}

	/**
	 * Gets the number of data objects in the set.
	 *
	 * @return  integer  The number of objects.
	 *
	 * @since   12.3
	 */
	public function count()
	{
		return count($this->_objects);
	}

	/**
	 * Clears the objects in the data set.
	 *
	 * @return  JDataSet  Returns itself to allow chaining.
	 *
	 * @since   12.3
	 */
	public function clear()
	{
		$this->_objects = array();
		$this->rewind();

		return $this;
	}

	/**
	 * Get the current data object in the set.
	 *
	 * @return  JData  The current object, or false if the array is empty or the pointer is beyond the end of the elements.
	 *
	 * @since   12.3
	 */
	public function current()
	{
		return is_scalar($this->_current) ? $this->_objects[$this->_current] : false;
	}

	/**
	 * Dumps the data object in the set, recursively if appropriate.
	 *
	 * @param   integer           $depth   The maximum depth of recursion (default = 3).
	 *                                     For example, a depth of 0 will return a stdClass with all the properties in native
	 *                                     form. A depth of 1 will recurse into the first level of properties only.
	 * @param   SplObjectStorage  $dumped  An array of already serialized objects that is used to avoid infinite loops.
	 *
	 * @return  array  An associative array of the date objects in the set, dumped as a simple PHP stdClass object.
	 *
	 * @see     JData::dump()
	 * @since   12.3
	 */
	public function dump($depth = 3, SplObjectStorage $dumped = null)
	{
		// Check if we should initialise the recursion tracker.
		if ($dumped === null)
		{
			$dumped = new SplObjectStorage;
		}

		// Add this object to the dumped stack.
		$dumped->attach($this);

		$objects = array();

		// Make sure that we have not reached our maximum depth.
		if ($depth > 0)
		{
			// Handle JSON serialization recursively.
			foreach ($this->_objects as $key => $object)
			{
				$objects[$key] = $object->dump($depth, $dumped);
			}
		}

		return $objects;
	}

	/**
	 * Gets the data set in a form that can be serialised to JSON format.
	 *
	 * Note that this method will not return an associative array, otherwise it would be encoded into an object.
	 * JSON decoders do not consistently maintain the order of associative keys, whereas they do maintain the order of arrays.
	 *
	 * @param   mixed  $serialized  An array of objects that have already been serialized that is used to infinite loops
	 *                              (null on first call).
	 *
	 * @return  array  An array that can be serialised by json_encode().
	 *
	 * @since   12.3
	 */
	public function jsonSerialize($serialized = null)
	{
		// Check if we should initialise the recursion tracker.
		if ($serialized === null)
		{
			$serialized = array();
		}

		// Add this object to the serialized stack.
		$serialized[] = spl_object_hash($this);
		$return = array();

		// Iterate through the objects.
		foreach ($this->_objects as $object)
		{
			// Call the method for the object.
			$return[] = $object->jsonSerialize($serialized);
		}

		return $return;
	}

	/**
	 * Gets the key of the current object in the iterator.
	 *
	 * @return  scalar  The object key on success; null on failure.
	 *
	 * @since   12.3
	 */
	public function key()
	{
		return $this->_current;
	}

	/**
	 * Gets the array of keys for all the objects in the iterator (emulates array_keys).
	 *
	 * @return  array  The array of keys
	 *
	 * @since   12.3
	 */
	public function keys()
	{
		return array_keys($this->_objects);
	}

	/**
	 * Advances the iterator to the next object in the iterator.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function next()
	{
		// Get the object offsets.
		$keys = $this->keys();

		// Check if _current has been set to false but offsetUnset.
		if ($this->_current === false && isset($keys[0]))
		{
			// This is a special case where offsetUnset was used in a foreach loop and the first element was unset.
			$this->_current = $keys[0];

			return;
		}

		// Get the current key.
		$position = array_search($this->_current, $keys);

		// Check if there is an object after the current object.
		if ($position !== false && isset($keys[$position + 1]))
		{
			// Get the next id.
			$this->_current = $keys[$position + 1];

			return;
		}

		// That was the last object or the internal properties have become corrupted.
		$this->_current = null;
	}

	/**
	 * Checks whether an offset exists in the iterator.
	 *
	 * @param   mixed  $offset  The object offset.
	 *
	 * @return  boolean  True if the object exists, false otherwise.
	 *
	 * @since   12.3
	 */
	public function offsetExists($offset)
	{
		return isset($this->_objects[$offset]);
	}

	/**
	 * Gets an offset in the iterator.
	 *
	 * @param   mixed  $offset  The object offset.
	 *
	 * @return  JData  The object if it exists, null otherwise.
	 *
	 * @since   12.3
	 */
	public function offsetGet($offset)
	{
		return isset($this->_objects[$offset]) ? $this->_objects[$offset] : null;
	}

	/**
	 * Sets an offset in the iterator.
	 *
	 * @param   mixed  $offset  The object offset.
	 * @param   JData  $object  The object object.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @throws  InvalidArgumentException if an object is not an instance of JData.
	 */
	public function offsetSet($offset, $object)
	{
		// Check if the object is a JData object.
		if (!($object instanceof JData))
		{
			throw new InvalidArgumentException(sprintf('%s("%s", *%s*)', __METHOD__, $offset, gettype($object)));
		}

		// Set the offset.
		$this->_objects[$offset] = $object;
	}

	/**
	 * Unsets an offset in the iterator.
	 *
	 * @param   mixed  $offset  The object offset.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function offsetUnset($offset)
	{
		if (!$this->offsetExists($offset))
		{
			// Do nothing if the offset does not exist.
			return;
		}

		// Check for special handling of unsetting the current position.
		if ($offset == $this->_current)
		{
			// Get the current position.
			$keys = $this->keys();
			$position = array_search($this->_current, $keys);

			$current = false;

			// Check if there is an object before the current object.
			if ($position > 0)
			{
				// Move the current position back one.
				$current = $keys[$position - 1];
			}

			$this->_current = $current;
		}

		unset($this->_objects[$offset]);
	}

	/**
	 * Rewinds the iterator to the first object.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function rewind()
	{
		// Set the current position to the first object.
		if (empty($this->_objects))
		{
			$this->_current = false;
			return;
		}

		$keys = $this->keys();
		$this->_current = array_shift($keys);
	}

	/**
	 * Validates the iterator.
	 *
	 * @return  boolean  True if valid, false otherwise.
	 *
	 * @since   12.3
	 */
	public function valid()
	{
		// Check the current position.
		return (is_scalar($this->_current) && isset($this->_objects[$this->_current]));
	}

	/**
	 * Initialises the list with an array of objects.
	 *
	 * @param   array  $input  An array of objects.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @throws  InvalidArgumentException if an object is not an instance of JData.
	 */
	private function _initialise(array $input = array())
	{
		foreach ($input as $key => $object)
		{
			if (!is_null($object))
			{
				$this->offsetSet($key, $object);
			}
		}

		$this->rewind();
	}
}
