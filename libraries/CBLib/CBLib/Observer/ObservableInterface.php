<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 10/19/13 1:56 PM $
* @package CBLib\Observer
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/


namespace CBLib\Observer;


/**
 * Observable Subject pattern interface
 *
 * To make a class and its inheriting classes observable:
 * 1) add: implements ObservableInterface
 *    to its class
 *
 * 2) add a protected variable:
 * @ var ObserverUpdater
 * protected $_observers;
 *
 * 3) at the end of the constructor, add:
 * // Create observer updater and attaches all observers interested by $this class:
 * $this->_observers = new ObserverUpdater($this);
 * ObserverMapper::attachAllObservers($this);
 *
 * 4) add the function attachObserver below to your class to add observers using the ObserverUpdater class:
 * 	public function attachObserver(ObserverInterface $observer)
 * 	{
 * 		$this->_observers->attachObserver($observer);
 * 	}
 *
 * 4) in the methods that need to be observed, add, e.g. (name of event, params of event):
 * 		$this->_observers->update('onBeforeLoad', array($keys, $reset));
 *
 * @package CBLib\Observer
 */
interface ObservableInterface {
	/**
	 * Adds an observer to this ObservableInterface instance.
	 * This method will be called automatically fron the constructor of ObserverInterface
	 * which will be instanciated by ObserverMapper.
	 * The implementation of this function can use ObserverUpdater (see above class description)
	 *
	 * @param   ObserverInterface    $observer  The observer to attach to $this observable subject
	 * @return  ObservableInterface
	 */
	public function attachObserver(ObserverInterface $observer);
}
