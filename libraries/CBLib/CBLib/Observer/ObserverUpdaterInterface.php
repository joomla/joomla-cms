<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 10/19/13 1:58 PM $
* @package CBLib\Observer
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/


namespace CBLib\Observer;


/**
 * Class ObserverUpdaterInterface Observer updater pattern
 * This allows to attach many observers to one observable and to update them as needed.
 *
 * @package CBLib\Observer
 */
interface ObserverUpdaterInterface {
	/**
	 * Constructor
	 *
	 * @param   ObservableInterface  $observable  The observable subject object
	 */
	public function __construct(ObservableInterface $observable);

	/**
	 * Adds an observer to the ObservableInterface instance updated by this
	 * This method can be called from ObservableInterface::attachObserver
	 *
	 * @param   ObserverInterface  $observer  The observer object
	 * @return  void
	 */
	public function attachObserver(ObserverInterface $observer);

	/**
	 * Removes $observer from the ObservableInterface instance updated by this
	 *
	 * @param   ObserverInterface  $observer  The observer object
	 * @return  void
	 */
	public function detachObserver(ObserverInterface $observer);

	/**
	 * Call all observers for $event with $params
	 *
	 * @param   string  $event   Event name (function name in observer)
	 * @param   array   $params  Params of event (params in observer function)
	 * @return  void
	 */
	public function update($event, $params);

	/**
	 * Enable/Disable calling of observers (this is useful when calling parent:: function
	 *
	 * @param   boolean  $enabled  Enable (true) or Disable (false) the observer events
	 * @return  boolean  Returns old state
	 */
	public function doCallObservers($enabled);
}
