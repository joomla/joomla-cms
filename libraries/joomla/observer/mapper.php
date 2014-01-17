<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Observer
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Observer mapping pattern implementation for Joomla
 *
 * @package     Joomla.Platform
 * @subpackage  Observer
 * @link        http://docs.joomla.org/JObserverMapper
 * @since       3.1.2
 */
class JObserverMapper
{
	/**
	 * Array: array( JObservableInterface_classname => array( JObserverInterface_classname => array( paramname => param, .... ) ) )
	 *
	 * @var    array
	 * @since  3.1.2
	 */
	protected static $observations = array();

	/**
	 * Adds a mapping to observe $observerClass subjects with $observableClass observer/listener, attaching it on creation with $params
	 * on $observableClass instance creations
	 *
	 * @param   string         $observerClass    The name of the observer class (implementing JObserverInterface)
	 * @param   string         $observableClass  The name of the observable class (implementing JObservableInterface)
	 * @param   array|boolean  $params           The params to give to the JObserverInterface::createObserver() function, or false to remove mapping
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public static function addObserverClassToClass($observerClass, $observableClass, $params = array())
	{
		if ($params !== false)
		{
			static::$observations[$observableClass][$observerClass] = $params;
		}
		else
		{
			unset(static::$observations[$observableClass][$observerClass]);
		}
	}

	/**
	 * Attaches all applicable observers to an $observableObject
	 *
	 * @param   JObservableInterface  $observableObject  The observable subject object
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public static function attachAllObservers(JObservableInterface $observableObject)
	{
		$observableClass = get_class($observableObject);

		while ($observableClass != false)
		{
			// Attach applicable Observers for the class to the Observable subject:
			if (isset(static::$observations[$observableClass]))
			{
				foreach (static::$observations[$observableClass] as $observerClass => $params)
				{
					// Attach an Observer to the Observable subject:
					/**
					 * @var JObserverInterface $observerClass
					 */
					$observerClass::createObserver($observableObject, $params);
				}
			}

			// Get parent class name (or false if none), and redo the above on it:
			$observableClass = get_parent_class($observableClass);
		}
	}
}
