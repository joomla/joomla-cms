<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Observer
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Observer pattern interface for Joomla
 *
 * A class that wants to observe another class must:
 *
 * 1) Add: implements JObserverInterface
 *    to its class
 *
 * 2) Implement a constructor, that can look like this:
 * 	public function __construct(JObservableInterface $observableObject)
 * 	{
 * 	    $observableObject->attachObserver($this);
 *  	$this->observableObject = $observableObject;
 * 	}
 *
 * 3) and must implement the instanciator function createObserver() below, e.g. as follows:
 * 	public static function createObserver(JObservableInterface $observableObject, $params = array())
 * 	{
 * 	    $observer = new self($observableObject);
 *      $observer->... = $params['...']; ...
 * 	    return $observer;
 * 	}
 *
 * 4) Then add functions corresponding to the events to be observed,
 *    E.g. to respond to event: $this->_observers->update('onBeforeLoad', array($keys, $reset));
 *    following function is needed in the obser:
 *  public function onBeforeLoad($keys, $reset) { ... }
 *
 * 5) Finally, the binding is made outside the observable and observer classes, using:
 * JObserverMapper::addObserverClassToClass('ObserverClassname', 'ObservableClassname', array('paramName' => 'paramValue'));
 * where the last array will be provided to the observer instanciator function createObserver.
 *
 * @package     Joomla.Platform
 * @subpackage  Observer
 * @link        http://docs.joomla.org/JObserverInterface
 * @since       3.1.2
 */
interface JObserverInterface
{
	/**
	 * Creates the associated observer instance and attaches it to the $observableObject
	 *
	 * @param   JObservableInterface  $observableObject  The observable subject object
	 * @param   array                 $params            Params for this observer
	 *
	 * @return  JObserverInterface
	 *
	 * @since   3.1.2
	 */
	public static function createObserver(JObservableInterface $observableObject, $params = array());
}
