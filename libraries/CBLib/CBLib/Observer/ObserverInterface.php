<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 10/19/13 1:54 PM $
* @package CBLib\Observer
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/


namespace CBLib\Observer;


/**
 * Observer pattern interface
 *
 * The principle is to have one class dedicated at observing another class.
 * An object instance is then created automatically for each corresponding
 * observable object of that other class.
 *
 * A class that wants to observe another class must:
 *
 * 1) Add: implements ObserverInterface
 *    to its class
 *
 * 2) Implement a constructor, that can look like this:
 * 	public function __construct(ObservableInterface $observableObject)
 * 	{
 *  	$this->observableObject = $observableObject->attachObserver($this);
 * 	}
 *
 * 3) and must implement the instanciator function createObserver() below, e.g. as follows:
 * 	public static function createObserver(ObservableInterface $observableObject, $params = array())
 * 	{
 * 	    $observer = new static($observableObject);
 *      $observer->... = $params['...']; ...
 * 	    return $observer;
 * 	}
 *
 * 4) Then add functions corresponding to the events to be observed,
 *    E.g. to respond to event: $this->_observers->update('onBeforeLoad', array($keys, $reset));
 *    following function is needed in the observer:
 *  public function onBeforeLoad($keys, $reset) { ... }
 *
 * 5) Finally, the binding is made outside the observable and observer classes, using:
 * ObserverMapper::addObserverClassToClass('ObserverClassname', 'ObservableClassname', array('paramName' => 'paramValue'));
 * where the last array $params will be provided to the observer instanciator function createObserver.
 *
 * @package CBLib\Observer
 */
interface ObserverInterface {
	/**
	 * Creates the associated observer instance and attaches it to the $observableObject
	 *
	 * @param   ObservableInterface  $observableObject  The observable subject object
	 * @param   array                 $params            Params for this observer
	 * @return  ObserverInterface
	 */
	public static function createObserver(ObservableInterface $observableObject, $params = array());
}
