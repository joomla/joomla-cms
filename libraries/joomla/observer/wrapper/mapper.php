<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Observer
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for JObserverMapper
 *
 * @package     Joomla.Platform
 * @subpackage  Observer
 * @since       3.4
 */
class JObserverWrapperMapper
{
	/**
	 * Helper wrapper method for addObserverClassToClass
	 *
	 * @param   string         $observerClass    The name of the observer class (implementing JObserverInterface).
	 * @param   string         $observableClass  The name of the observable class (implementing JObservableInterface).
	 * @param   array|boolean  $params           The params to give to the JObserverInterface::createObserver() function, or false to remove mapping.
	 *
	 * @return void
	 *
	 * @see     JObserverMapper::addObserverClassToClass
	 * @since   3.4
	 */
	public function addObserverClassToClass($observerClass, $observableClass, $params = array())
	{
		return JObserverMapper::addObserverClassToClass($observerClass, $observableClass, $params);
	}

	/**
	 * Helper wrapper method for attachAllObservers
	 *
	 * @param   JObservableInterface  $observableObject  The observable subject object.
	 *
	 * @return void
	 *
	 * @see     JObserverMapper::attachAllObservers
	 * @since   3.4
	 */
	public function attachAllObservers(JObservableInterface $observableObject)
	{
		return JObserverMapper::attachAllObservers($observableObject);
	}
}
