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
 * Observable Subject pattern interface for Joomla
 *
 * To make a class and its inheriting classes observable:
 * 1) add: implements JObservableInterface
 *    to its class
 *
 * 2) at the end of the constructor, add:
 * // Create observer updater and attaches all observers interested by $this class:
 * $this->_observers = new JObserverUpdater($this);
 * JObserverMapper::attachAllObservers($this);
 *
 * 3) add the function attachObserver below to your class to add observers using the JObserverUpdater class:
 * 	public function attachObserver(JObserverInterface $observer)
 * 	{
 * 		$this->_observers->attachObserver($observer);
 * 	}
 *
 * 4) in the methods that need to be observed, add, e.g. (name of event, params of event):
 * 		$this->_observers->update('onBeforeLoad', array($keys, $reset));
 *
 * @package     Joomla.Platform
 * @subpackage  Observer
 * @link        http://docs.joomla.org/JObservableInterface
 * @since       3.1.2
 */
interface JObservableInterface
{
	/**
	 * Adds an observer to this JObservableInterface instance.
	 * Ideally, this method should be called fron the constructor of JObserverInterface
	 * which should be instanciated by JObserverMapper.
	 * The implementation of this function can use JObserverUpdater
	 *
	 * @param   JObserverInterface  $observer  The observer to attach to $this observable subject
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function attachObserver(JObserverInterface $observer);
}
